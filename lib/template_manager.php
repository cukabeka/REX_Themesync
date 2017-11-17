<?php

class rex_themesync_template_manager extends rex_themesync_manager {
    public function __construct(&$addon) {
        parent::__construct('template', $addon);
    }

    public function renderItem(&$item, $mode) {
        $local = $this->getLocal();
        $template_key = $item->getKey();
        if ($mode === rex_themesync_source::REPO) {
            /* @var $local rex_themesync_local */
            $is_installed = $local->isTemplateExisting($item);
            $in_repo = true;
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
                    <label class="" for="<?= $template_key ?>_check"><?= htmlentities($item->getName()) ?></label>
                    <code><?= $template_key ?></code>
                </td>
                <td>
                <?php if ($in_repo) : ?>
                    <input type="checkbox" id="<?= $template_key ?>_install_check" class="" name="install[]" value="<?= htmlentities($item->getName()) ?>" />
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
            //$upload = rex_post('upload', 'array', []);
            
            /* @var $repo rex_themesync_source */
            $repo  = $this->getRepo();
            $local = $this->getLocal();
            
            foreach ($install as $template_name) {
                $template = new rex_themesync_template($template_name, $repo);
                if (!$template->isExisting()) {
                    echo rex_view::warning($this->addon->i18n('template_not_found').': '. htmlentities($template->getName()));
                    continue;
                }

                if (!$local->installTemplate($template, true)) {
                    // TODO last error oder sowas...
                    echo rex_view::warning($this->addon->i18n('template_not_installed').': '. htmlentities($template->getName()));
                    continue;
                }

                echo rex_view::success($this->addon->i18n('template_sucess').': '. htmlentities($template->getName()));
            }
            $repo->resetTemplates();
        }
    }

    public function render() {
        /* @var $repo rex_themesync_source */
        $repo = $this->getRepo();
        
        $items = $repo->listTemplates();
        //print_r($items);
        $html = '';
        foreach ($items as &$item) {
            $html .= $this->renderItem($item, rex_themesync_source::REPO);
        }
        return $html;
    }
}