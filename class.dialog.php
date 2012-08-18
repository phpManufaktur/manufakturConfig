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

// wb2lepton compatibility
if (!defined('LEPTON_PATH')) require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

require_once LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/library.php';
global $manufakturConfig;
if (!is_object($manufakturConfig)) $manufakturConfig = new manufakturConfig();

if (!class_exists('Dwoo'))
  require_once LEPTON_PATH.'/modules/dwoo/include.php';

// initialize the template engine
global $parser;
if (!is_object($parser)) {
  $cache_path = LEPTON_PATH.'/temp/cache';
  if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
  $compiled_path = LEPTON_PATH.'/temp/compiled';
  if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
  $parser = new Dwoo($compiled_path, $cache_path);
}
// load extensions for the template engine
$loader = $parser->getLoader();
$loader->addDirectory(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/plugins/');


class manufakturConfigDialog {

  const REQUEST_ACTION = 'mca';
  const REQUEST_ITEMS = 'mci';
  const REQUEST_XML_ACTION = 'xml';
  const REQUEST_XML_FILE = 'xmlf';
  const REQUEST_XML_MODULE_ONLY = 'xmo';
  const REQUEST_XML_RESET_VALUES = 'xrv';

  const ACTION_DIALOG = 'dlg';
  const ACTION_CHECK = 'chk';
  const ACTION_NO_ACTION = 'nix';
  const ACTION_XML_EXPORT_ALL = 'xexa';
  const ACTION_XML_EXPORT_MODULE = 'xexm';
  const ACTION_XML_IMPORT = 'xim';
  const ACTION_XML_FILE = 'xmlf';

  private static $message = '';
  private static $error = '';
  private static $module_directory = '';
  private static $module_name = '';
  private static $dialog_link = '';
  private static $img_url = '';
  private static $pages = null;
  private static $abort_link = '';
  protected $lang = null;

  /**
   * Constructor for the manufakturConfigDialog
   *
   * @param string $module_directory directory of the used module
   * @param string $module_name name of the used module
   * @param string $dialog_link link of the calling admin tool
   * @param string $abort_link link to use if the configuration dialog is aborted
   */
  public function __construct($module_directory, $module_name, $dialog_link, $abort_link='') {
    global $I18n;
    $this->lang = $I18n;
    self::$module_directory = $module_directory;
    self::$module_name = $module_name;
    self::$dialog_link = $dialog_link;
    self::$abort_link = $abort_link;
    self::$img_url = LEPTON_URL.'/modules/'.basename(dirname(__FILE__)).'/images/';
    self::$pages = null;
    // make shure that the KIT_HTML_REQUEST session is active!
    if (!isset($_SESSION['KIT_HTML_REQUEST'])) $_SESSION['KIT_HTML_REQUEST'] = array();
  } // __construct()

  /**
   * @return string $message
   */
  public function getMessage() {
    return self::$message;
  }

  /**
   * @param string $message
   */
  protected function setMessage($message) {
    self::$message = $message;
  }

  /**
   * Check if $this->message is empty
   *
   * @return boolean
   */
  public function isMessage() {
    return (bool) !empty(self::$message);
  } // isMessage

  /**
   * @return string $error
   */
  public function getError() {
    return self::$error;
  }

  /**
   * @param string $error
   */
  protected function setError($error) {
    self::$error = $error;
  }

  /**
   * Check if $this->message is empty
   *
   * @return boolean
   */
  public function isError() {
    return (bool) !empty(self::$error);
  } // isMessage

  /**
   * Return the needed template
   *
   * @param $template string
   * @param $template_data array
   */
  protected function getTemplate($template, $template_data, $trigger_error=false) {
    global $parser;

    $template_path = LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/backend/';

    // check if a custom template exists ...
    $load_template = (file_exists($template_path.'custom.'.$template)) ? $template_path.'custom.'.$template : $template_path.$template;
    try {
      $result = $parser->get($load_template, $template_data);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate(
          'Error executing the template <b>{{ template }}</b>: {{ error }}', array(
              'template' => basename($load_template),
              'error' => $e->getMessage()))));
      if ($trigger_error)
        trigger_error($this->getError(), E_USER_ERROR);
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * The action handler for manufakturConfigDialog
   * This function expects that the xssPrevent method will be executed by the
   * calling backend module!
   *
   * @return string result dialog
   */
  public function action() {
    global $manufakturConfig;

    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DIALOG;

    if (isset($_REQUEST[self::REQUEST_XML_ACTION]) && ($_REQUEST[self::REQUEST_XML_ACTION] != self::ACTION_NO_ACTION)) {
      $action = $_REQUEST[self::REQUEST_XML_ACTION];
    }

    switch ($action):
    case self::ACTION_XML_FILE:
      $result = $this->show(self::ACTION_DIALOG, $this->checkXMLfile());
      break;
    case self::ACTION_XML_EXPORT_ALL:
    case self::ACTION_XML_EXPORT_MODULE:
      $result = $this->show(self::ACTION_DIALOG, $this->xmlExport());
      break;
    case self::ACTION_XML_IMPORT:
      $result = $this->show(self::ACTION_DIALOG, $this->xmlImport());
      break;
    case self::ACTION_DIALOG:
      $result = $this->show(self::ACTION_DIALOG, $this->dlgSettings());
      break;
    case self::ACTION_CHECK:
      $result = $this->show(self::ACTION_DIALOG, $this->checkSettings());
      break;
    default:
      $result = $this->show(self::ACTION_DIALOG, $this->dlgSettings());
    endswitch;
    return $result;
  } // action()

  /**
   * Return the completet template, ready for display
   *
   * @param string $action not used at the moment
   * @param string $content the dialog to display
   * @return Ambigous <boolean, string, mixed>
   */
  protected function show($action, $content) {
    $navigation = self::$pages;
    $data = array(
        'LEPTON_URL' => LEPTON_URL,
        'IMG_URL' => self::$img_url,
        'navigation' => $navigation,
        'error' => ($this->isError()) ? 1 : 0,
        'content' => ($this->isError()) ? $this->getError() : $content
        );
    return $this->getTemplate('body.lte', $data, true);
  } // show();

  /**
   * The main dialog for the settings
   *
   * @return string dialog
   */
  protected function dlgSettings() {
    global $manufakturConfig;

    $pages = array();
    if (false === ($count_pages = $manufakturConfig->countPages(self::$module_directory, $pages))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }
    if ($count_pages > 1) {
      $page = (isset($_REQUEST[manufakturConfig::FIELD_VALUE_PAGE])) ? urldecode($_REQUEST[manufakturConfig::FIELD_VALUE_PAGE]) : $pages[0];
    }
    else {
      $page = null;
    }
    $settings = array();
    if (!$manufakturConfig->getSettingsForModule(self::$module_directory, $settings, $page, true)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }
    $pages_array = array();
    foreach ($pages as $text) {
      $pages_array[] = array(
          'text' => $text,
          'link' => sprintf('%s&amp;%s', self::$dialog_link, http_build_query(array(
              self::REQUEST_ACTION => self::ACTION_DIALOG,
              manufakturConfig::FIELD_VALUE_PAGE => urlencode($text)
              ))),
          'is_active' => ($text == $page) ? 1 : 0
          );
    }
    self::$pages = array(
        'is_active' => is_null($page) ? 0 : 1,
        'actual' => $page,
        'items' => $pages_array
    );

    $items = array();
    $cfg_ids = array();
    foreach ($settings as $setting) {
      $cfg_ids[] = $setting[manufakturConfig::FIELD_ID];
      $value = isset($_REQUEST[manufakturConfig::FIELD_NAME]) ? $_REQUEST[manufakturConfig::FIELD_NAME] : $setting[manufakturConfig::FIELD_VALUE];
      $items[$setting[manufakturConfig::FIELD_NAME]] = array(
          'id' => $setting[manufakturConfig::FIELD_ID],
          'name' => $setting[manufakturConfig::FIELD_NAME],
          'value' => $manufakturConfig->formatValue($value, $setting[manufakturConfig::FIELD_TYPE], manufakturConfig::FORMAT_OUTPUT),
          'type' => $setting[manufakturConfig::FIELD_TYPE],
          'set' => $setting[manufakturConfig::FIELD_VALUE_SET],
          'page' => $setting[manufakturConfig::FIELD_VALUE_PAGE],
          'timestamp' => $setting[manufakturConfig::FIELD_TIMESTAMP],
          'label' => manufakturConfig::unsanitize($setting[manufakturConfig::FIELD_LABEL]),
          'hint' => manufakturConfig::unsanitize($setting[manufakturConfig::FIELD_HINT]),
          'module_group' => $setting[manufakturConfig::FIELD_MODULE_GROUP],
          'version' => $setting[manufakturConfig::FIELD_VERSION]
          );
      // allow HTML REQUESTs for fields of type STRING and TEXT
      if (($setting[manufakturConfig::FIELD_TYPE] == manufakturConfig::TYPE_STRING) ||
          ($setting[manufakturConfig::FIELD_TYPE] == manufakturConfig::TYPE_TEXT)) {
        if (!in_array($setting[manufakturConfig::FIELD_NAME], $_SESSION['KIT_HTML_REQUEST']))
          $_SESSION['KIT_HTML_REQUEST'][] = $setting[manufakturConfig::FIELD_NAME];
      }
    }
    $data = array(
        'module_directory' => self::$module_directory,
        'module_name' => self::$module_name,
        'form' => array(
            'name' => 'manufaktur_cfg',
            'action' => self::$dialog_link,
            'abort' => (!empty(self::$abort_link)) ? self::$abort_link : self::$dialog_link
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_CHECK
            ),
        'page' => array(
            'name' => (is_null($page)) ? null : manufakturConfig::FIELD_VALUE_PAGE,
            'value' => $page
            ),
        'xml' => array(
            'name' => self::REQUEST_XML_ACTION,
            'value' => self::ACTION_NO_ACTION,
            'items' => array(
                array(
                    'value' => self::ACTION_NO_ACTION,
                    'text' => $this->lang->translate('- no XML action -')
                    ),
                array(
                    'value' => self::ACTION_XML_EXPORT_MODULE,
                    'text' => $this->lang->translate('Export the settings for this module as XML file (single)')
                    ),
                array(
                    'value' => self::ACTION_XML_EXPORT_ALL,
                    'text' => $this->lang->translate('Export the settings for all modules as XML file (complete)')
                    ),
                array(
                    'value' => self::ACTION_XML_IMPORT,
                    'text' => $this->lang->translate('Import settings from XML file')
                    )
                )
            ),
        'request_items' => array(
            'name' => self::REQUEST_ITEMS,
            'value' => implode(",", $cfg_ids)),
        'message' => array(
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'items' => $items,);
    return $this->getTemplate('config.lte', $data);
  } // dlgSettings()

  /**
   * Check the result of the setting dialog and call him again
   *
   * @return string dlgSettings()
   */
  protected function checkSettings() {
    global $manufakturConfig;

    if (!isset($_REQUEST[self::REQUEST_ITEMS])) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Missing the parameter REQUEST_ITEMS.')));
      return false;
    }
    $items = $_REQUEST[self::REQUEST_ITEMS];
    $items = explode(',', $items);

    $changed_settings = false;
    if (!$manufakturConfig->checkSettings($items, $changed_settings)) {
      if ($manufakturConfig->isError()) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
        return false;
      }
      // no error, but something does not match ...
      $this->setMessage($manufakturConfig->getMessage());
    }
    elseif ($changed_settings) {
      // settings has changed
      $this->setMessage($this->lang->translate('<p>The settings has changed:</p>{{ changed_settings }}',
          array('changed_settings' => $manufakturConfig->getMessage())));
    }
    else {
      // nothing changed
      $this->setMessage($this->lang->translate('<p>The settings has not changed.</p>'));
    }
    return $this->dlgSettings();
  } // checkSettings()

  /**
   * Export the settings for the specified module or for all modules as XML
   * file in the media directory.
   *
   * @return string dialog config
   */
  protected function xmlExport() {
    global $manufakturConfig;

    if ($_REQUEST[self::REQUEST_XML_ACTION] == self::ACTION_XML_EXPORT_MODULE) {
      $module_directory = self::$module_directory;
      $path = LEPTON_PATH.MEDIA_DIRECTORY.'/'.date('ymd').'-'.$module_directory.'-config.xml';
    }
    else {
      $module_directory = null;
      $path = LEPTON_PATH.MEDIA_DIRECTORY.'/'.date('ymd').'-complete-config.xml';
    }
    if (!$manufakturConfig->writeXMLfile($path, $module_directory)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }
    $this->setMessage($manufakturConfig->getMessage());
    return $this->dlgSettings();
  } // xmlExport()

  /**
   * Display a XML import dialog with additional options and settings
   *
   * @return string import dialog
   */
  protected function xmlImport() {
    $data = array(
        'form' => array(
            'name' => 'manufaktur_import_xml',
            'action' => self::$dialog_link,
            'abort' => (!empty(self::$abort_link)) ? self::$abort_link : self::$dialog_link
            ),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_XML_FILE
             ),
        'message' => array(
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'xml' => array(
            'module_name' => self::$module_name,
            'module_directory' => self::$module_directory,
            'name' => self::REQUEST_XML_FILE,
            'module_only' => array(
                'name' => self::REQUEST_XML_MODULE_ONLY,
                'value' => (isset($_REQUEST[self::REQUEST_XML_MODULE_ONLY])) ? 1 : 1
                ),
            'reset_values' => array(
                'name' => self::REQUEST_XML_RESET_VALUES,
                'value' => (isset($_REQUEST[self::REQUEST_XML_RESET_VALUES])) ? 1 : 0
                )
            )
        );
    return $this->getTemplate('load.xml.lte', $data);
  } // xmlImport()

  /**
   * Check the XML file and call the library to process the import of
   * the uploaded XML file.
   *
   * @return string config dialog with status message(s)
   */
  protected function checkXMLfile() {
    global $manufakturConfig;

    $xml_path = null;
    // first: check upload
    if (isset($_FILES[self::REQUEST_XML_FILE]) && (is_uploaded_file($_FILES[self::REQUEST_XML_FILE]['tmp_name']))) {
      if ($_FILES[self::REQUEST_XML_FILE]['error'] == UPLOAD_ERR_OK) {
        if ($_FILES[self::REQUEST_XML_FILE]['type'] != 'text/xml') {
          // this is not a XML file!
          $this->setMessage($this->lang->translate('The uploaded file <b>{{ file }}</b> is not a valid XML file!',
              array('file' => $_FILES[self::REQUEST_XML_FILE]['name'])));
          @unlink($_FILES[self::REQUEST_XML_FILE]['tmp_name']);
          return $this->xmlImport();
        }
        $xml_path = LEPTON_PATH.'/temp/'.$_FILES[self::REQUEST_XML_FILE]['name'];
        if (!move_uploaded_file($_FILES[self::REQUEST_XML_FILE]['tmp_name'], $xml_path)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
              $this->lang->translate('The file {{ file }} could not moved to the temporary directory.',
                  array('file' => $_FILES[self::REQUEST_XML_FILE]['name']))));
          return false;
        }
      }
      else {
        switch ($_FILES[self::REQUEST_XML_FILE]['error']) :
        case UPLOAD_ERR_INI_SIZE:
          $error = $this->lang->translate('The uploaded file <b>{{ file }}</b> is greater than the parameter <b>upload_max_filesize</b> of <b>{{ max_size }}</b> within the <b>php.ini</b>',
              array('max_size' => ini_get('upload_max_filesize'), 'file' => $_FILES[self::REQUEST_XML_FILE]['name']));
          break;
        case UPLOAD_ERR_FORM_SIZE:
          $error = $this->lang->translate('The uploaded file <b>{{ file }}</b> is greater than MAX_FILE_SIZE within the form directive.',
              array('file' => $_FILES[self::REQUEST_XML_FILE]['name']));
          break;
        case UPLOAD_ERR_PARTIAL:
          $error = $this->lang->translate('The file <b>{{ file }}</b> was uploaded partial, please try again!',
              array('file' => $_FILES[self::request_file]['name']));
          break;
        default:
          $error = $this->lang->translate('A not described error occured during file upload, please try again!');
          break;
        endswitch;
        @unlink($_FILES[self::REQUEST_XML_FILE]['tmp_name']);
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $error));
        return false;
      }
    }
    else {
      // nothing to do ...
      $this->setMessage($this->lang->translate('There was no file specified for upload!'));
      return $this->xmlImport();
    }
    $module_directory = isset($_REQUEST[self::REQUEST_XML_MODULE_ONLY]) ? self::$module_directory : null;
    $reset_values = isset($_REQUEST[self::REQUEST_XML_RESET_VALUES]) ? true : false;
    if (!$manufakturConfig->readXMLfile($xml_path, $module_directory, $reset_values)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }
    $this->setMessage($manufakturConfig->getMessage());
    return $this->dlgSettings();
  } // checkXMLfile()

} // class manufakturConfigDialog