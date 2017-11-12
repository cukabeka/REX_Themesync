<?php

trait rex_themesync_has_files {
    abstract public function downloadFile($path, $destination);
    abstract public function getFileContents($path);
}

abstract class rex_themesync_repo {
    protected $repoConfig;
    protected $modules = null;
    protected $type = null;
    
    const LOCAL = 1;
    const REPO = 2;
    
    static $local = null;
    static $repo = null;
    static $config;
    
    
    static protected function load_config() {
        $file = rex_path::addonData('themesync', 'repo.yml');
        self::$config = rex_file::getConfig($file);
    }
    
    static public function &get_local() {
        if (is_null(self::$local)) {
            self::$local = new rex_themesync_local();
        }
        return self::$local;
    }
    
    // TODO: config verwenden!
    static public function &get_repo() {
        if (is_null(self::$repo)) {
            self::load_config();
            
            $classname = self::$config['classname'];
            unset(self::$config['classname']);
            self::$repo = new $classname(self::$config);
        }
        return self::$repo;
    }
    
    public function __construct($type, $repoConfig = []) {
        $this->type = $type;
        $this->repoConfig = $repoConfig;
    }
    
    protected function createModule($name) {
        $module = new rex_themesync_module($name, $this);
        $key = $module->getKey();
        if (isset($this->modules[$key])) {
            throw new \Exception('Name / Key Conflict with '.$name .' and '.$this->modules[$key]->getName());
        }
        $this->modules[$key] = $module;
    }
    
    public function resetModules() {
        $this->modules = null;
    }
    
    public function &listModules() {
        if (is_null($this->modules)) {
            $this->_listModules();
        }
        return $this->modules;
    }
    
    public function isExisting(rex_themesync_module &$module) {
        return $this->_isExisting($module);
    }
    
    /**
     * input und output wird im modul gesetzt
     * @param rex_themesync_module $module
     */
    public function loadInputOutput(rex_themesync_module &$module) {
        return $this->_loadInputOutput($module);
    }
    
    abstract protected function _loadInputOutput(rex_themesync_module &$module);
    
    #abstract public function getInput(rex_themesync_module &$module);
    #abstract public function getOutput(rex_themesync_module &$module);
    
    abstract protected function _isExisting(rex_themesync_module &$module);
    
}