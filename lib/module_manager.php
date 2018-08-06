<?php

class rex_themesync_module_manager extends rex_themesync_manager {
    public function __construct(&$addon) {
        parent::__construct('module', $addon);
    }

    public function renderItem(&$item, $mode) {
        $local = $this->getLocal();
        $repo = $this->getRepo();
        
        $module_key = $item->getKey();
        $statusfarbe = '#f8a8e8;';
        if ($mode === rex_themesync_source::REPO) {
            /* @var $local rex_themesync_local */
            $is_installed = $local->isModuleExisting($item);
            $in_repo = true;
        } else {
            $is_installed = true;
            $in_repo = $repo->isModuleExisting($item);
        }

        #$pd = new Parsedown();
        #$info = $item->getReadme();
        #$info = $info ? $pd->text($info) : '';
        $info = '';
        ob_implicit_flush(false);
        ob_start();
        ?>
        <tr class="module_row" data-key="<?= $module_key ?>" data-name="<?= htmlentities($item->getName()) ?>" >
                <td>
            <?php if ($in_repo) : ?><i class="glyphicon glyphicon-ok"></i><?php endif; ?>
                </td>
                <td>
            <?php if ($is_installed) : ?><i class="glyphicon glyphicon-ok"></i><?php endif; ?>
                </td>
                <td data-title="<?= $this->addon->i18n('modul') ?>">
                    <!--<input class="form-control" type="text" name="modul_name" value="<?= htmlentities($item->getName()) ?>">-->
                    <label class="" for="<?= $module_key ?>_check"><?= htmlentities($item->getName()) ?></label>
                    <code><?= $module_key ?></code>
                    <div id="<?= $module_key ?>_info" class="collapse">
                        <p class="accordiontitle">Info</p>
                        <div style="padding: 10px; background: #f5f5f5; border: 1px solid #ccc;">
                            <?= $info ?>
                        </div>
                    </div>

                    <div id="<?= $module_key ?>_code" class="collapse"></div>

                    <div id="<?= $module_key ?>_scss" class="collapse">
                        <div style="padding: 10px 0 10px 0;" >
                        </div>
                    </div>
                </td>

              <td style="font-size: 2rem;">
                <!--<i data-toggle="collapse" onclick="themesync_modul_details(this)" data-target="#<?= $module_key ?>_code" class="rex-icon rex-icon-module" style="<?= $statusfarbe ?>cursor:pointer;" title="todo: statusinfo"></i>-->
              </td>

              <td style="font-size: 2rem;">
                <?php if ($info) : ?>
                <!--<i data-toggle="collapse" data-target="#<?= $module_key ?>_info" class="rex-icon rex-icon-info" style="cursor:pointer;"></i>-->
                <?php endif; ?>
              </td>

                <td>
                    <?php if ($in_repo) : ?>
                    <input type="checkbox" id="<?= $module_key ?>_install_check" class="" name="install[]" value="<?= htmlentities($item->getName()) ?>" />
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_installed) : ?>
                    <input type="checkbox" id="<?= $module_key ?>_upload_check" class="" name="upload[]" value="<?= htmlentities($item->getName()) ?>" />
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

            foreach ($install as $module_name) {
                $module = new rex_themesync_module($module_name, $repo);
                if (!$module->isExisting()) {
                    echo rex_view::warning($this->addon->i18n('module_install_not_found').': '. htmlentities($module->getName()));
                    continue;
                }

                if (!$local->installModule($module, true)) {
                    // TODO last error oder sowas...
                    echo rex_view::warning($this->addon->i18n('module_install_fail').': '. htmlentities($module->getName()));
                    continue;
                }

                echo rex_view::success($this->addon->i18n('module_install_sucess').': '. htmlentities($module->getName()));
            }
            
            
            foreach ($upload as $module_name) {
                $module = new rex_themesync_module($module_name, $local);
                if (!$module->isExisting()) {
                    echo rex_view::warning($this->addon->i18n('module_upload_not_found').': '. htmlentities($module->getName()));
                    continue;
                }

                if (!$repo->uploadModule($module, true)) {
                    // TODO last error oder sowas...
                    echo rex_view::warning($this->addon->i18n('module_upload_fail').': '. htmlentities($module->getName()));
                    continue;
                }

                echo rex_view::success($this->addon->i18n('module_upload_sucess').': '. htmlentities($module->getName()));
            }
            
            $repo->resetModules();
            $local->resetModules();
        }
    }
    
    
    public function render() {
        /* @var $repo rex_themesync_source */
        $repo = $this->getRepo();
        $local = $this->getLocal();
        
        $items = $repo->listModules();
        
        $html = '';
        foreach ($items as &$item) {
            $html .= $this->renderItem($item, rex_themesync_source::REPO);
        }
        
        // lokale, die nicht im repo sind
        $local_items = $local->listModules();
        foreach ($local_items as &$item) {
            if (isset($items[$item->getKey()])) {
                continue;
            }
            $html .= $this->renderItem($item, rex_themesync_source::LOCAL);
        }
        return $html;
    }

}