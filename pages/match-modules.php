<?php
$manager = new rex_themesync_module_manager($this);

$manager->init();

// Create match module manager
$match_manager = new rex_themesync_match_module_manager($manager->getRepo(), $this);

// Handle actions
$module_folder = rex_request('module_folder', 'string');
$paired_module = rex_request('pair_' . $module_folder, 'int');
$function = rex_request('function', 'string');

if ($module_folder !== '') {
    $match_manager->doActions($module_folder, $function, $paired_module);
}

// Show manager list
$match_manager->showManagerList();

