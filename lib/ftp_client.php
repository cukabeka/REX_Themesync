<?php
/**
 * Simpler Wrapper für die PHP ftp_* functionen
 */

// http://php.net/manual/de/book.ftp.php
/**
 * Repräsentiert eine FTP-Datei
 */
class rex_themesync_ftp_file {
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

/**
 * Der FTP-Client.
 */
class rex_themesync_ftp_client { 
    private $connection;
    
    const DIRS = 1;
    const FILES = 2;

    /**
     * 
     * @param string $host
     * @param int $port
     * @param int $timeout
     */
    public function __construct($host, $port = 21, $timeout = 90) { 
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
    
    public function dir_exists($d) {
        $pwd = $this->pwd();
        if (@$this->chdir($d)) {
            $this->chdir($pwd);
            return true;
        }
        return false;
    }
    
    
    public function listing($flags = self::DIRS | self::FILES) {
        $dir = $this->pwd();
        $raw = $this->rawlist('.');
        //([0-9]+)  
        $pattern = '/^([d\\-]).*[\\s]+([0-9]+)[\\s]+[A-Za-z]+[\\s]+[0-9]+[\\s]+[0-9][0-9]:[0-9][0-9][\\s]+([^\\s]+)$/';
        $list = [];
        foreach ($raw as $r) {
            
            
            $chunks = preg_split("/\s+/", $r);
            list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks; 
            $item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file'; 
            array_splice($chunks, 0, 8);
            
            $name = implode(" ", $chunks);    
            
            if ($name === '.' || $name === '..') {
                continue;
            }

            $is_dir = $item['type'] === 'directory';
            $size = intval($item['size']);
            $list[] = new rex_themesync_ftp_file($dir, $name, $is_dir, $size);
            
        }
        return $list;
    }
    
    function get_contents($remote_file, $mode) {
        ob_implicit_flush(false);
        ob_start();
        $result = $this->get("php://output", $remote_file, $mode);
        $data = ob_get_contents();
        ob_end_clean();
        
        if ($result) {
            return $data;
        }
        return false;
    }
    
    function put_contents($remote_file, $contents, $mode, $startpos = 0) {
        $tmp = tmpfile();
        fwrite($tmp, $contents);
        rewind($tmp);
        $result = $this->fput($remote_file, $tmp, $mode, $startpos);
        fclose($tmp);
        return $result;
    }
    
    
    
} 