<?php

/**
 * Lokale Datenbank als "Quelle" bzw "Ziel" für Module / Templates
 */
class rex_themesync_local extends rex_themesync_source {
    
    
    public function __construct($repoConfig = []) {
        parent::__construct(self::LOCAL, $repoConfig);
        
        #$this->table = rex::getTable('module');
        #$this->nameColumn = 'name';
    }

    
    protected function _list($type) {
        $table = rex::getTable($type);
        
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM `' . $table . '`');
            for ($i = 0, $rows = $sql->getRows(); $i < $rows; ++$i, $sql->next()) {
                $name = $sql->getValue('name');
                if ($type === 'module') {
                    $this->createModule($name);
                } else if ($type === 'template') {
                    $this->createTemplate($name);
                }
            }
        } finally {
        }
    }

    /**
     * Den entsprechenden Datensatz laden.
     * 
     * @param type $item
     * @return type
     */
    protected function &_getDBObject(&$item) {
        if (!$item->hasRepoCache('local_sql')) {
            $type = get_class($item);
            if ($type == 'rex_themesync_module') $type = 'module';
            if ($type == 'rex_themesync_template') $type = 'template';
            $table = rex::getTable($type);
            try {
                $sql = rex_sql::factory();
                $sql->setQuery('SELECT * FROM `' . $table . '` WHERE name=?', [$item->getName()]);
                if ($sql->getRows() === 0) {
                    return;
                }
                $sql->getRow();
            } finally {
                //$sql->
                #echo 'null';
                #$sql = null;
            }
            $item->setRepoCache('local_sql', $sql);
            return $sql;
        }
        return $item->getRepoCache('local_sql');
    }
    
    public function loadModuleInputOutput(\rex_themesync_module &$module) {
        $sql = $this->_getDBObject($module);
        if (!$sql || $sql->getRows() === 0) {
            return;
        }
        $module->setInput($sql->getValue('input'));
        $module->setOutput($sql->getValue('output'));
    }
    
    public function loadTemplateContent(\rex_themesync_template &$template) {
        $sql = $this->_getDBObject($template);
        if (!$sql || $sql->getRows() === 0) {
            return;
        }
        $template->setContent($sql->getValue('content'));
    }
    
    
    /**
     * 
     * @param rex_themesync_template $template
     * @param bool $update
     * @return boolean
     */
    public function installTemplate(rex_themesync_template &$template, $update = false) {
        $existing = $this->_getDBObject($template);
        if (!$existing || $existing->getRows()===0) $existing = null;
        
        if ($existing && !$update) {
            return false;
        }
        
        $content = $template->getContent();
        
        /*
        name	varchar(255) NULL	 
        content	mediumtext NULL	 
        active	tinyint(1) NULL	 
        createuser	varchar(255)	 
        updateuser	varchar(255)	 
        createdate	datetime	 
        updatedate	datetime	 
        attributes	text NULL	 
        revision	int(11)
         */
        
        
        $mi = rex_sql::factory();
        
        //$mi->setDebug(true);
        
        $mi->setTable(rex::getTable('template'));
        
        if ($existing) {
            $mi->setWhere(['id' => $existing->getValue('id')]);
        }
        
        $mi->setValue('content', $content);
        $mi->setValue('name', $template->getName());
        
        if ($existing) {
            $mi->update();
        } else {
            $mi->insert();
            $template_id = (int) $mi->getLastId();
        }
        
        return true;
    }

    
    /**
     * 
     * @param rex_themesync_module $module
     * @param bool $update
     * @return boolean
     */
    public function installModule(rex_themesync_module &$module, $update = false) {
        
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
        
        //$mi->setDebug(true);
        
        $mi->setTable(rex::getTable('module'));
        
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

    protected function _isExisting(&$item) {
        $sql = $this->_getDBObject($item);
        if (!$sql) {
            return false;
        }
        return $sql->getRows() > 0;
    }

    public function getRepoInfo($short = false) {
        return 'Lokale Datenbank';
    }

}
