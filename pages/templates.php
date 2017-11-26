<?php
$manager = new rex_themesync_template_manager($this);

$manager->init();

$manager->action();

ob_implicit_flush(false);
ob_start();
?>

<div id="themesync templates">
    <?= rex_view::info('<strong>Repo:</strong> '.$manager->getRepo()->getRepoInfo()) ?>
    <div class="row">
        <form action="<?= rex_url::currentBackendPage() ?>" method="POST">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Repo</th>
                        <th>Lokal</th>
                        <th class="td_title"><?= $this->i18n('template') ?></th>
                        <th>Install<br/>
                        <th>Upload</th>
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
    

<?php
$content = ob_get_contents();
ob_end_clean();

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

