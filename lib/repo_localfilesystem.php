<?php


class rex_themesync_repo_localfilesystem extends rex_themesync_source {
    use rex_themesync_has_files;
    
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

    public function downloadFile($type, $path, $destination) {
        return copy($this->repoDir .$type.'s/'. $path, $destination);
    }

    public function getFileContents($type, $path) {
        return file_get_contents($this->repoDir.$type.'s/' . $path);
    }

    protected function _isExisting(&$item) {
        $type = get_class($item);
        if ($type == 'rex_themesync_module') $type = 'module';
        if ($type == 'rex_themesync_template') $type = 'template';
        return is_dir($this->repoDir.'/'.$type.'s/' . $item->getName());
    }

    protected function loadModuleInputOutput(\rex_themesync_module &$module) {
        $infn = $module->getName().'/'.'input.php';
        $outfn = $module->getName().'/'.'output.php';
        $module->setInput($this->getFileContents($infn));
        $module->setOutput($this->getFileContents($outfn));
    }
    
    protected function loadTemplateContent(\rex_themesync_template &$template) {
        $fn = $template->getName().'/'.'template.php';
        $template->setContent($this->getFileContents('template', $fn));
    }

    public function getRepoInfo($short = false) {
        return 'Lokaler Ordner: <code>'.htmlentities($this->repoDir).'</code>';
    }

}