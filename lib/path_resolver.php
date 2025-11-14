<?php

/**
 * Path resolver for theme addon compatibility
 * Determines correct paths for modules and templates based on:
 * 1. Theme addon availability (primary: /theme/)
 * 2. Themesync repository (fallback: /data/addons/themesync/repository/)
 */
class rex_themesync_path_resolver {
    
    /**
     * Get base path for modules
     * Priority:
     * 1. /theme/modules/ (if theme addon available)
     * 2. /redaxo/data/addons/themesync/repository/modules/ (fallback)
     * 
     * @return string Absolute path to modules directory (with trailing slash)
     */
    public static function getModulesPath() {
        // Check if theme addon is available
        if (rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
            $path = rex_path::base('theme/modules/');
            // Create directory if it doesn't exist
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            return $path;
        }
        
        // Fallback to themesync repository
        $path = rex_path::addonData('themesync', 'repository/modules/');
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        return $path;
    }
    
    /**
     * Get base path for templates
     * Priority:
     * 1. /theme/templates/ (if theme addon available)
     * 2. /redaxo/data/addons/themesync/repository/templates/ (fallback)
     * 
     * @return string Absolute path to templates directory (with trailing slash)
     */
    public static function getTemplatesPath() {
        // Check if theme addon is available
        if (rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
            $path = rex_path::base('theme/templates/');
            // Create directory if it doesn't exist
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            return $path;
        }
        
        // Fallback to themesync repository
        $path = rex_path::addonData('themesync', 'repository/templates/');
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        return $path;
    }
    
    /**
     * Check if theme addon is available and being used
     * 
     * @return bool True if theme addon paths are being used
     */
    public static function isUsingThemeAddon() {
        return rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable();
    }
    
    /**
     * Get current repository mode info
     * 
     * @return array Info about current paths
     */
    public static function getRepositoryInfo() {
        $usingThemeAddon = self::isUsingThemeAddon();
        
        return [
            'using_theme_addon' => $usingThemeAddon,
            'modules_path' => self::getModulesPath(),
            'templates_path' => self::getTemplatesPath(),
            'mode' => $usingThemeAddon ? 'theme' : 'repository',
        ];
    }
    
    /**
     * Get relative path for display (shorter, more readable)
     * 
     * @param string $absolutePath Absolute path
     * @return string Relative path from REDAXO base
     */
    public static function getRelativePath($absolutePath) {
        $basePath = rex_path::base();
        if (strpos($absolutePath, $basePath) === 0) {
            return substr($absolutePath, strlen($basePath));
        }
        return $absolutePath;
    }
}
