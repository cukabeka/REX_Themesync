<?php

$file = rex_file::get(rex_path::addon('themesync','README.md'));
$Parsedown = new Parsedown();
$content =  '<div id="themesync">'.$Parsedown->text($file);


$fragment = new rex_fragment();
$fragment->setVar('title', 'Info');
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
