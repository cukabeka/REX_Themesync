<?php


class rex_themesync_repo_localfilesystem extends rex_themesync_repo {
    use rex_themesync_has_files;
    
    private $repoDir;
    
    public function __construct($repoConfig = []) {
        parent::__construct(self::REPO, $repoConfig);
        
        $this->repoDir = rex_path::addonData('themesync', $repoConfig['repo']);
        if (substr($this->repoDir, -1) !== '/') {
            $this->repoDir .= '/';
        }
        
    }

    
    protected function _listModules() {
        $dirList = glob($this->repoDir.'*', GLOB_NOSORT|GLOB_ONLYDIR|GLOB_MARK);
        if (!is_array($dirList)) {
            return;
        }
        foreach ($dirList as $path) {
            $name = basename($path);
            $this->createModule($name);
        }
    }

    public function downloadFile($path, $destination) {
        return copy($this->repoDir . $path, $destination);
    }

    public function getFileContents($path) {
        return file_get_contents($this->repoDir . $path);
    }

    protected function _isExisting(\rex_themesync_module &$module) {
        return is_dir($this->repoDir . $module->getName());
    }

    protected function _loadInputOutput(\rex_themesync_module &$module) {
        $infn = $module->getName().'/'.'input.php';
        $outfn = $module->getName().'/'.'output.php';
        $module->setInput($this->getFileContents($infn));
        $module->setOutput($this->getFileContents($outfn));
    }


}