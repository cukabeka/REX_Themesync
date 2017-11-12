<?php

/* @var $repo rex_themesync_repo */
/* @var $local rex_themesync_local */
$repo  = rex_themesync_repo::get_repo();
$local = rex_themesync_repo::get_local();



$repo_modules  = $repo->listModules();
$local_modules = $local->listModules();

/*
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
    $local->resetModules();
    $local_modules = $local->listModules();
}
*/





$rex_themesync_render_module = function (&$m, $mode) use($local_modules, $repo_modules) {
    $module_key = $m->getKey();
    $statusfarbe = '#f8a8e8;';
    if ($mode === rex_themesync_repo::REPO) {
        #echo $module_key, isset($local_modules[$module_key]) ? ' ja' : ' nein', '<br/>';
        $is_installed = isset($local_modules[$module_key]);
        $in_repo = true;
    } else {
        $is_installed = true;
        $in_repo = isset($repo_modules[$module_key]);
    }
    
    $pd = new Parsedown();
    $info = $m->getReadme();
    $info = $info ? $pd->text($info) : '';
    ?>
<tr class="module_row" data-key="<?= $module_key ?>" data-name="<?= htmlentities($m->getName()) ?>" >
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









ob_implicit_flush(false);
ob_start();
?>
    <!-- TODO: besserer weg um css zu integrieren-->
<!--<style>
.Differences {
    font-size:90%;
	width: 100%;
	border-collapse: collapse;
	border-spacing: 0;
	empty-cells: show;
}

.Differences thead th {
	text-align: left;
	border-bottom: 1px solid #000;
	background: #aaa;
	color: #000;
	padding: 4px;
}
.Differences tbody th {
	text-align: right;
	background: #ccc;
	width: 4em;
	padding: 1px 2px;
	border-right: 1px solid #000;
	vertical-align: top;
	font-size: 13px;
}

.Differences td {
	padding: 1px 2px;
	font-family: Consolas, monospace;
	font-size: 13px;
}

.DifferencesSideBySide .ChangeInsert td.Left {
	background: #dfd;
}

.DifferencesSideBySide .ChangeInsert td.Right {
	background: #cfc;
}

.DifferencesSideBySide .ChangeDelete td.Left {
	background: #f88;
}

.DifferencesSideBySide .ChangeDelete td.Right {
	background: #faa;
}

.DifferencesSideBySide .ChangeReplace .Left {
	background: #fe9;
}

.DifferencesSideBySide .ChangeReplace .Right {
	background: #fd8;
}

.Differences ins, .Differences del {
	text-decoration: none;
}

.DifferencesSideBySide .ChangeReplace ins, .DifferencesSideBySide .ChangeReplace del {
	background: #fc0;
}

.Differences .Skipped {
	background: #f7f7f7;
}

.DifferencesInline .ChangeReplace .Left,
.DifferencesInline .ChangeDelete .Left {
	background: #fdd;
}

.DifferencesInline .ChangeReplace .Right,
.DifferencesInline .ChangeInsert .Right {
	background: #dfd;
}

.DifferencesInline .ChangeReplace ins {
	background: #9e9;
}

.DifferencesInline .ChangeReplace del {
	background: #e99;
}

pre {
	width: 100%;
	overflow: auto;
}
</style>-->

<div id="modulsammlung">
    <div class="row">
        <form action="<?= rex_url::currentBackendPage() ?>" method="POST">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Repo</th>
                        <th>Lokal</th>
                        <th class="td_title"><?= $this->i18n('module') ?></th>
                        <th></th>
                        <th></th>
                        <th>Install<br/><!--<input type="checkbox" name=""/>--></th>
                        <th>Upload<br/><!--<input disabled type="checkbox" name=""/>--></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($repo_modules as &$m) {
                        $rex_themesync_render_module->call($this, $m, rex_themesync_repo::REPO);
                    }
                    ?>
                    <?php
                    foreach ($local_modules as &$m) {
                        if (isset($repo_modules[$m->getKey()])) {
                            continue;
                        }
                        $rex_themesync_render_module->call($this, $m, rex_themesync_repo::LOCAL);
                    }
                    ?>
                </tbody>
            </table>

            <input type="submit" class="pull-right btn btn-primary" name="sync" value="<?= $this->i18n('apply'); ?>"/>

        </form>



    </div>
</div>
    
<script>
    function themesync_modul_details(el) {
        var $mrow = $(el).closest('.module_row');
        var name = $mrow.data('name');
        var key = $mrow.data('key');
        var $code = $mrow.find('#'+key+'_code');
        
        //console.log(name);
        if ($code.text()==='') {
            //console.log(name+' go');
            $code.text('lade...').load("index.php", "page=themesync/sync&rex-api-call=module_info&name="+name);
        }
    }
</script>

<?php
$content = ob_get_contents();
ob_end_clean();

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

