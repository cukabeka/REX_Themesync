<?php


/**
 * Repräsentiert ein Template über seinen Namen.
 * Alle anderen Dinge werden an das Repo delegiert.
 */
class rex_themesync_template extends rex_themesync_item_base {
    private $contentLoaded = false;
    private $content = null;
    
    public function __construct($name, &$repo) {
        parent::__construct('template', $name, $repo);
    }
    
    
    public function setContent($c) {
        $this->content = $c;
    }
    
    public function getContent() {
        $this->loadContent();
        return $this->content;
    }
    
    public function loadContent() {
        if ($this->contentLoaded) {
            return;
        }
        $this->contentLoaded = true;
        return $this->repo->loadTemplateContent($this);
    }
    
    
    
    public function isExisting() {
        return $this->repo->isTemplateExisting($this);
    }
          
    
    /**
     * Dateiinhalt zurückgeben
     */
    public function getFile($path) {
        if (method_exists($this->repo, 'getFileContents')) {
            return $this->repo->getFileContents('module', $this->name . '/' . $path);
        }
        return null;
    }
    
    /**
     * Dateiinhalt herunterladen
     */
    public function saveFile($path, $destination) {
        if (method_exists($this->repo, 'downloadFile')) {
            return $this->repo->downloadFile('module', $this->name . '/' . $path, $destination);
        }
        return false;
    }
    
    public function getReadme() {
        return $this->getFile('README.md');
    }
    
    
}


