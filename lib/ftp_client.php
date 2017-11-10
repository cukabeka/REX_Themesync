<?php

// http://php.net/manual/de/book.ftp.php

class rex_themesync_ftp_client {
    private $dir;
    private $name;
    private $is_dir;
    private $size;
    
    public function __construct($dir, $name, $is_dir, $size) {
        $this->dir = $dir;
        $this->name = $name;
        $this->is_dir = $is_dir;
        $this->size = $size;
    }
    
    public function isDir() {
        return $this->is_dir;
    }
    
    public function getFilename() {
        return $this->name;
    }
    
    public function getDir() {
        return $this->dir;
    }
    
    public function getFullPath() {
        $path = $this->dir;
        if ($path !== '/') $path .= '/';
        
        return $path . $this->name;
    }
    
    public function getSize() {
        return $this->size;
    }
    
}

class FTPClient { 
    private $connection;

    public function __construct(string $host, int $port = 21, int $timeout = 90) { 
        //$this->connection = \ftp_connect($host, $port, $timeout); 
        $this->connection = \ftp_ssl_connect($host, $port, $timeout);
    } 
    
    public function __destruct() {
        \ftp_close($this->connection);
    }
    
    public function a() {
        echo 'A';
    }
    
    public function __call($func, $a) { 
        $funcname = 'ftp_'.$func;
        
        if (function_exists($funcname)) { 
            array_unshift($a, $this->connection); 
            return call_user_func_array($funcname, $a); 
        } else {
            throw new \Exception('method not found');
        } 
    }
    
    
    public function listing() {
        $dir = $this->pwd();
        $raw = $this->rawlist('.');
        //([0-9]+)  
        $pattern = '/^([d\\-]).*[\\s]+([0-9]+)[\\s]+[A-Za-z]+[\\s]+[0-9]+[\\s]+[0-9][0-9]:[0-9][0-9][\\s]+([^\\s]+)$/';
        $list = [];
        foreach ($raw as $r) {
            if (preg_match($pattern, $r, $m)) {
                $name = $m[3];
                if ($name === '.' || $name === '..') {
                    continue;
                }
                $is_dir = $m[1]==='d';
                $size = intval($m[2]);
                $list[] = new File($dir, $name, $is_dir, $size);
            }
        }
        return $list;
    }
} 