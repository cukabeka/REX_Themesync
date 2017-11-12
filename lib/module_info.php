<?php

/**
 * Für AJAX Requests um Details über das Modul zu laden
 * z.B. den Code oder ein Diff wenn die repo und die lokale version sich unterscheiden.
 */
class rex_api_module_info extends rex_api_function {
    function execute() {
        #$result = new \stdClass();
        #$result->isOk = true;
        // rex_string::highlight($modul['input'])
        #echo json_encode($result);
        
        /* @var $repo rex_themesync_repo */
        /* @var $local rex_themesync_repo */
        $repo  = rex_themesync_repo::get_repo();
        $local = rex_themesync_repo::get_local();
        
        
        $name = urldecode(rex_get('name'));
        
        $repo_module = new rex_themesync_module($name, $repo);
        $local_module = new rex_themesync_module($name, $local);
        
        
        
        $inRepo  = $repo_module->isExisting();
        $inLocal = $local_module->isExisting();
        
        if ($inRepo) {
            $repo_module->loadInputOutput();
        }
        if ($inLocal) {
            $local_module->loadInputOutput();
        }
        
        if ($inRepo && $inLocal) {
            $r_input  = $repo_module->getInput();
            $r_output = $repo_module->getOutput();
            
            $l_input  = $local_module->getInput();
            $l_output = $local_module->getOutput();
            
            
            print_r(mb_detect_encoding($r_input));
            print_r(mb_detect_encoding($l_input));
            
            
            $r_input  = explode("\n", $r_input);
            $r_output = explode("\n", $r_output);
            
            $l_input  = explode("\n", $l_input);
            $l_output = explode("\n", $l_output);
            
            
            // https://github.com/chrisboulton/php-diff/blob/master/example/example.php
            require_once dirname(__FILE__).'/diff/lib/Diff.php';
            require_once dirname(__FILE__).'/diff/lib/Diff/Renderer/Html/SideBySide.php';
            
            
            // Options for generating the diff
            $options = array(
                'ignoreWhitespace' => true,
                //'ignoreCase' => true,
            );
            $lang = [
                'first'  => 'Repo',
                'second' => 'Local',
            ];
            
            echo '<strong>Input</strong>';
            // Initialize the diff class
            $diff = new Diff($r_input, $l_input, $options);
            if (count($diff->getGroupedOpcodes())) {
                $renderer = new Diff_Renderer_Html_SideBySide($lang);
                echo $diff->Render($renderer);
            } else {
                echo rex_string::highlight(implode("\n", $l_input));
            }
            
            echo '<strong>Output</strong>';
            // Initialize the diff class
            $diff = new Diff($r_output, $l_output, $options);
            if (count($diff->getGroupedOpcodes())) {
                $renderer = new Diff_Renderer_Html_SideBySide($lang);
                echo $diff->Render($renderer);
            } else {
                echo rex_string::highlight(implode("\n", $l_output));
            }
            
            exit();
        } else if ($inRepo) {
            $input = $repo_module->getInput();
            $output = $repo_module->getOutput();
        } else if ($inLocal) {
            $input = $local_module->getInput();
            $output = $local_module->getOutput();
        }
        
        echo '<strong>Input</strong>';
        echo rex_string::highlight($input);
        echo '<strong>Output</strong>';
        echo rex_string::highlight($output);
        
        
        exit();
    }
}