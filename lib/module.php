<?php


class rex_themesync_module {
    private $name, $key;
    private $sync = null;
    private $input = null;
    private $output = null;
    
    public function __construct($name, &$sync) {
        $this->name = $name;
        $this->sync = $sync;
        
        $this->key = preg_replace('`[^a-z0-9\\.]+`', '_', strtolower($this->name));
        //$this->key = preg_replace('`[^A-Za-z0-9\\-]`', '_', $this->key);
        #$this->key = preg_replace('`_-`', '_', $this->key);
        #$this->key = preg_replace('`-_`', '_', $this->key);
        $this->key = preg_replace('`_\\.`', '_', $this->key);
        $this->key = preg_replace('`\\._`', '_', $this->key);
        $this->key = preg_replace('`_$`', '', $this->key);
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getKey() {
        return $this->key;
    }
    
    public function setOutput($output) {
        $this->output = $output;
    }
    
    public function setInput($input) {
        $this->input = $input;
    }
    
    public function getOutput() {
        return $this->output;
    }
    
    public function getInput() {
        return $this->input;
    }
    
    public function load() {
        return $this->sync->loadModule($this);
    }
           
    
    public function install() {
        if (!$this->load()) {
            return false;
        }
        
        // TODO existiert??
        
        $input = $this->getInput();
        $output = $this->getOutput();
        
        /*
        name	varchar(255)	 
        output	mediumtext	 
        input	mediumtext	 
        createuser	varchar(255)	 
        updateuser	varchar(255)	 
        createdate	datetime	 
        updatedate	datetime	 
        attributes	text NULL	 
        revision	int(10) unsigned
        */

        $mi = rex_sql::factory();
        
        $mi->debugsql = 0;
        $mi->setTable(rex::getTable('module'));
        $mi->setValue('input', $input);
        $mi->setValue('output', $output);
        $mi->setValue('name', $this->getName());
        
        $mi->insert();
        
        $modul_id = (int) $mi->getLastId();
         
        return true;
    }
}


