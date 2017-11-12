<?php


class rex_themesync_local extends rex_themesync_repo {
    private $table, $nameColumn;
    
    public function __construct($repoConfig = []) {
        parent::__construct(self::LOCAL, $repoConfig);
        
        $this->table = rex::getTable('module');
        $this->nameColumn = 'name';
    }

    
    protected function _listModules() {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM `' . $this->table . '`');
            for ($i = 0, $rows = $sql->getRows(); $i < $rows; ++$i, $sql->next()) {
                $name = $sql->getValue($this->nameColumn);
                $this->createModule($name);
            }
        } finally {
        }
    }

    protected function &_getDBObject(\rex_themesync_module$module) {
        if (!$module->hasRepoCache('local_sql')) {
            try {
                $sql = rex_sql::factory();
                $sql->setQuery('SELECT * FROM `' . $this->table . '` WHERE name=?', [$module->getName()]);
                $sql->getRow();
            } finally {
                //$sql->
                #echo 'null';
                #$sql = null;
            }
            $module->setRepoCache('local_sql', $sql);
            return $sql;
        }
        return $module->getRepoCache('local_sql');
    }
    
    protected function _loadInputOutput(\rex_themesync_module &$module) {
        $sql = $this->_getDBObject($module);
        if (!$sql || $sql->getRows() === 0) {
            return;
        }
        $module->setInput($sql->getValue('input'));
        $module->setOutput($sql->getValue('output'));
    }
    

    public function install(rex_themesync_module &$module, bool $update = false) {
        
        
        
        
        $existing = $this->_getDBObject($module);
        if (!$existing || $existing->getRows()===0) $existing = null;
        
        if ($existing && !$update) {
            return false;
        }
        
        $input = $module->getInput();
        $output = $module->getOutput();
        
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
        
        $mi->setDebug(true);
        
        $mi->setTable($this->table);
        
        if ($existing) {
            $mi->setWhere(['id' => $existing->getValue('id')]);
        }
        
        $mi->setValue('input', $input);
        $mi->setValue('output', $output);
        $mi->setValue('name', $module->getName());
        
        if ($existing) {
            $mi->update();
        } else {
            $mi->insert();
            $modul_id = (int) $mi->getLastId();
        }
        
         
        return true;
    }

    protected function _isExisting(\rex_themesync_module &$module) {
        $sql = $this->_getDBObject($module);
        if (!$sql) {
            return false;
        }
        return $sql->getRows() > 0;
    }

}