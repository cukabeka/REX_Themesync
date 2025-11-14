<?php

/**
 * Class for module matching and pairing between repository and REDAXO database
 * Inspired by D2U Helper module system
 */
class rex_themesync_match_module {
    /** @var string Module folder name (e.g., "01-text-bild-video-link") */
    private $folder_name = '';
    
    /** @var string Module name */
    private $name = '';
    
    /** @var string|null Numeric key from folder name */
    private $key = null;
    
    /** @var string|null Version from config.yml */
    private $version = null;
    
    /** @var int REDAXO module ID (0 if not installed) */
    private $rex_module_id = 0;
    
    /** @var array|null Config.yml data */
    private $config = null;
    
    /** @var rex_themesync_source Repository source */
    private $repo = null;
    
    /** @var bool True if autoupdate is activated */
    private $autoupdate = false;
    
    /**
     * Constructor
     * 
     * @param string $folder_name Folder name (e.g., "01-text-bild-video-link")
     * @param string $name Display name
     * @param string|null $version Version string (e.g., "2.1.0")
     * @param rex_themesync_source $repo Repository source
     */
    public function __construct($folder_name, $name, $version, &$repo) {
        $this->folder_name = $folder_name;
        $this->name = $name;
        $this->version = $version;
        $this->repo = $repo;
        
        // Extract key from folder name
        $this->key = rex_themesync_key_extractor::extractKey($folder_name);
        
        // Try to find paired REDAXO module
        $this->findPairedModule();
    }
    
    /**
     * Find paired REDAXO module based on key or name
     */
    private function findPairedModule() {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'module');
        
        // Try to find by themesync_key attribute first
        $sql->setWhere('`attributes` LIKE ' . $sql->escape('%"themesync_key":"' . $this->folder_name . '"%'));
        $sql->select();
        
        if ($sql->getRows() > 0) {
            $this->rex_module_id = (int) $sql->getValue('id');
            $attributes = json_decode($sql->getValue('attributes'), true);
            if (is_array($attributes) && isset($attributes['themesync_autoupdate'])) {
                $this->autoupdate = $attributes['themesync_autoupdate'];
            }
            return;
        }
        
        // Fallback: try to find by name similarity
        $sql->setWhere('`name` = ' . $sql->escape($this->name));
        $sql->select();
        
        if ($sql->getRows() > 0) {
            $this->rex_module_id = (int) $sql->getValue('id');
        }
    }
    
    /**
     * Get folder name
     * 
     * @return string
     */
    public function getFolderName() {
        return $this->folder_name;
    }
    
    /**
     * Get display name
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Get numeric key
     * 
     * @return string|null
     */
    public function getKey() {
        return $this->key;
    }
    
    /**
     * Get version
     * 
     * @return string|null
     */
    public function getVersion() {
        return $this->version;
    }
    
    /**
     * Get REDAXO module ID
     * 
     * @return int
     */
    public function getRedaxoId() {
        return $this->rex_module_id;
    }
    
    /**
     * Check if module is installed in REDAXO
     * 
     * @return bool
     */
    public function isInstalled() {
        return $this->rex_module_id > 0;
    }
    
    /**
     * Check if module needs update
     * 
     * @return bool
     */
    public function isUpdateNeeded() {
        if (!$this->isInstalled()) {
            return false;
        }
        
        // Get current REDAXO module
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'module');
        $sql->setWhere(['id' => $this->rex_module_id]);
        $sql->select();
        
        if ($sql->getRows() === 0) {
            return false;
        }
        
        // Check if update is needed based on updatedate
        $attributes = json_decode($sql->getValue('attributes'), true);
        if (is_array($attributes) && isset($attributes['themesync_version'])) {
            $installed_version = $attributes['themesync_version'];
            // Simple version comparison
            return version_compare($this->version, $installed_version, '>');
        }
        
        return true; // Unknown version, assume update needed
    }
    
    /**
     * Check if autoupdate is activated
     * 
     * @return bool
     */
    public function isAutoupdateActivated() {
        return $this->autoupdate;
    }
    
    /**
     * Activate autoupdate
     */
    public function activateAutoupdate() {
        $this->autoupdate = true;
        $this->saveAttributes();
    }
    
    /**
     * Disable autoupdate
     */
    public function disableAutoupdate() {
        $this->autoupdate = false;
        $this->saveAttributes();
    }
    
    /**
     * Install or update module in REDAXO
     * 
     * @param int $rex_module_id Optional REDAXO module ID to pair with
     * @return bool Success
     */
    public function install($rex_module_id = 0) {
        if ($rex_module_id > 0) {
            $this->rex_module_id = $rex_module_id;
        }
        
        // Load module from repository
        $module = new rex_themesync_module($this->folder_name, $this->repo);
        if (!$module->isExisting()) {
            return false;
        }
        
        $module->loadInputOutput();
        
        // Create or update REDAXO module
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'module');
        $sql->setValue('name', $this->name);
        $sql->setValue('input', $module->getInput());
        $sql->setValue('output', $module->getOutput());
        
        if ($this->rex_module_id === 0) {
            // New module
            $sql->addGlobalCreateFields();
            $sql->insert();
            $this->rex_module_id = (int) $sql->getLastId();
        } else {
            // Update existing module
            $sql->addGlobalUpdateFields();
            $sql->setWhere(['id' => $this->rex_module_id]);
            $sql->update();
        }
        
        // Save attributes
        $this->saveAttributes();
        
        // Delete cache
        rex_delete_cache();
        
        return true;
    }
    
    /**
     * Unlink module from REDAXO module
     */
    public function unlink() {
        if ($this->rex_module_id === 0) {
            return;
        }
        
        $sql = rex_sql::factory();
        $sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'module '
            . 'SET `attributes` = NULL '
            . 'WHERE `id` = ' . $this->rex_module_id);
        
        $this->rex_module_id = 0;
        $this->autoupdate = false;
    }
    
    /**
     * Save module attributes to REDAXO database
     */
    private function saveAttributes() {
        if ($this->rex_module_id === 0) {
            return;
        }
        
        $attributes = [
            'themesync_key' => $this->folder_name,
            'themesync_version' => $this->version,
            'themesync_autoupdate' => $this->autoupdate,
        ];
        
        $sql = rex_sql::factory();
        $sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'module '
            . "SET `attributes` = '" . json_encode($attributes) . "' "
            . 'WHERE `id` = ' . $this->rex_module_id);
    }
    
    /**
     * Load config from repository
     * 
     * @return array|null
     */
    public function getConfig() {
        if ($this->config === null) {
            $module = new rex_themesync_module($this->folder_name, $this->repo);
            $this->config = $module->getConfig();
        }
        return $this->config;
    }
}
