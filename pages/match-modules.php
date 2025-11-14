<?php
$manager = new rex_themesync_module_manager($this);

$manager->init();

$manager->action();

#ob_implicit_flush(false);
#ob_start();

#dump($manager->getRepo()->listModules());
$modules = [];
foreach ($manager->getRepo()->listModules() as $key => $obj) {
      #dump($obj->getName());

      $modules[] = new D2UModule(
      #"00-1", // $d2u_module_id D2U Module ID, if known, else set 0
      #"Umbruch ganze Breite",  // $name Modules title or name
      # 3 //revision

      #$obj->getValue("key"),
      #$obj->getValue("name"),
      $key,
      $obj->getName(),
      "untracked (ftp)"//.$obj->getRevision()

    );
}
$d2u_module_manager = new D2UModuleManager($modules);


// D2UModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if($d2u_module_id != "") {
	$d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
}

// D2UModuleManager show list
$d2u_module_manager->showManagerList();
