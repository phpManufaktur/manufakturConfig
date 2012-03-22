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

if (!class_exists('manufaktur_I18n'))
  require_once LEPTON_PATH.'/modules/manufaktur_i18n/library.php';
global $lang;
if (!is_object($lang)) $lang = new manufaktur_I18n('manufaktur_config', LANGUAGE);

class dbManufakturConfig {

  const FIELD_ID = 'cfg_id';
  const FIELD_NAME = 'cfg_name';
  const FIELD_TYPE = 'cfg_type';
  const FIELD_VALUE = 'cfg_value';
  const FIELD_LABEL = 'cfg_label';
  const FIELD_HINT = 'cfg_hint';
  const FIELD_STATUS = 'cfg_status';
  const FIELD_TIMESTAMP = 'cfg_timestamp';

  const STATUS_ACTIVE = 'ACTIVE';
  const STATUS_DELETED = 'DELETED';
  const STATUS_LOCKED = 'LOCKED';

  const TYPE_UNDEFINED = 'UNDEFINED';
  const TYPE_ARRAY = 'ARRAY';
  const TYPE_BOOLEAN = 'BOOLEAN';
  const TYPE_EMAIL = 'EMAIL';
  const TYPE_FLOAT = 'FLOAT';
  const TYPE_INTEGER = 'INTEGER';
  const TYPE_LIST = 'LIST';
  const TYPE_PATH = 'PATH';
  const TYPE_STRING = 'STRING';
  const TYPE_URL = 'URL';

  private $message = '';
  private $error = '';

  protected $lang = NULL;

  public function __construct() {
    global $lang;
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $lang;
  } // __construct()

  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_manufaktur_cfg` ( ".
        "`cfg_id` INT(11) NOT NULL AUTO_INCREMENT, ".
        "`cfg_name` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`cfg_type` ENUM('UNDEFINED','ARRAY','BOOLEAN','EMAIL','FLOAT','INTEGER','LIST','STRING') NOT NULL DEFAULT 'UNDEFINED', ".
        "`cfg_value` TEXT, ".
        "`cfg_label` VARCHAR(64) NOT NULL DEFAULT '- undefined -', ".
        "`cfg_hint` TEXT, ".
        "`cfg_status` ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE', ".
        "`cfg_timestamp` TIMESTAMP "."PRIMARY KEY (`cfg_id`), KEY (`cfg_name`) ) ".
        "ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
    $database->query($SQL);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
      return false;
    }
    return true;
  } // createTable()

  /**
   * @return string $message
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param string $message
   */
  public function setMessage($message) {
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
  public function setError($error) {
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


} // class dbManufakturConfig
