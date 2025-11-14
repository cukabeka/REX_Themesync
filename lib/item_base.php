<?php

/**
 * Basis-Klasse für Modul-Item / Template-Item
 * 
 * Wird über Name identifiziert; der $key ist im wesentlichen für 
 * technische zwecke, wie ids im HTML etc.
 * 
 * Enhanced for REDAXO 5.15+ with numeric key support from folder names
 */
abstract class rex_themesync_item_base {
    private $name, $key;
    private $numericKey = null; // Numeric key from folder name (e.g., "01", "02")
    private $config = null; // Config.yml data
    
    /* @var $repo rex_themesync_repo */
    protected $repo = null;
    
    protected $type = '';
    
    public function __construct($type, $name, &$repo) {
        $this->name = $name;
        $this->repo = $repo;
        $this->type = $type;
        
        // Original key generation for HTML IDs etc.
        $this->key = preg_replace('`[^a-z0-9\\.]+`', '_', strtolower($this->name));
        $this->key = preg_replace('`_\\.`', '_', $this->key);
        $this->key = preg_replace('`\\._`', '_', $this->key);
        $this->key = preg_replace('`_$`', '', $this->key);
        
        // Extract numeric key from folder name (e.g., "01-module-name" → "01")
        $this->numericKey = rex_themesync_key_extractor::extractKey($name);
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
    
    public function hasRepoCache($k) {
        return isset($this->repoCache[$k]);
    }
    
    
    
    
    
    
    public function getName() {
        return $this->name;
    }
    
    public function getKey() {
        return $this->key;
    }
    
    /**
     * Get numeric key from folder name (e.g., "01", "02", "0010")
     * Used for REDAXO 5.15+ key field support
     * 
     * @return string|null Numeric key or null if not present
     */
    public function getNumericKey() {
        return $this->numericKey;
    }
    
    /**
     * Set numeric key manually
     * 
     * @param string|null $key Numeric key
     */
    public function setNumericKey($key) {
        $this->numericKey = $key;
    }
    
    /**
     * Load config.yml for this item
     * 
     * @return array|null Config data or null if not found
     */
    public function loadConfig() {
        if ($this->config === null) {
            $configPath = $this->getConfigPath();
            if ($configPath && file_exists($configPath)) {
                $this->config = rex_themesync_config_yml_handler::read($configPath);
                
                // If config has a key field, use it
                if ($this->config && isset($this->config['key'])) {
                    $this->numericKey = $this->config['key'];
                }
            } else {
                $this->config = false; // Mark as attempted but not found
            }
        }
        
        return $this->config ?: null;
    }
    
    /**
     * Get config data (loads if not already loaded)
     * 
     * @return array|null Config data or null
     */
    public function getConfig() {
        $this->loadConfig();
        return $this->config ?: null;
    }
    
    /**
     * Save config.yml for this item
     * 
     * @param array $data Config data to save
     * @return bool Success
     */
    public function saveConfig($data) {
        $configPath = $this->getConfigPath();
        if (!$configPath) {
            return false;
        }
        
        $this->config = $data;
        return rex_themesync_config_yml_handler::write($configPath, $data);
    }
    
    /**
     * Get path to config.yml file
     * 
     * @return string|null Path to config.yml or null if not available
     */
    protected function getConfigPath() {
        if (method_exists($this->repo, 'getItemConfigPath')) {
            return $this->repo->getItemConfigPath($this);
        }
        return null;
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


