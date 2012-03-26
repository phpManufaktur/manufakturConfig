<?php

/**
 * manufakturConfig
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// wb2lepton compatibility
if (!defined('LEPTON_PATH')) require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

require_once LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/library.php';

// set cache and compile path for the template engine
$cache_path = LEPTON_PATH.'/temp/cache';
if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
$compiled_path = LEPTON_PATH.'/temp/compiled';
if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);

// init the template engine
global $parser;
if (!is_object($parser)) $parser = new Dwoo($compiled_path, $cache_path);


class manufakturConfigDialog {

  const REQUEST_ACTION = 'mca';
  const REQUEST_ITEMS = 'mci';

  const ACTION_DIALOG = 'dlg';
  const ACTION_CHECK = 'chk';

  private $message = '';
  private $error = '';
  private $module_directory = '';
  private $dialog_link = '';
  private $img_url = '';
  private $lang = null;

  public function __construct($module_directory, $dialog_link) {
    global $lang;
    $this->module_directory = $module_directory;
    $this->dialog_link = $dialog_link;
    $this->lang = $lang;
    $this->img_url = LEPTON_URL.'/modules/'.basename(dirname(__FILE__)).'/images/';
  } // __construct()

  /**
   * @return string $message
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param string $message
   */
  protected function setMessage($message) {
    $this->message = $message;
  }

  /**
   * Check if $this->message is empty
   *
   * @return boolean
   */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage

  /**
   * @return string $error
   */
  public function getError() {
    return $this->error;
  }

  /**
   * @param string $error
   */
  protected function setError($error) {
    $this->error = $error;
  }

  /**
   * Check if $this->message is empty
   *
   * @return boolean
   */
  public function isError() {
    return (bool) !empty($this->error);
  } // isMessage

  /**
   * Return the needed template
   *
   * @param $template string
   * @param $template_data array
   */
  protected function getTemplate($template, $template_data) {
    global $parser;

    $template_path = LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/backend/';

    // check if a custom template exists ...
    $load_template = (file_exists($template_path.'custom.'.$template)) ? $template_path.'custom.'.$template
    : $template_path.$template;
    try {
      $result = $parser->get($load_template, $template_data);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n(
          'Error executing the template ' . '<b>{{ template }}</b>: {{ error }}', array(
              'template' => basename($load_template),
              'error' => $e->getMessage()))));
      return false;
    }
    return $result;
  } // getTemplate()



  public function action() {
    global $manufakturConfig;

    $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DIALOG;

    switch ($action):
    default:
      $result = $this->show(self::ACTION_DIALOG, $this->dlgConfig());
    endswitch;

    return $result;
  } // action()

  protected function show($action, $content) {
    $navigation = null;
    $data = array(
        'LEPTON_URL' => LEPTON_URL,
        'IMG_URL' => $this->img_url,
        'navigation' => $navigation,
        'error' => ($this->isError()) ? 1 : 0,
        'content' => ($this->isError()) ? $this->getError() : $content
        );
    return $this->getTemplate('body.lte', $data);
  } // show();

  protected function dlgConfig() {
    global $manufakturConfig;

    $manufakturConfig->readXMLfile(LEPTON_PATH.'/modules/manufaktur_config/config.xml');

    $groups = array();
    if (false === ($count_groups = $manufakturConfig->countGroups($this->module_directory, $groups))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }
    if (false === ($count_sets = $manufakturConfig->countSets($this->module_directory))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }
    if ($count_groups > 1) {
      $group = (isset($_REQUEST[manufakturConfig::FIELD_VALUE_GROUP])) ? $_REQUEST[manufakturConfig::FIELD_VALUE_GROUP] : $groups[0];
    }
    else {
      $group = null;
    }

    $settings = array();
    if (!$manufakturConfig->getSettingsForModule($this->module_directory, $settings, $group, true)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $manufakturConfig->getError()));
      return false;
    }

    $items = array();
    foreach ($settings as $setting) {
      $value = isset($_REQUEST[manufakturConfig::FIELD_NAME]) ? $_REQUEST[manufakturConfig::FIELD_NAME] : $setting[manufakturConfig::FIELD_NAME];
      $items[$setting[manufakturConfig::FIELD_NAME]] = array(
          'name' => $setting[manufakturConfig::FIELD_NAME],
          'value' => manufakturConfig::unsanitize($value),
          );
    }

    return __METHOD__;

    $SQL = sprintf("SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s", $dbCronjobConfig->getTableName(), dbCronjobConfig::FIELD_STATUS, dbCronjobConfig::STATUS_DELETED, dbCronjobConfig::FIELD_NAME);
    $config = array();
    if (!$dbCronjobConfig->sqlExec($SQL, $config)) {
      $this->setError($dbCronjobConfig->getError());
      return false;
    }
    $count = array();
    $items = array();
    // bestehende Eintraege auflisten
    foreach ($config as $entry) {
      $id = $entry[dbCronjobConfig::FIELD_ID];
      $count[] = $id;
      $value = ($entry[dbCronjobConfig::FIELD_TYPE] == dbCronjobConfig::TYPE_LIST) ? $dbCronjobConfig->getValue($entry[dbCronjobConfig::FIELD_NAME])
          : $entry[dbCronjobConfig::FIELD_VALUE];
      if (isset($_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id]))
        $value = $_REQUEST[dbCronjobConfig::FIELD_VALUE . '_' . $id];
      $value = str_replace('"', '&quot;', stripslashes($value));
      $items[] = array(
          'id' => $id,
          'identifier' => $entry[dbCronjobConfig::FIELD_LABEL],
          'value' => $value,
          'name' => sprintf('%s_%s', dbCronjobConfig::FIELD_VALUE, $id),
          'description' => $entry[dbCronjobConfig::FIELD_HINT],
          'type' => $entry[dbCronjobConfig::FIELD_TYPE],
          'field' => $entry[dbCronjobConfig::FIELD_NAME]);
    }
    $data = array(
        'form' => array(
            'name' => 'cronjob_cfg',
            'action' => $this->page_link),
        'action' => array(
            'name' => self::REQUEST_ACTION,
            'value' => self::ACTION_CONFIG_CHECK),
        'request_items' => array(
            'name' => self::REQUEST_ITEMS,
            'value' => implode(",", $count)),
        'message' => array(
            'text' => $this->isMessage() ? $this->getMessage() : ''),
        'items' => $items,);
    return $this->getTemplate('config.lte', $data);

  } // dlgConfig()

} // class manufakturConfigDialog