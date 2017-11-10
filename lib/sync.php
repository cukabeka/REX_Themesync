<?php


abstract class rex_themesync_sync {
    protected $repoConfig;
    protected $modules = null;
    
    const REX_THEMESYNC_LOCAL = 1;
    const REX_THEMESYNC_REPO = 2;
    
    public function __construct($repoConfig = []) {
        $this->repoConfig = $repoConfig;
    }
    
    public function &listModules() {
        if (is_null($this->modules)) {
            $this->modules = $this->_listModules();
        }
        return $this->modules;
    }
    
    public function loadModule(rex_themesync_module &$module) {
        return $this->_loadModule($module);
    }
    
    abstract protected function _listModules();
    
    abstract protected function _loadModule(rex_themesync_module &$module);
    
    
}