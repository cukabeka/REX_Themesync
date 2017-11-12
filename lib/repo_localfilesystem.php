<?php


class rex_themesync_sync_localfilesystem extends rex_themesync_sync {
    private $repoDir;
    
    public function __construct($repoConfig = []) {
        parent::__construct($repoConfig);
        
        $this->repoDir = $repoConfig['repo'];
        if (substr($this->repoDir, -1) !== '/') {
            $this->repoDir .= '/';
        }
        
        echo 'Repo Dir: ', $this->repoDir, PHP_EOL;
        
    }

    
    protected function _listModules() {
        $dirList = glob($this->repoDir.'*', GLOB_NOSORT|GLOB_ONLYDIR|GLOB_MARK);
        if (!is_array($dirList)) {
            return null;
        }
        $mList = [];
        foreach ($dirList as $path) {
            $name = basename($path);
            $module = new rex_themesync_module($name, $this);
            $mList[$module->getKey()] = $module;
        }
        return $mList;
    }

    protected function _loadModule(rex_themesync_module &$module) {
        // TODO rex functions??
        
        $infn = $this->repoDir . $module->getName().'/'.'input.php';
        $outfn = $this->repoDir . $module->getName().'/'.'output.php';
        
        $input = file_get_contents($infn);
        $output = file_get_contents($outfn);
        
        $module->setInput($input);
        $module->setOutput($output);
        return true;
    }

}