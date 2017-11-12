<?php


class rex_themesync_repo_ftp extends rex_themesync_repo {
    use rex_themesync_has_files;
    
    private $host, $user, $pass;
    private $dir;
    
    private $ftpClient;
    
    public function __construct($repoConfig = array()) {
        parent::__construct(self::REPO, $repoConfig);
        
        $this->host = $this->repoConfig['host'];
        //$this->port = $this->repoConfig['port'];
        $this->user = $this->repoConfig['user'];
        $this->pass = $this->repoConfig['pass'];
        $this->dir  = $this->repoConfig['dir'];
        
        $this->ftpClient = new rex_themesync_ftp_client($this->host);
        $this->ftpClient->login($this->user, $this->pass);
        
        $this->ftpClient->chdir($this->dir);
        $this->ftpClient->pasv(true);

                
    }
    
    
    protected function _listModules() {
        if (!$this->ftpClient->chdir($this->dir . 'modules/')) {
            return;
        }
        
        $dirList = $this->ftpClient->listing();
        if (!is_array($dirList)) {
            return;
        }
        /* @var $file rex_themesync_ftp_file */
        foreach ($dirList as $file) {
            $name = $file->getFilename();
            $this->createModule($name);
        }
    }


    public function downloadFile($path, $destination) {
        throw new Exception();
    }

    public function getFileContents($path) {
        $fn = $this->dir . 'modules/' . $path;
        
        ob_implicit_flush(false);
        ob_start();
        $result = $this->ftpClient->get("php://output", $fn, FTP_BINARY);
        $data = ob_get_contents();
        ob_end_clean();
        
        if ($result) {
            return $data;
        }
        return true;
    }

    protected function _isExisting(\rex_themesync_module &$module) {
        return $this->ftpClient->chdir($this->dir . 'modules/'.$module->getName());
    }

    protected function _loadInputOutput(\rex_themesync_module &$module) {
        $infn = $module->getName().'/'.'input.php';
        $outfn = $module->getName().'/'.'output.php';
        $module->setInput($this->getFileContents($infn));
        $module->setOutput($this->getFileContents($outfn));
    }

}