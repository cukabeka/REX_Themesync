<?php

/**
 * FTP-Server als Repository
 */
class rex_themesync_repo_ftp extends rex_themesync_repo {
    #use rex_themesync_has_files;
    
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
        
        if (empty($this->host)) {
            throw new \Exception('Missing FTP-Server Config');
        }
        
        // todo: port / anonymous user...
        $this->ftpClient = new rex_themesync_ftp_client($this->host);
        $this->ftpClient->login($this->user, $this->pass);
        
        $this->ftpClient->chdir($this->dir);
        $this->ftpClient->pasv(true);

    }
    
    
    protected function _list($type) {
        $dir = $this->dir . $type. 's/';
        
        if (!$this->ftpClient->dir_exists($dir)) {
            return;
        }
        
        $this->ftpClient->chdir($dir);
        
        $dirList = $this->ftpClient->listing();
        if (!is_array($dirList)) {
            return;
        }
        
        /* @var $file rex_themesync_ftp_file */
        if ($type === 'module') {
            foreach ($dirList as $file) {
                $name = $file->getFilename();
                $this->createModule($name);
            }
        } else if ($type === 'template') {
            foreach ($dirList as $file) {
                $name = $file->getFilename();
                $this->createTemplate($name);
            }
        }
    }


    #public function downloadFile($type, $path, $destination) {
    #    throw new Exception('ftp repo downloadFile nyi');
    #}

    public function getFileContents($type, $path) {
        $fn = $this->dir . $type . 's/' . $path;
        return $this->ftpClient->get_contents($fn, FTP_BINARY);
    }
    
    public function putFileContents($type, $path, $content) {
        $fn = $this->dir . $type . 's/' . $path;
        return $this->ftpClient->put_contents($fn, $content, FTP_BINARY);
    }

    protected function _isExisting(&$item) {
        $type = $item->getType();
        return $this->ftpClient->dir_exists($this->dir . $type.'s/'.$item->getName());
    }

    /*public function loadModuleInputOutput(\rex_themesync_module &$module) {
        $infn = $module->getName().'/'.'input.php';
        $outfn = $module->getName().'/'.'output.php';
        $module->setInput($this->getFileContents('module', $infn));
        $module->setOutput($this->getFileContents('module', $outfn));
    }
    
    public function loadTemplateContent(\rex_themesync_template &$template) {
        $fn = $template->getName().'/'.'template.php';
        $template->setContent($this->getFileContents('template', $fn));
    }

    public function saveModuleInputOutput(\rex_themesync_module &$module) {
        
    }

    public function saveTemplateContent(\rex_themesync_template &$template) {
        
    }*/
    
    public function getRepoInfo($short = false) {
        return 'FTP: <code>'. htmlentities($this->user).' @ '.htmlentities($this->host) . ' '. htmlentities($this->dir).'</code>';
    }

    /*
    public function uploadModule(\rex_themesync_module &$module, $update = false) {
        // Todo fehlerbehandlung
        
        $dir = $this->dir . 'modules/' . $module->getName();
        
        if (!$this->ftpClient->dir_exists($dir)) {
            $this->ftpClient->mkdir($dir);
            if (!$this->ftpClient->dir_exists($dir)) {
                return false;
            }
        } else {
            if (!$update) {
                return false;
            }
        }
        
        $this->ftpClient->chdir($dir)
        
        $a = $this->saveModuleInputOutput($module);
        
        return !!$a;
    }

    public function uploadTemplate(\rex_themesync_template &$template, $update = false) {
        // Todo fehlerbehandlung
        
        $dir = $this->dir . 'templates/' . $template->getName();
        
        if (!$this->ftpClient->chdir($dir)) {
            $this->ftpClient->mkdir($dir);
            if (!$this->ftpClient->chdir($dir)) {
                return false;
            }
        } else {
            if (!$update) {
                return false;
            }
        }
        
        $a = $this->saveTemplateContent($template);
        
        return !!$a;
    }*/

    protected function makeItemDir(&$item) {
        $dir = $this->dir . $item->getType() .'s/' . $item->getName();
        
        if (!$this->ftpClient->dir_exists($dir)) {
            $this->ftpClient->mkdir($dir);
            if (!$this->ftpClient->dir_exists($dir)) {
                return false;
            }
        }
        return true;
    }
    


}