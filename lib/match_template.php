<?php

/**
 * Class for template matching and pairing between repository and REDAXO database
 * Similar to rex_themesync_match_module but for templates
 */
class rex_themesync_match_template {
    /** @var string Template folder name (e.g., "01-standard") */
    private $folder_name = '';
    
    /** @var string Template name */
    private $name = '';
    
    /** @var string|null Numeric key from folder name */
    private $key = null;
    
    /** @var string|null Version from config.yml */
    private $version = null;
    
    /** @var int REDAXO template ID (0 if not installed) */
    private $rex_template_id = 0;
    
    /** @var array|null Config.yml data */
    private $config = null;
    
    /** @var rex_themesync_source Repository source */
    private $repo = null;
    
    /** @var bool True if autoupdate is activated */
    private $autoupdate = false;
    
    /**
     * Constructor
     * 
     * @param string $folder_name Folder name (e.g., "01-standard")
     * @param string $name Display name
     * @param string|null $version Version string (e.g., "1.5.0")
     * @param rex_themesync_source $repo Repository source
     */
    public function __construct($folder_name, $name, $version, &$repo) {
        $this->folder_name = $folder_name;
        $this->name = $name;
        $this->version = $version;
        $this->repo = $repo;
        
        // Extract key from folder name
        $this->key = rex_themesync_key_extractor::extractKey($folder_name);
        
        // Try to find paired REDAXO template
        $this->findPairedTemplate();
    }
    
    /**
     * Find paired REDAXO template based on key or name
     */
    private function findPairedTemplate() {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'template');
        
        // Try to find by themesync_key attribute first
        $sql->setWhere('`attributes` LIKE ' . $sql->escape('%"themesync_key":"' . $this->folder_name . '"%'));
        $sql->select();
        
        if ($sql->getRows() > 0) {
            $this->rex_template_id = (int) $sql->getValue('id');
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
            $this->rex_template_id = (int) $sql->getValue('id');
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
     * Get REDAXO template ID
     * 
     * @return int
     */
    public function getRedaxoId() {
        return $this->rex_template_id;
    }
    
    /**
     * Check if template is installed in REDAXO
     * 
     * @return bool
     */
    public function isInstalled() {
        return $this->rex_template_id > 0;
    }
    
    /**
     * Check if template needs update
     * 
     * @return bool
     */
    public function isUpdateNeeded() {
        if (!$this->isInstalled()) {
            return false;
        }
        
        // Get current REDAXO template
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'template');
        $sql->setWhere(['id' => $this->rex_template_id]);
        $sql->select();
        
        if ($sql->getRows() === 0) {
            return false;
        }
        
        // Check if update is needed based on version
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
     * Install or update template in REDAXO
     * 
     * @param int $rex_template_id Optional REDAXO template ID to pair with
     * @return bool Success
     */
    public function install($rex_template_id = 0) {
        if ($rex_template_id > 0) {
            $this->rex_template_id = $rex_template_id;
        }
        
        // Load template from repository
        $template = new rex_themesync_template($this->folder_name, $this->repo);
        if (!$template->isExisting()) {
            return false;
        }
        
        $template->loadContent();
        
        // Create or update REDAXO template
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . 'template');
        $sql->setValue('name', $this->name);
        $sql->setValue('content', $template->getContent());
        
        if ($this->rex_template_id === 0) {
            // New template
            $sql->addGlobalCreateFields();
            $sql->insert();
            $this->rex_template_id = (int) $sql->getLastId();
        } else {
            // Update existing template
            $sql->addGlobalUpdateFields();
            $sql->setWhere(['id' => $this->rex_template_id]);
            $sql->update();
        }
        
        // Save attributes
        $this->saveAttributes();
        
        // Delete cache
        rex_delete_cache();
        
        return true;
    }
    
    /**
     * Unlink template from REDAXO template
     */
    public function unlink() {
        if ($this->rex_template_id === 0) {
            return;
        }
        
        $sql = rex_sql::factory();
        $sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'template '
            . 'SET `attributes` = NULL '
            . 'WHERE `id` = ' . $this->rex_template_id);
        
        $this->rex_template_id = 0;
        $this->autoupdate = false;
    }
    
    /**
     * Save template attributes to REDAXO database
     */
    private function saveAttributes() {
        if ($this->rex_template_id === 0) {
            return;
        }
        
        $attributes = [
            'themesync_key' => $this->folder_name,
            'themesync_version' => $this->version,
            'themesync_autoupdate' => $this->autoupdate,
        ];
        
        $sql = rex_sql::factory();
        $sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'template '
            . "SET `attributes` = '" . json_encode($attributes) . "' "
            . 'WHERE `id` = ' . $this->rex_template_id);
    }
    
    /**
     * Load config from repository
     * 
     * @return array|null
     */
    public function getConfig() {
        if ($this->config === null) {
            $template = new rex_themesync_template($this->folder_name, $this->repo);
            $this->config = $template->getConfig();
        }
        return $this->config;
    }
}
