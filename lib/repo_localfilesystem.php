<?php

/**
 * Ein lokaler Ordner als Repository
 * Unterstützt Theme-Addon Pfade (/theme/) und config.yml
 */
class rex_themesync_repo_localfilesystem extends rex_themesync_repo {
    #use rex_themesync_has_files;
    
    private $repoDir;
    private $useThemePaths = false;
    
    public function __construct($repoConfig = []) {
        parent::__construct(self::REPO, $repoConfig);
        
        // Check if we should use theme addon paths
        if (isset($repoConfig['use_theme_paths']) && $repoConfig['use_theme_paths']) {
            $this->useThemePaths = true;
        }
        
        // Determine repository directory
        if ($this->useThemePaths && rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
            // Use theme addon paths
            $this->repoDir = rex_path::base('theme/');
        } else {
            // Use configured path or default
            $repoPath = isset($repoConfig['repo']) ? $repoConfig['repo'] : 'repository/';
            
            // Check if absolute path
            if (substr($repoPath, 0, 1) === '/') {
                $this->repoDir = $repoPath;
            } else {
                $this->repoDir = rex_path::addonData('themesync', $repoPath);
            }
        }
        
        if (substr($this->repoDir, -1) !== '/') {
            $this->repoDir .= '/';
        }
        
        // Ensure directories exist
        $this->ensureDirectories();
    }
    
    /**
     * Ensure module and template directories exist
     */
    protected function ensureDirectories() {
        $modulesDir = $this->repoDir . 'modules/';
        $templatesDir = $this->repoDir . 'templates/';
        
        if (!is_dir($modulesDir)) {
            @mkdir($modulesDir, 0755, true);
        }
        
        if (!is_dir($templatesDir)) {
            @mkdir($templatesDir, 0755, true);
        }
    }
    
    protected function _list($type) {
        $dirList = glob($this->repoDir.$type.'s/*', GLOB_NOSORT|GLOB_ONLYDIR|GLOB_MARK);
        if (!is_array($dirList)) {
            return;
        }
        foreach ($dirList as $path) {
            $name = basename($path);
            if ($type === 'module') {
                $module = $this->createModule($name);
                // Load config.yml to get additional metadata
                $module->loadConfig();
            } else if ($type === 'template') {
                $template = $this->createTemplate($name);
                // Load config.yml to get additional metadata
                $template->loadConfig();
            }
        }
    }

    public function getFileContents($type, $path) {
        $fullPath = $this->repoDir . $type.'s/' . $path;
        if (!file_exists($fullPath)) {
            return false;
        }
        return file_get_contents($fullPath);
    }
    
    public function putFileContents($type, $path, $content) {
        $fullPath = $this->repoDir . $type.'s/' . $path;
        
        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        return file_put_contents($fullPath, $content) !== false;
    }
    
    /**
     * Get path to item's config.yml file
     * 
     * @param rex_themesync_item_base $item
     * @return string Path to config.yml
     */
    public function getItemConfigPath($item) {
        return $this->repoDir . $item->getType() . 's/' . $item->getName() . '/config.yml';
    }
    
    /**
     * Get directory path for an item
     * 
     * @param rex_themesync_item_base $item
     * @return string Directory path
     */
    public function getItemDirPath($item) {
        return $this->repoDir . $item->getType() . 's/' . $item->getName() . '/';
    }


    protected function _isExisting(&$item) {
        $type = $item->getType();
        return is_dir($this->repoDir . $type.'s/' . $item->getName());
    }


    public function getRepoInfo($short = false) {
        $mode = $this->useThemePaths ? 'Theme-Addon' : 'Repository';
        $relativePath = rex_themesync_path_resolver::getRelativePath($this->repoDir);
        return $mode . ': <code>'.htmlentities($relativePath).'</code>';
    }


    
    protected function makeItemDir(&$item) {
        $dir = $this->repoDir . $item->getType() .'s/' . $item->getName();
        
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return false;
            }
        }
        return true;
    }
    
}