<?php


class rex_themesync_module extends rex_themesync_item_base {
    private $inputOutputLoaded = false;
    private $input = null;
    private $output = null;
    
    /**
     * input/output muss erst geladen werden.
     * das passier idR über  loadInputOutput()
     * @param type $output
     */
    public function setOutput($output) {
        $this->output = $output;
    }
    
    public function setInput($input) {
        $this->input = $input;
    }
    
    public function getOutput() {
        $this->loadInputOutput();
        return $this->output;
    }
    
    public function getInput() {
        $this->loadInputOutput();
        return $this->input;
    }
    
    public function loadInputOutput() {
        if ($this->inputOutputLoaded) {
            return;
        }
        $this->inputOutputLoaded = true;
        return $this->repo->loadModuleInputOutput($this);
    }
    
    
    
    public function isExisting() {
        return $this->repo->isModuleExisting($this);
    }
          
    
    public function getFile($path) {
        if (method_exists($this->repo, 'getFileContents')) {
            return $this->repo->getFileContents('module', $this->name . '/' . $path);
        }
        return null;
    }
    
    public function saveFile($path, $destination) {
        if (method_exists($this->repo, 'downloadFile')) {
            return $this->repo->downloadFile('module', $this->name . '/' . $path, $destination);
        }
        return false;
    }
    
}


