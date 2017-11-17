<?php

trait rex_themesync_has_files {
    abstract public function downloadFile($type, $path, $destination);
    abstract public function getFileContents($type, $path);
}

abstract class rex_themesync_source {
    protected $repoConfig;
    protected $moduleCache = null, $templateCache = null;
    protected $type = null;
    
    const LOCAL = 1;
    const REPO = 2;
    
    
    
    public function __construct($type, $repoConfig = []) {
        $this->type = $type;
        $this->repoConfig = $repoConfig;
    }
    
    
    
    
    
    protected function createModule($name) {
        $module = new rex_themesync_module($name, $this);
        $key = $module->getKey();
        if (isset($this->moduleCache[$key])) {
            throw new \Exception('Name / Key Conflict with '.$name .' and '.$this->moduleCache[$key]->getName());
        }
        $this->moduleCache[$key] = $module;
    }
    
    public function resetModules() {
        $this->moduleCache = null;
    }
    
    public function &listModules() {
        if (is_null($this->moduleCache)) {
            $this->_list('module');
        }
        return $this->moduleCache;
    }
    
    
    protected function createTemplate($name) {
        $template = new rex_themesync_template($name, $this);
        $key = $template->getKey();
        if (isset($this->templateCache[$key])) {
            throw new \Exception('Name / Key Conflict with '.$name .' and '.$this->templateCache[$key]->getName());
        }
        $this->templateCache[$key] = $template;
    }
    
    public function resetTemplates() {
        $this->templateCache = null;
    }
    
    public function &listTemplates() {
        if (is_null($this->templateCache)) {
            $this->_list('template');
        }
        return $this->templateCache;
    }
    
    
    
    
    public function isModuleExisting(rex_themesync_module &$module) {
        return $this->_isExisting($module);
    }
    
    public function isTemplateExisting(rex_themesync_template &$template) {
        return $this->_isExisting($template);
    }
    
    /**
     * input und output wird im modul gesetzt
     * @param rex_themesync_module $module
     */
    abstract protected function loadModuleInputOutput(rex_themesync_module &$module);
    abstract protected function loadTemplateContent(rex_themesync_template &$template);
    
    abstract protected function _isExisting(&$item);
    
    abstract protected function _list($type);
    
    abstract public function getRepoInfo($short = false);
    
}