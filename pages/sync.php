<?php
$repo = new rex_themesync_sync_localfilesystem([
    'repo' => rex_path::addonData('themesync', 'repo'),
        ]);

$local = new rex_themesync_sync_localdb();


$repo_modules = $repo->listModules();
$local_modules = $local->listModules();


if (rex_post('sync')) {
    $install = rex_post('install', 'array', []);
    $upload = rex_post('upload', 'array', []);
    
    foreach ($install as $module_key) {
        if (!isset($repo_modules[$module_key])) {
            echo rex_view::warning($this->i18n('module_not_found'));
            continue;
        }
        
        $module = $repo_modules[$module_key];
        if (!$module->install()) {
            // TODO last error oder sowas...
            echo rex_view::warning($this->i18n('module_not_installed'));
            continue;
        }
        
        echo rex_view::success($this->i18n('module_sucess'));
    }
    
    // TODO: redirect!!
    $repo_modules = $repo->listModules();
    $local_modules = $local->listModules();
}
#echo '<pre>';
#print_r($_POST);
#echo '</pre>';





$rex_themesync_render_module = function (&$m, $mode) {
    $module_key = $m->getKey();
    $statusfarbe = '#f8a8e8;';
    if ($mode === rex_themesync_sync::REX_THEMESYNC_REPO) {
        $is_installed = isset($local_modules[$module_key]);
        $in_repo = true;
    } else {
        $is_installed = true;
        $in_repo = isset($repo_modules[$module_key]);
    }
    ?>
    <tr>
        <td>
    <?php if ($in_repo) : ?><i class="glyphicon glyphicon-ok"></i><?php endif; ?>
        </td>
        <td>
    <?php if ($is_installed) : ?><i class="glyphicon glyphicon-ok"></i><?php endif; ?>
        </td>
        <td data-title="<?= $this->i18n('modul') ?>">
            <!--<input class="form-control" type="text" name="modul_name" value="<?= htmlentities($m->getName()) ?>">-->
            <label class="" for="<?= $module_key ?>_check"><?= htmlentities($m->getName()) ?></label>
            <code><?= $module_key ?></code>
            <div id="<?= $module_key ?>_info" class="collapse">
                <p class="accordiontitle">Info</p>
                <div style="padding: 10px; background: #f5f5f5; border: 1px solid #ccc;">
                    info
                </div>
            </div>
            <div id="<?= $module_key ?>_code" class="collapse">
    <?php /*
      <p class="accordiontitle"><?= $this->i18n('input') ?></p>
      TODO rex_string::highlight($modul['input'])
      <p class="accordiontitle"><?= $this->i18n('output') ?></p>
      TODO rex_string::highlight($modul['output']) */ ?>
            </div>
            <div id="<?= $module_key ?>_scss" class="collapse">
                <div style="padding: 10px 0 10px 0;" >
    <?php /*

      if($modul['styles_scss']) {
      $modulausgabe[] = '
      <p class="accordiontitle">'.$this->i18n('scss').'</p>
      '.rex_string::highlight($modul['styles_scss']);
      }
      if($modul['styles_css']) {
      $modulausgabe[] = '
      <p class="accordiontitle">'.$this->i18n('css').'</p>
      '.rex_string::highlight($modul['styles_css']);
      }
      $modulausgabe[] = ' */
    ?>
                </div>
            </div>
        </td>
    <?php /*
      <td style="font-size: 2rem;">
      <!--<i data-toggle="collapse" data-target="#<?= $module_key ?>_code" class="rex-icon rex-icon-module" style="<?= $statusfarbe ?>cursor:pointer;" title="todo: statusinfo"></i>-->
      </td>
      <td style="font-size: 2rem;">
      <i data-toggle="collapse" data-target="#<?= $module_key ?>_info" class="rex-icon rex-icon-info" style="cursor:pointer;"></i>
      </td>
      <td>
      if ($moduls[$module_key]['styles_scss'] OR $moduls[$module_key]['styles_css']) {
      $modulausgabe[] = '<span class="btn btn-success" data-toggle="collapse" data-target="#'.$module_key.'_scss">'.$this->i18n('styles').'</span>'  ;
      }
      </td>
      <td>
      if ($modul['config']['status'] != 0) {
      $modulausgabe[] = '<input type="submit" class="btn btn-primary" class="rex-button" value="'.$this->i18n('modul_installieren').'" />';
      }


      </td> */
    ?>
        <td>
        <?php if ($in_repo) : ?>
                <input type="checkbox" id="<?= $module_key ?>_install_check" class="" name="install[]" value="<?= $module_key ?>" />
            <?php endif; ?>
        </td>
        <td>
    <?php if ($is_installed) : ?>
                <input disabled type="checkbox" id="<?= $module_key ?>_upload_check" class="" name="upload[]" value="<?= $module_key ?>" />
            <?php endif; ?>
        </td>
    </tr>
<?php
};










ob_start();
?>
<div id="modulsammlung">
    <div class="row">
        <form action="<?= rex_url::currentBackendPage() ?>" method="POST">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Repo</th>
                        <th>Lokal</th>
                        <th class="td_title"><?= $this->i18n('module') ?></th>
                        <th>Install<br/><input type="checkbox" name=""/></th>
                        <th>Upload<br/><input disabled type="checkbox" name=""/></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($repo_modules as &$m) {
                        $rex_themesync_render_module->call($this, $m, rex_themesync_sync::REX_THEMESYNC_REPO);
                    }
                    ?>
                    <?php
                    foreach ($local_modules as &$m) {
                        if (isset($repo_modules[$m->getKey()])) {
                            continue;
                        }
                        $rex_themesync_render_module->call($this, $m, rex_themesync_sync::REX_THEMESYNC_LOCAL);
                    }
                    ?>
                </tbody>
            </table>

            <input type="submit" class="pull-right btn btn-primary" name="sync" value="<?= $this->i18n('apply'); ?>"/>

        </form>



    </div>
</div>

<?php
$content = ob_get_contents();
ob_end_clean();

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

