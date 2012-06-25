<?php

/**
 * manufakturConfig
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/manufakturConfig
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php
if ('á' != "\xc3\xa1") {
  // important: language files must be saved as UTF-8 (without BOM)
  trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

$LANG = array(
    '- no XML action -'
      => '- keine XML Aktion -',
    'Abort'
      => 'Abbruch',
    'A not described error occured during file upload, please try again!'
      => 'Ein nicht näher beschriebener Fehler ist während der Datei Übermittlung aufgetreten, bitte versuchen Sie es erneut!',
    'At minimun the fields NAME, TYPE and VALUE must set for configuration records of type HIDDEN.'
      => 'Für Konfigurations Datensätze vom Typ HIDDEN müssen wenigstens die Felder NAME, TYPE und VALUE gesetzt sein.',
    'Edit the settings for {{ module_name }}.'
      => 'Bearbeiten Sie die Einstellungen für {{ module_name }}.',
    'Error executing the template <b>{{ template }}</b>: {{ error }}'
      => 'Bei der Ausführung des Templates <b>{{ template }}</b> ist ein Fehler aufgetreten: {{ error }}',
    'Error message'
      => 'Fehlermeldung',
    'Error writing the XML file {{ file }}.'
      => 'Fehler beim Schreiben der XML Datei {{ file }}.',
    'Export the settings for all modules as XML file (complete)'
      => 'Exportiere alle Einstellungen für alle Add-ons als XML Datei (Vollständig)',
    'Export the settings for this module as XML file (single)'
      => 'Exportiere die Einstellungen für dieses Add-on als XML Datei (Einzeln)',
    'Got no IDs for processing the settings!'
      => 'Keine IDs erhalten um die Einstellungen zu ermitteln!',
    'If this box is checked, only settings for the specified addon will be imported. Otherwise all settings, also for other addons, within the XML file will be imported.'
      => 'Wenn dieser Schalter gesetzt ist werden ausschließlich Einstellungen für das angegebene Add-on importiert. Andernfalls werden <i>alle</i> Einstellungen, auch die für andere Add-ons, die sich in der XML Datei befinden importiert.',
    'If you check this, all settings will be set to the value specified within the XML file. Otherwise the import will use the general data handling.'
      => 'Wenn Sie diesen Schalter setzen, werden alle Einstellungen auf die in der XML Datei angegebenen Werte gesetzt. Andernfalls behält der Import bereits gesetzte Werte bei.',
    'Import / Export'
      => 'Import / Export',
    'import <i>only</i> settings for <b>{{ module_name }}</b>'
      => '<i>ausschließlich</i> Einstellungen für <b>{{ module_name }}</b> importieren',
    'Import settings from XML file'
      => 'Importiere Einstellungen von einer XML Datei',
    'Import XML file'
      => 'XML Datei importieren',
    '<p>Missing attributes for the <b>dialog</b> tag, needed are <i>set</i> and <i>page</i>, skipped entry!</p>'
      => '<p>Es fehlen Attribute für den <b>dialog</b> Bezeichner, benötigt werden <i>set</i> und <i>page</i>, der Eintrag wurde übersprungen!</p>',
    '<p>Missing attributes for the setting <b>{{ name }}</b>, needed are <i>name, usage</i> and <i>update</i>, skipped entry!</p>'
      => '<p>Es fehlen Attribute für die Einstellung <b>{{ name }}</b>, benötigt werden <i>name, usage</i> und <i>update</i>, der Eintrag wurde übersprungen!</p>',
    '<p>Missing the attribute <b>type</b> for the value of <b>{{ name }}</b>, skipped entry!</p>'
      => '<p>Es fehlt das Attribut <b>type</b> für den Wert von <b>{{ name }}</b>, der Eintrag wurde übersprungen!</p>',
    'Missing the parameter REQUEST_ITEMS.'
      => 'Der Parameter REQUEST_ITEMS fehlt!',
    '<p>Missing the tag <b>dialog</b> for <b>{{ name }}</b>, skipped entry!</p>'
      => '<p>Es fehlt die Bezeichnung <b>dialog</b> für <b>{{ name }}</b>, der Eintrag wurde übersprungen!</p>',
    'Missing the <b>value</b> for the configuration record with the <b>ID {{ id }}</b>.'
      => 'Der <b>Wert</b> für den Konfigurations Datensatz mit der <b>ID {{ id }}</b> fehlt.',
    'No'
      => 'Nein',
    'OK'
      => 'OK',
    'Please help to improve open source software and report this problem to the <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> group.'
      => 'Bitte helfen Sie dabei diese Open Source Software zu verbessern und melden Sie das aufgetretene Problem an die <a href="{{ url }}" target="_blank">phpManufaktur Addons Support</a> Gruppe.',
    '<i>reset</i> all settings to <b>default value</b>'
      => 'alle Einstellungen auf den <b>ursprünglichen Wert</b> <i>zurücksetzen</i>',
    '<p>Saved <b>{{ count }}</b> configuration records as XML file at <b>{{ file }}</b></p>'
      => '<p>Es wurden <b>{{ count }}</b> Konfigurations Einstellungen als XML Datei unter <b>{{ file }}</b> gesichert.</p>',
    'Select the XML file you wish to import'
      => 'Wählen Sie die XML Datei aus, die importiert werden soll.',
    '<p>Set value of <b>{{ field_name }}</b> to <i>{{ field_value }}</i>.</p>'
      => '<p>Setze den Wert von <b>{{ field_name }}</b> auf <i>{{ field_value }}</i>.</p>',
    'Settings'
      => 'Einstellungen',
    'The configuration key <b>{{ name }}</b> for the module directory <b>{{ directory }}</b> does not exists!'
      => 'Der Konfigurations Schlüssel <b>{{ name }}</b> für das Modul Verzeichnis <b>{{ directory }}</b> existiert nicht!',
    'The configuration record is not valid, a <b>name</b> and the <b>module name</b> or the <b>module directory</b> is needed for identify'
      => 'Der Konfigurations Datensatz ist nicht gültig, ein <b>Name</b> und der <b>Modul Name</b> oder das <b>Modul Verzeichnis</b> werden für die Identifizierung benötigt.',
    '<p>The configuration record <b>{{ name }}</b> was successfull deleted.</p>'
      => '<p>Der Konfigurations Datensatz <b>{{ name }}</b> wurde erfolgreich gelöscht.</p>',
    '<p>The configuration record <b>{{ name }}</b> was successfull inserted.</p>'
      => '<p>Der Konfigurations Datensatz <b>{{ name }}</b> wurde erfolgreich eingefügt.</p>',
    '<p>The configuration record <b>{{ name }}</b> was successfull updated.</p>'
      => '<p>Der Konfigurations Datensatz <b>{{ name }}</b> wurde erfolgreich aktualisiert.</p>',
    'The configuration record with the <b>ID {{ id }}</b> does not exist!'
      => 'Der Konfigurations Datensatz mit der <b>ID {{ id }}</b> existiert nicht!',
    '<p>The email address <b>{{ email }}</b> is not valid!</p>'
      => '<p>Die E-Mail Adresse <b>{{ email }}</b> ist nicht gültig!</p>',
    'The field <b>{{ field }}</b> must be defined!'
      => 'Das Feld <b>{{ field }}</b> muss definiert sein!',
    'The file {{ file }} could not moved to the temporary directory.'
      => 'Die Datei {{ file }} konnte nicht in das temporäre Verzeichnis verschoben werden.',
    'The file <b>{{ file }}</b> is no valid manufakturConfig file, missing one or more <b>module attribute</b> in the XML element <b>module</b>, needed are <i>name, directory, group and date</i>.'
      => 'Die Datei <b>{{ file }}</b> ist keine gültige manufakturConfig Datei, es fehlen ein oder mehrere <b>Modul Attribute</b> in dem XML Element <b>module</b>, benötigt werden <i>name, directory, group</i> und <i>date</i>.',
    'The file <b>{{ file }}</b> is no valid manufakturConfig file, missing the <b>version attribute</b> in the XML element <b>xmcfg</b>.'
      => 'Die Datei <b>{{ file }}</b> ist keine gültige manufakturConfig Datei, es das <b>Versions Attribut</b> in dem XML Element <b>xmcfg</b>.',
    'The file <b>{{ file }}</b> is no valid manufakturConfig file, missing the XML element <b>xmcfg</b>.'
      => 'Die Datei <b>{{ file }}</b> ist keine gültige manufakturConfig Datei, es fehlt das XML Element <b>xmcfg</b>.',
    'The file <b>{{ file }}</b> was uploaded partial, please try again!'
      => 'Die Datei <b>{{ file }} wurde nur teilweise übertragen, bitte versuchen Sie es erneut!',
    'The format <b>{{ format }}</b> is not defined!'
      => 'Das Format <b>{{ format }}</b> ist nicht definiert!',
    '<p>The settings has changed:</p>{{ changed_settings }}'
      => '<p>Die Einstellungen wurden geändert:</p>{{ changed_settings }}',
    '<p>The settings has not changed.</p>'
      => '<p>Die Einstellungen wurden nicht geändert.</p>',
    'The type <b>{{ type }}</b> is not defined!'
      => 'Der Typ <b>{{ type }}</b> ist nicht definiert!',
    'The uploaded file <b>{{ file }}</b> is greater than MAX_FILE_SIZE within the form directive.'
      => 'Die übertragene Datei <b>{{ file }}</b> ist größer als die MAX_FILE_SIZE Direktive innerhalb des Formulars!',
    'The uploaded file <b>{{ file }}</b> is greater than the parameter <b>upload_max_filesize</b> of <b>{{ max_size }}</b> within the <b>php.ini</b>'
      => 'Die übertragene Datei <b>{{ file }}</b> ist größer als der Parameter <b>upload_max_filesize</b> von <b>{{ max_size }}</b> in der <b>php.ini</b>.',
    'The uploaded file <b>{{ file }}</b> is not a valid XML file!'
      => 'Die übertragene Datei <b>{{ file }}</b> ist keine gültige XML Datei!',
    'The XML file <b>{{ file }}</b> does not exist!'
      => 'Die XML Datei <b>{{ file }}</b> existiert nicht!',
    '<p>There are no configuration records to save as XML file.</p>'
      => '<p>Es sind keine Konfigurations Einstellungen als XML Datei zu sichern.</p>',
    'There were no settings to process!'
      => 'Es gab keine Einstellungen die geändert werden mussten!',
    'There was no file specified for upload!'
      => 'Es wurde keine Datei für die Übertragung angegeben!',
    'To import settings from a XML file or to create a XML file from the settings please select the action you wish to perform.'
      => 'Um Einstellungen von einer XML Datei zu importieren oder um Einstellungen zu exportieren, wählen Sie bitte die gewünschte Aktion aus.',
    'XML file'
      => 'XML Datei',
    'Yes'
      => 'Ja'
    );