
<?php

#echo rex_view::info('Konfiguration in der Datei <code>'. htmlentities(rex_path::addonData('themesync', 'repo.yml')).'</code> bearbeiten.');

// Show current path resolver info
$pathInfo = rex_themesync_path_resolver::getRepositoryInfo();
$infoMessage = '<strong>Aktueller Modus:</strong> ' . ($pathInfo['using_theme_addon'] ? 'Theme-Addon' : 'Repository') . '<br/>';
$infoMessage .= '<strong>Module-Pfad:</strong> <code>' . htmlentities(rex_themesync_path_resolver::getRelativePath($pathInfo['modules_path'])) . '</code><br/>';
$infoMessage .= '<strong>Template-Pfad:</strong> <code>' . htmlentities(rex_themesync_path_resolver::getRelativePath($pathInfo['templates_path'])) . '</code>';
echo rex_view::info($infoMessage);


$func = rex_request('func', 'string');

// Konfiguration speichern
if ($func == 'update') {

    $this->setConfig(rex_post('settings', [
        // ====================================================== Set options
        ['host', 'string'],
        ['user', 'string'],
        ['pass', 'string'],
        ['dir', 'string'],
        ['repo', 'string'],
        ['use_theme_paths', 'bool'],
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
$Values['repo'] = $this->getConfig('repo');
$Values['use_theme_paths'] = $this->getConfig('use_theme_paths');
$Values['classname'] = $this->getConfig('classname');


$content .= '<fieldset><legend>' . $this->i18n('themesync_title') . ' Settings</legend>';

// Select Repository Type
$formElements = [];
$n = [];
$n['label'] = '<label for="classname">' . $this->i18n('classname') . '</label>';
$select = new rex_select();
$select->setId('classname');
$select->setAttribute('class', 'classname');
$select->setName('settings[classname]');
$select->addOption('FTP Repository', 'rex_themesync_repo_ftp');
$select->addOption('Local Filesystem', 'rex_themesync_repo_localfilesystem');
$select->setSelected($this->getConfig('classname'));
$n['field'] = $select->get();
$n['note'] = 'FTP für externe Server (nur Download), Local Filesystem für bidirektionale Sync';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// FTP Settings
$content .= '<div id="ftp-settings" style="display:none;">';

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
$n['field'] = '<input class="form-control" type="password" id="pass" name="settings[pass]" value="' . $Values['pass'] . '" />';
$n['note'] = htmlspecialchars_decode($this->i18n('pass_help'));
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// FTP Directory
$formElements = [];
$n = [];
$n['label'] = '<label for="dir">FTP Directory</label>';
$n['field'] = '<input class="form-control" type="text" id="dir" name="settings[dir]" value="' . $Values['dir'] . '" />';
$n['note'] = 'Pfad zum Repository-Root auf dem FTP-Server (z.B. /httpdocs/theme/)';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

$content .= '</div>'; // end ftp-settings

// Local Filesystem Settings
$content .= '<div id="local-settings" style="display:none;">';

// Checkbox for use_theme_paths
$formElements = [];
$n = [];
$n['label'] = '<label for="use_theme_paths">Theme-Addon verwenden</label>';
$n['field'] = '<input type="checkbox" id="use_theme_paths" name="settings[use_theme_paths]" value="1" ' . ($Values['use_theme_paths'] ? 'checked' : '') . ' />';
$n['note'] = 'Wenn aktiviert, werden /theme/modules/ und /theme/templates/ verwendet (empfohlen wenn Theme-Addon installiert)';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Repository Path
$formElements = [];
$n = [];
$n['label'] = '<label for="repo">Repository Pfad</label>';
$n['field'] = '<input class="form-control" type="text" id="repo" name="settings[repo]" value="' . $Values['repo'] . '" />';
$n['note'] = 'Relativer Pfad (zu /data/addons/themesync/) oder absoluter Pfad. Wird nur verwendet wenn "Theme-Addon verwenden" deaktiviert ist.';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

$content .= '</div>'; // end local-settings

$content .= '</fieldset>';

// JavaScript to show/hide relevant settings
$content .= '
<script>
jQuery(function($) {
    function toggleSettings() {
        var classname = $("#classname").val();
        if (classname === "rex_themesync_repo_ftp") {
            $("#ftp-settings").show();
            $("#local-settings").hide();
        } else if (classname === "rex_themesync_repo_localfilesystem") {
            $("#ftp-settings").hide();
            $("#local-settings").show();
        }
    }
    
    $("#classname").on("change", toggleSettings);
    toggleSettings(); // Initial state
});
</script>
';



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