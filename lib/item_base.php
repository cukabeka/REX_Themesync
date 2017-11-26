<?php

/**
 * Basis-Klasse für Modul-Item / Template-Item
 * 
 * Wird über Name identifiziert; der $key ist im wesentlichen für 
 * technische zwecke, wie ids im HTML etc.
 */
abstract class rex_themesync_item_base {
    private $name, $key;
    /* @var $repo rex_themesync_repo */
    protected $repo = null;
    
    protected $type = '';
    
    public function __construct($type, $name, &$repo) {
        $this->name = $name;
        $this->repo = $repo;
        $this->type = $type;
        
        $this->key = preg_replace('`[^a-z0-9\\.]+`', '_', strtolower($this->name));
        //$this->key = preg_replace('`[^A-Za-z0-9\\-]`', '_', $this->key);
        #$this->key = preg_replace('`_-`', '_', $this->key);
        #$this->key = preg_replace('`-_`', '_', $this->key);
        $this->key = preg_replace('`_\\.`', '_', $this->key);
        $this->key = preg_replace('`\\._`', '_', $this->key);
        $this->key = preg_replace('`_$`', '', $this->key);
    }
    
    
    public function getType() {
        return $this->type;
    }
    
    /**
     * Der Repo Cache kann dafür genutzt werden, dass das Repo repospezifische
     * Daten zum Modul ablegen kann.
     * @var array
     */
    
    private $repoCache = [];
    
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
    
    public function getReadme() {
        return $this->getFile('README.md');
    }
    
    
    abstract public function isExisting();
    
    /**
     * Dateiinhalt zurückgeben
     */
    abstract public function getFile($path);
    
    /**
     * Dateiinhalt herunterladen
     */
    abstract public function saveFile($path, $destination);
    
    
    
}


