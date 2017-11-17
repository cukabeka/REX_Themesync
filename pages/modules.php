<?php
$manager = new rex_themesync_module_manager($this);

$manager->init();

$manager->action();

ob_implicit_flush(false);
ob_start();
?>

<div id="themesync modules">
    <?= rex_view::info('<strong>Repo:</strong> '.$manager->getRepo()->getRepoInfo()) ?>
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
                        <!-- <th>Upload<br/><input disabled type="checkbox" name=""/></th>-->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    echo $manager->render();
                    ?>
                </tbody>
            </table>

            <input type="submit" class="pull-right btn btn-primary" name="sync" value="<?= $this->i18n('apply'); ?>"/>

        </form>



    </div>
</div>
    
<script>
    /*
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
    */
</script>

<?php
$content = ob_get_contents();
ob_end_clean();

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

