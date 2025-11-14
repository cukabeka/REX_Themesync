<?php

/**
 * Handler for config.yml files in modules and templates
 * Reads and writes metadata including key, version, git info, etc.
 */
class rex_themesync_config_yml_handler {
    
    /**
     * Parse config.yml from file path
     * 
     * @param string $filePath Absolute path to config.yml
     * @return array|null Parsed config data or null if file doesn't exist
     */
    public static function read($filePath) {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }
        
        // Parse YAML - using rex_file for REDAXO compatibility
        try {
            $data = rex_file::getConfig($filePath);
            return $data;
        } catch (Exception $e) {
            // If YAML parsing fails, return null
            return null;
        }
    }
    
    /**
     * Write config.yml to file path
     * 
     * @param string $filePath Absolute path to config.yml
     * @param array $data Configuration data to write
     * @return bool True on success, false on failure
     */
    public static function write($filePath, $data) {
        try {
            return rex_file::putConfig($filePath, $data);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Create default config.yml structure for a module
     * 
     * @param string $name Module name
     * @param string|null $key Module key (optional)
     * @param string|null $dirPath Directory path for git info extraction
     * @return array Default config structure
     */
    public static function createModuleConfig($name, $key = null, $dirPath = null) {
        $config = [
            'name' => $name,
            'description' => '',
            'version' => '1.0.0',
            'author' => '',
            'category' => 'content',
        ];
        
        if ($key !== null) {
            $config['key'] = $key;
        }
        
        // Add git information if available
        if ($dirPath && $gitInfo = self::extractGitInfo($dirPath)) {
            $config['git'] = $gitInfo;
        }
        
        $config['files'] = [
            'input' => 'input.php',
            'output' => 'output.php',
        ];
        
        return $config;
    }
    
    /**
     * Create default config.yml structure for a template
     * 
     * @param string $name Template name
     * @param string|null $key Template key (optional)
     * @param string|null $dirPath Directory path for git info extraction
     * @return array Default config structure
     */
    public static function createTemplateConfig($name, $key = null, $dirPath = null) {
        $config = [
            'name' => $name,
            'description' => '',
            'version' => '1.0.0',
            'author' => '',
        ];
        
        if ($key !== null) {
            $config['key'] = $key;
        }
        
        // Add git information if available
        if ($dirPath && $gitInfo = self::extractGitInfo($dirPath)) {
            $config['git'] = $gitInfo;
        }
        
        $config['files'] = [
            'template' => 'template.php',
        ];
        
        return $config;
    }
    
    /**
     * Extract git information from directory
     * 
     * @param string $dirPath Directory path to check for git info
     * @return array|null Git information or null if not a git repository
     */
    protected static function extractGitInfo($dirPath) {
        // Find the git repository root
        $gitDir = $dirPath;
        $maxDepth = 10;
        $depth = 0;
        
        while ($depth < $maxDepth && !is_dir($gitDir . '/.git')) {
            $parent = dirname($gitDir);
            if ($parent === $gitDir) {
                // Reached filesystem root
                return null;
            }
            $gitDir = $parent;
            $depth++;
        }
        
        if (!is_dir($gitDir . '/.git')) {
            return null;
        }
        
        $gitInfo = [];
        
        // Get commit hash
        $commit = @shell_exec('cd ' . escapeshellarg($gitDir) . ' && git rev-parse HEAD 2>/dev/null');
        if ($commit) {
            $gitInfo['commit'] = trim($commit);
        }
        
        // Get branch name
        $branch = @shell_exec('cd ' . escapeshellarg($gitDir) . ' && git rev-parse --abbrev-ref HEAD 2>/dev/null');
        if ($branch) {
            $gitInfo['branch'] = trim($branch);
        }
        
        // Get last commit date
        $lastUpdate = @shell_exec('cd ' . escapeshellarg($gitDir) . ' && git log -1 --format=%cI 2>/dev/null');
        if ($lastUpdate) {
            $gitInfo['last_update'] = trim($lastUpdate);
        }
        
        return !empty($gitInfo) ? $gitInfo : null;
    }
    
    /**
     * Update git information in existing config
     * 
     * @param array $config Existing config data
     * @param string $dirPath Directory path to extract git info from
     * @return array Updated config with fresh git info
     */
    public static function updateGitInfo($config, $dirPath) {
        if ($gitInfo = self::extractGitInfo($dirPath)) {
            $config['git'] = $gitInfo;
        } else {
            // Remove git info if no longer in a git repo
            unset($config['git']);
        }
        
        return $config;
    }
    
    /**
     * Increment version number (semantic versioning)
     * 
     * @param string $version Current version (e.g., "1.2.3")
     * @param string $level Level to increment: 'major', 'minor', or 'patch'
     * @return string New version
     */
    public static function incrementVersion($version, $level = 'patch') {
        $parts = explode('.', $version);
        $major = isset($parts[0]) ? (int)$parts[0] : 0;
        $minor = isset($parts[1]) ? (int)$parts[1] : 0;
        $patch = isset($parts[2]) ? (int)$parts[2] : 0;
        
        switch ($level) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
            default:
                $patch++;
                break;
        }
        
        return "$major.$minor.$patch";
    }
}
