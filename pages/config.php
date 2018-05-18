
<?php

#echo rex_view::info('Konfiguration in der Datei <code>'. htmlentities(rex_path::addonData('themesync', 'repo.yml')).'</code> bearbeiten.');



$func = rex_request('func', 'string');

// Konfiguration speichern
if ($func == 'update') {

    $this->setConfig(rex_post('settings', [
        // ====================================================== Set options
        ['host', 'string'],
        ['user', 'string'],
        ['pass', 'string'],
        ['dir', 'string'],

        #['local_dir', 'string'],
        ['classname', 'string']


    ]));

    echo rex_view::success($this->i18n('config_saved'));
}

// Config-Werte bereitstellen
$Values = array();
$Values['host'] = $this->getConfig('host');
$Values['user'] = $this->getConfig('user');
$Values['pass'] = $this->getConfig('pass');
$Values['dir'] = $this->getConfig('dir');
#$Values['local_dir'] = $this->getConfig('local_path');
$Values['classname'] = $this->getConfig('classname');


$content .= '<fieldset><legend>' . $this->i18n('themesync_title') . ' Settings</legend>';


// Text
$formElements = [];
$n = [];
$n['label'] = '<label for="host">' . htmlspecialchars_decode($this->i18n('host')) . '</label>';
$n['field'] = '<input class="form-control" type="text" id="host" name="settings[host]" value="' . $Values['host'] . '" />';
$n['note'] = htmlspecialchars_decode($this->i18n('host_help'));
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Text
$formElements = [];
$n = [];
$n['label'] = '<label for="user">' . htmlspecialchars_decode($this->i18n('user')) . '</label>';
$n['field'] = '<input class="form-control" type="text" id="user" name="settings[user]" value="' . $Values['user'] . '" />';
$n['note'] = htmlspecialchars_decode($this->i18n('user_help'));
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Text
$formElements = [];
$n = [];
$n['label'] = '<label for="pass">' . htmlspecialchars_decode($this->i18n('pass')) . '</label>';
$n['field'] = '<input class="form-control" type="text" id="pass" name="settings[pass]" value="' . $Values['pass'] . '" />';
$n['note'] = htmlspecialchars_decode($this->i18n('pass_help'));
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Select
$formElements = [];
$n = [];
$n['label'] = '<label for="classname">' . $this->i18n('classname') . '</label>';
$select = new rex_select();
$select->setId('classname');
$select->setAttribute('class', 'classname');
$select->setName('settings[classname]');
$select->addOption('rex_themesync_repo_ftp','rex_themesync_repo_ftp');
$select->setSelected($this->getConfig('classname'));
$n['field'] = $select->get();
$n['note'] = htmlspecialchars_decode($this->i18n('classname_help'));
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');




// Textarea
/*
$formElements = [];
$n = [];
$n['label'] = '<label for="repo_yml">' . htmlspecialchars_decode($this->i18n('repo_yml')) . '</label>';
$n['field'] = '<textarea class="form-control" type="text" id="feed_description" style="min-height:3em !important;" name="settings[repo_yml]">' . $Values['repo_yml'] . '</textarea>';
$n['note'] = htmlspecialchars_decode($this->i18n('repo_yml_help'));
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');
*/


$content .= '</fieldset>';



// Save-Button
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $this->i18n('save') . '">' . $this->i18n('save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

// Ausgabe Section
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('settings'), false);
$fragment->setVar('class', 'edit', false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');



$content = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="func" value="update" />
    ' . $content . '
</form>
';

echo $content;
