<?php

/**
 * Ein lokaler Ordner als Repository
 * Unterstützt zwei Modi:
 * - Relativ: data/addons/themesync/{repo_path} (Legacy)
 * - Absolut: theme/modules/, theme/templates/ (GitHub Installer kompatibel, Theme-Addon kompatibel)
 * - Absolut: assets/modules/, assets/templates/ (Fallback)
 */
class rex_themesync_repo_localfilesystem extends rex_themesync_repo {
    #use rex_themesync_has_files;
    
    private $repoDir;
    private $baseDir;
    
    public function __construct($repoConfig = []) {
        parent::__construct(self::REPO, $repoConfig);
        
        // Bestimme Base-Verzeichnis basierend auf Konfiguration
        $repoPath = $repoConfig['repo'] ?? 'repo/';
        
        // Priorisierte Verzeichnisse (in Reihenfolge)
        $possibleDirs = [
            // 1. Theme-Addon Kompatibilität (bevorzugt)
            rex_path::base('theme/modules/'),
            rex_path::base('theme/templates/'),
            // 2. Assets/modules (GitHub Installer Struktur)
            rex_path::base('assets/modules/'),
            rex_path::base('assets/templates/'),
            // 3. Legacy: Addon-Data (Fallback)
            rex_path::addonData('themesync', $repoPath)
        ];
        
        // Nutze das erste existierende Verzeichnis
        $this->baseDir = null;
        foreach ($possibleDirs as $dir) {
            if (is_dir($dir)) {
                $this->baseDir = $dir;
                break;
            }
        }
        
        // Fallback: Erstelle das Theme-Verzeichnis wenn nichts existiert
        if (is_null($this->baseDir)) {
            $this->baseDir = rex_path::base('theme/modules/');
            if (!is_dir($this->baseDir)) {
                @mkdir($this->baseDir, 0755, true);
            }
        }
        
        $this->repoDir = $this->baseDir;
        if (substr($this->repoDir, -1) !== '/') {
            $this->repoDir .= '/';
        }
    }

    protected function _list($type) {
        // Suche nach modules/ oder templates/ Ordnern (GitHub Installer Struktur)
        $searchDirs = [
            $this->repoDir . $type . 's/',      // modules/, templates/
            $this->repoDir . $type . '/',       // module/, template/ (Legacy)
        ];
        
        foreach ($searchDirs as $searchDir) {
            $dirList = @glob($searchDir . '*', GLOB_NOSORT|GLOB_ONLYDIR|GLOB_MARK);
            if (is_array($dirList) && !empty($dirList)) {
                foreach ($dirList as $path) {
                    $name = basename(rtrim($path, '/'));
                    if ($type === 'module') {
                        $this->createModule($name);
                    } else if ($type === 'template') {
                        $this->createTemplate($name);
                    }
                }
                return;
            }
        }
    }

    public function getFileContents($type, $path) {
        // Versuche verschiedene Pfade
        $paths = [
            $this->repoDir . $type . 's/' . $path,      // modules/{name}/... , templates/{name}/...
            $this->repoDir . $type . '/' . $path,       // module/{name}/... (Legacy)
        ];
        
        foreach ($paths as $fullPath) {
            if (file_exists($fullPath)) {
                return file_get_contents($fullPath);
            }
        }
        
        return null;
    }
    
    public function putFileContents($type, $path, $content) {
        // Schreibe immer in die neue Struktur: modules/ oder templates/
        $fullPath = $this->repoDir . $type . 's/' . $path;
        $dir = dirname($fullPath);
        
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        return file_put_contents($fullPath, $content) !== false;
    }

    protected function _isExisting(&$item) {
        $type = $item->getType();
        
        // Prüfe beide Verzeichnis-Strukturen
        $possibleDirs = [
            $this->repoDir . $type . 's/' . $item->getName(),      // modules/{name}
            $this->repoDir . $type . '/' . $item->getName(),       // module/{name} (Legacy)
        ];
        
        foreach ($possibleDirs as $dir) {
            if (is_dir($dir)) {
                return true;
            }
        }
        
        return false;
    }


    public function getRepoInfo($short = false) {
        $info = htmlentities($this->repoDir);
        if ($short) {
            return $info;
        }
        
        return <<<HTML
<div class="themesync-repo-info">
    <p><strong>Repository-Typ:</strong> Lokales Dateisystem</p>
    <p><strong>Pfad:</strong> <code>$info</code></p>
    <p><small>Kompatibel mit: GitHub Installer, Theme-Addon</small></p>
</div>
HTML;
    }

    /*
    public function uploadModule(\rex_themesync_module &$module, $update = false) {
        // Todo fehlerbehandlung
        
        $dir = $this->repoDir . 'modules/' . $module->getName();
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            if (!is_dir($dir)) {
                return false;
            }
        }
        $a = $this->saveModuleInputOutput($module);
        
        return !!$a;
    }

    public function uploadTemplate(\rex_themesync_template &$template, $update = false) {
        // Todo fehlerbehandlung
        
        $dir = $this->repoDir . 'templates/' . $template->getName();
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            if (!is_dir($dir)) {
                return false;
            }
        }
        $a = $this->saveTemplateContent($template);
        
        return !!$a;
    }*/

    
    protected function makeItemDir(&$item) {
        $type = $item->getType();
        // Schreibe immer in die neue Struktur: modules/ oder templates/
        $dir = $this->repoDir . $type . 's/' . $item->getName();
        
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return false;
            }
        }
        return true;
    }
    
    
}