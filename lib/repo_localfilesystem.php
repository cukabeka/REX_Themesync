<?php

/**
 * Ein lokaler Ordner (unterhalb des data-Ordners des Addons)
 * als Repository
 */
class rex_themesync_repo_localfilesystem extends rex_themesync_repo {
    #use rex_themesync_has_files;
    
    private $repoDir;
    
    public function __construct($repoConfig = []) {
        parent::__construct(self::REPO, $repoConfig);
        
        $this->repoDir = rex_path::addonData('themesync', $repoConfig['repo']);
        if (substr($this->repoDir, -1) !== '/') {
            $this->repoDir .= '/';
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
                $this->createModule($name);
            } else if ($type === 'template') {
                $this->createTemplate($name);
            }
        }
    }

    #public function downloadFile($type, $path, $destination) {
    #    return copy($this->repoDir .$type.'s/'. $path, $destination);
    #}

    public function getFileContents($type, $path) {
        return file_get_contents($this->repoDir . $type.'s/' . $path);
    }
    
    public function putFileContents($type, $path, $content) {
        return file_put_contents($this->repoDir . $type.'s/' . $path, $content) !== false;
    }

    protected function _isExisting(&$item) {
        $type = $item->getType();
        return is_dir($this->repoDir . $type.'s/' . $item->getName());
    }


    public function getRepoInfo($short = false) {
        return 'Lokaler Ordner: <code>'.htmlentities($this->repoDir).'</code>';
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
        echo '## '.$item->getType();
        $dir = $this->dir . $item->getType() .'s/' . $item->getName();
        
        if (!is_dir($dir)) {
            mkdir($dir);
            if (!is_dir($dir)) {
                return false;
            }
        }
        return true;
    }
    
    
}