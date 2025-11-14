<?php

class rex_themesync_template_manager extends rex_themesync_manager {
    public function __construct(&$addon) {
        parent::__construct('template', $addon);
    }

    public function renderItem(&$item, $mode) {
        $local = $this->getLocal();
        $repo = $this->getRepo();
        
        $template_key = $item->getKey();
        $numericKey = $item->getNumericKey();
        $config = $item->getConfig();
        
        if ($mode === rex_themesync_source::REPO) {
            /* @var $local rex_themesync_local */
            $is_installed = $local->isTemplateExisting($item);
            $in_repo = true;
        } else {
            $is_installed = true;
            $in_repo = $repo->isTemplateExisting($item);
        }

        
        
        
        
        ob_implicit_flush(false);
        ob_start();
        ?>
        <tr class="template_row" data-key="<?= $template_key ?>" data-name="<?= htmlentities($item->getName()) ?>" >
                <td>
            <?php if ($in_repo) : ?><i class="glyphicon glyphicon-ok"></i><?php endif; ?>
                </td>
                <td>
            <?php if ($is_installed) : ?><i class="glyphicon glyphicon-ok"></i><?php endif; ?>
                </td>
                <td data-title="<?= $this->addon->i18n('modul') ?>">
                    <label class="" for="<?= $template_key ?>_check">
                        <?= htmlentities($item->getName()) ?>
                    </label>
                    <code><?= $template_key ?></code>
                    <?php if ($numericKey): ?>
                        <span class="label label-info">Key: <?= htmlentities($numericKey) ?></span>
                    <?php endif; ?>
                    <?php if ($config): ?>
                        <br/>
                        <small class="text-muted">
                            <?php if (isset($config['version'])): ?>
                                <span class="label label-default">v<?= htmlentities($config['version']) ?></span>
                            <?php endif; ?>
                            <?php if (isset($config['description'])): ?>
                                <?= htmlentities($config['description']) ?>
                            <?php endif; ?>
                            <?php if (isset($config['git']['branch'])): ?>
                                <br/>
                                <i class="fa fa-code-fork"></i> <?= htmlentities($config['git']['branch']) ?>
                                <?php if (isset($config['git']['commit'])): ?>
                                    @ <?= htmlentities(substr($config['git']['commit'], 0, 7)) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($in_repo) : ?>
                    <input type="checkbox" id="<?= $template_key ?>_install_check" class="" name="install[]" value="<?= htmlentities($item->getName()) ?>" />
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_installed) : ?>
                    <input type="checkbox" id="<?= $template_key ?>_upload_check" class="" name="upload[]" value="<?= htmlentities($item->getName()) ?>" />
                    <?php endif; ?>
                </td>
            </tr>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function action() {
        if (rex_post('sync')) {
            $install = rex_post('install', 'array', []);
            $upload = rex_post('upload', 'array', []);
            
            /* @var $repo rex_themesync_source */
            $repo  = $this->getRepo();
            $local = $this->getLocal();
            
            foreach ($install as $template_name) {
                $template = new rex_themesync_template($template_name, $repo);
                if (!$template->isExisting()) {
                    echo rex_view::warning($this->addon->i18n('template_install_not_found').': '. htmlentities($template->getName()));
                    continue;
                }

                if (!$local->installTemplate($template, true)) {
                    // TODO last error oder sowas...
                    echo rex_view::warning($this->addon->i18n('template_install_fail').': '. htmlentities($template->getName()));
                    continue;
                }

                echo rex_view::success($this->addon->i18n('template_install_sucess').': '. htmlentities($template->getName()));
            }
            
            foreach ($upload as $template_name) {
                $template = new rex_themesync_template($template_name, $local);
                if (!$template->isExisting()) {
                    echo rex_view::warning($this->addon->i18n('template_upload_not_found').': '. htmlentities($template->getName()));
                    continue;
                }

                if (!$repo->uploadTemplate($template, true)) {
                    // TODO last error oder sowas...
                    echo rex_view::warning($this->addon->i18n('template_upload_fail').': '. htmlentities($template->getName()));
                    continue;
                }

                echo rex_view::success($this->addon->i18n('template_upload_sucess').': '. htmlentities($template->getName()));
            }
            
            $repo->resetTemplates();
            $local->resetTemplates();
        }
    }

    public function render() {
        /* @var $repo rex_themesync_source */
        $repo = $this->getRepo();
        
        $items = $repo->listTemplates();
        $local = $this->getLocal();
        
        $html = '';
        foreach ($items as &$item) {
            $html .= $this->renderItem($item, rex_themesync_source::REPO);
        }
        
        // lokale, die nicht im repo sind
        $local_items = $local->listTemplates();
        foreach ($local_items as &$item) {
            if (isset($items[$item->getKey()])) {
                continue;
            }
            $html .= $this->renderItem($item, rex_themesync_source::LOCAL);
        }
        return $html;
    }
}