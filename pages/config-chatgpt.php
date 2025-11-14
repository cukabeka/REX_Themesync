<?php

// Prüfe, ob das Formular abgeschickt wurde
if (rex_post('formsubmit', 'string') == '1') {
    // Hole die Eingabewerte aus dem Formular
    $media = rex_post('media', 'string');
    $title = rex_post('title', 'string');
    $url = rex_post('url', 'string');

    // Speichere die Eingabewerte als Addon-Einstellungen
    $this->setConfig('media', $media);
    $this->setConfig('title', $title);
    $this->setConfig('url', $url);

    // Gebe eine Meldung aus, um anzuzeigen, dass die Einstellungen gespeichert wurden
    echo rex_view::success('Die Einstellungen wurden gespeichert.');
}

// Erstelle das Formular
$form = rex_form::factory(rex::getTablePrefix().'addon', '', 'id=1');

// Erstelle das Medien-Eingabefeld
$media_input = $form->addMediaField('media');
$media_input->setLabel('Medien');
$media_input->setValue($this->getConfig('media'));

// Erstelle das Titel-Eingabefeld
$title_input = $form->addTextField('title');
$title_input->setLabel('Titel');
$title_input->setValue($this->getConfig('title'));

// Erstelle das URL-Eingabefeld
$url_input = $form->addUrlField('url');
$url_input->setLabel('URL');
$url_input->setValue($this->getConfig('url'));

// Erstelle das Absenden-Button
$form->addSubmitField('absenden', 'Einstellungen speichern');

// Gebe das Formular aus
echo $form->get();

?>