<?php

abstract class rex_themesync_repo extends rex_themesync_source {
    #use rex_themesync_has_files;
    
    
    abstract public function getFileContents($type, $path);
    abstract public function putFileContents($type, $path, $content);
    abstract protected function makeItemDir(&$item);
    
    #abstract public function uploadTemplate(rex_themesync_template &$template, $update = false);
    #abstract public function uploadModule(rex_themesync_module &$module, $update = false);
    
    
    public function uploadModule(\rex_themesync_module &$module, $update = false) {
        // Todo fehlerbehandlung
        
        if ($this->isModuleExisting($module) && !$update) {
            return false;
        }
        
        if (!$this->makeItemDir($module)) {
            return false;
        }
        
        // Save input/output files
        $a = $this->saveModuleInputOutput($module);
        
        // Save or update config.yml
        $this->saveItemConfig($module);
        
        return !!$a;
    }

    public function uploadTemplate(\rex_themesync_template &$template, $update = false) {
        // Todo fehlerbehandlung
        
        if ($this->isTemplateExisting($template) && !$update) {
            return false;
        }
        
        if (!$this->makeItemDir($template)) {
            return false;
        }
        
        // Save template content
        $a = $this->saveTemplateContent($template);
        
        // Save or update config.yml
        $this->saveItemConfig($template);
        
        return !!$a;
    }
    
    /**
     * Save or update config.yml for an item
     * Creates config if it doesn't exist, updates git info if it does
     * 
     * @param rex_themesync_item_base $item
     * @return bool Success
     */
    protected function saveItemConfig($item) {
        // Check if repo supports config path
        if (!method_exists($this, 'getItemConfigPath')) {
            return false;
        }
        
        $configPath = $this->getItemConfigPath($item);
        $itemDir = dirname($configPath);
        
        // Load existing config or create new one
        $config = $item->getConfig();
        
        if (!$config) {
            // Create new config
            if ($item->getType() === 'module') {
                $config = rex_themesync_config_yml_handler::createModuleConfig(
                    $item->getName(),
                    $item->getNumericKey(),
                    $itemDir
                );
            } else {
                $config = rex_themesync_config_yml_handler::createTemplateConfig(
                    $item->getName(),
                    $item->getNumericKey(),
                    $itemDir
                );
            }
        } else {
            // Update git info in existing config
            $config = rex_themesync_config_yml_handler::updateGitInfo($config, $itemDir);
        }
        
        return $item->saveConfig($config);
    }
    
    
    
    
    public function loadModuleInputOutput(\rex_themesync_module &$module) {
        $infn = $module->getName().'/'.'input.php';
        $outfn = $module->getName().'/'.'output.php';
        $module->setInput($this->getFileContents('module', $infn));
        $module->setOutput($this->getFileContents('module', $outfn));
    }
    
    public function saveModuleInputOutput(\rex_themesync_module &$module) {
        $infn = $module->getName().'/'.'input.php';
        $outfn = $module->getName().'/'.'output.php';
        $a = $this->putFileContents('module', $infn, $module->getInput());
        $b = $this->putFileContents('module', $outfn, $module->getOutput());
        return $a && $b;
    }
    
    public function loadTemplateContent(\rex_themesync_template &$template) {
        $fn = $template->getName().'/'.'template.php';
        $template->setContent($this->getFileContents('template', $fn));
    }
    
    public function saveTemplateContent(\rex_themesync_template &$template) {
        $fn = $template->getName().'/'.'template.php';
        return $this->putFileContents('template', $fn, $template->getContent());
    }
    
    #abstract public function saveModuleInputOutput(rex_themesync_module &$module);
    #abstract public function saveTemplateContent(rex_themesync_template &$template);

}