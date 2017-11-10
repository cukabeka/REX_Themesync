<?php


class rex_themesync_sync_localdb extends rex_themesync_sync {
    private $table, $nameColumn;
    
    public function __construct($repoConfig = []) {
        parent::__construct($repoConfig);
        
        $this->table = rex::getTable('module');
        $this->nameColumn = 'name';
    }

    
    protected function _listModules() {
        try {
            $mList = [];
            
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM `' . $this->table . '`');
            for ($i = 0, $rows = $sql->getRows(); $i < $rows; ++$i, $sql->next()) {
                $name = $sql->getValue($this->nameColumn);
                $module = new rex_themesync_module($name, $this);
                $mList[$module->getKey()] = $module;
            }
            return $mList;
        } finally {
        }
        return null;
    }

    protected function _loadModule(\rex_themesync_module &$module) {
        
    }

}