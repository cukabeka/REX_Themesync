<?php


class rex_themesync_module {
    private $name, $key;
    /* @var $repo rex_themesync_repo */
    private $repo = null;
    
    private $inputOutputLoaded = false;
    private $input = null;
    private $output = null;
    
    private $repoCache = [];
    
    public function __construct($name, &$repo) {
        $this->name = $name;
        $this->repo = $repo;
        
        $this->key = preg_replace('`[^a-z0-9\\.]+`', '_', strtolower($this->name));
        //$this->key = preg_replace('`[^A-Za-z0-9\\-]`', '_', $this->key);
        #$this->key = preg_replace('`_-`', '_', $this->key);
        #$this->key = preg_replace('`-_`', '_', $this->key);
        $this->key = preg_replace('`_\\.`', '_', $this->key);
        $this->key = preg_replace('`\\._`', '_', $this->key);
        $this->key = preg_replace('`_$`', '', $this->key);
    }
    
    public function setRepoCache($k, &$v) {
        $this->repoCache[$k] = $v;
    }
    
    public function &getRepoCache($k) {
        static $null = null;
        if (!isset($this->repoCache[$k])) {
            return $null;
        }
        return $this->repoCache[$k];
    }
    
    public function &hasRepoCache($k) {
        return isset($this->repoCache[$k]);
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getKey() {
        return $this->key;
    }
    
    public function setOutput($output) {
        $this->output = $output;
    }
    
    public function setInput($input) {
        $this->input = $input;
    }
    
    public function getOutput() {
        $this->loadInputOutput();
        return $this->output;
    }
    
    public function getInput() {
        $this->loadInputOutput();
        return $this->input;
    }
    
    public function loadInputOutput() {
        if ($this->inputOutputLoaded) {
            return;
        }
        $this->inputOutputLoaded = true;
        return $this->repo->loadInputOutput($this);
    }
    
    public function isExisting() {
        return $this->repo->isExisting($this);
    }
          
    
    public function getFile($path) {
        if (method_exists($this->repo, 'getFileContents')) {
            return $this->repo->getFileContents($this->name . '/' . $path);
        }
        return null;
    }
    
    public function saveFile($path, $destination) {
        if (method_exists($this->repo, 'downloadFile')) {
            return $this->repo->downloadFile($this->name . '/' . $path, $destination);
        }
        return false;
    }
    
    public function getReadme() {
        return $this->getFile('README.md');
    }
    
    
}


