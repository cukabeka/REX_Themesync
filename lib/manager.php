<?php


abstract class rex_themesync_manager {
    private $repo;
    private $local;
    
    private $type;
    
    private $config = null;
    
    protected $addon;
    
    public function __construct($type, &$addon) {
        $this->repo  = $repo;
        $this->local = $local;
        $this->type = $type;
        $this->addon = $addon;
    }
    
    protected function load_config() {
        if (is_null($this->config)) {
            $file = rex_path::addonData('themesync', 'repo.yml');
            $this->config = rex_file::getConfig($file);
        }
    }
    
    public function &getLocal() {
        if (is_null($this->local)) {
            // TODO: type!!
            $this->local = new rex_themesync_local();
        }
        return $this->local;
    }
    
    public function &getRepo() {
        if (is_null($this->repo)) {
            $this->load_config();
            
            $classname = $this->config['classname'];
            if (!$classname || !class_exists($classname)) {
                $this->repo = null;
                return $this->repo;
            }
            unset($this->config['classname']); // TODO geschickter machen falls da noch mehr config infos sind, die hier nicht relevalt sind
            try {
                $this->repo = new $classname($this->config);
            } catch (\Exception $e) {
                $this->repo = null;
            }
        }
        return $this->repo;
    }
    
    public function init() {
        $repo  = $this->getRepo();
        if (!$repo) {
            $content = rex_view::error('Repository konnte nicht geöffnet werden. Konfiguration data/addons/themesync/repo.yml korrekt angelegt?');
            $fragment = new rex_fragment();
            $fragment->setVar('body', $content, false);
            echo $fragment->parse('core/page/section.php');
            die();
        }
    }
    
    
    abstract public function action();
    
    abstract public function render();
    
    
    
}