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
if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

// load language depending onfiguration from manufakturConfig
if (file_exists(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/' . LANGUAGE . '.cfg.php'))
  require_once(LEPTON_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');
else
  require_once(LEPTON_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/EN.cfg.php');

// use LEPTON 2.x I18n for access to language files
if (!class_exists('LEPTON_Helper_I18n'))
  require_once LEPTON_PATH.'/modules/'. basename(dirname(__FILE__)).'/framework/LEPTON/Helper/I18n.php';

global $I18n;
if (!is_object($I18n))
  $I18n = new LEPTON_Helper_I18n();
else
  $I18n->addFile('DE.php', LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');

class manufakturConfig {

  const FIELD_ID = 'cfg_id';
  const FIELD_NAME = 'cfg_name';
  const FIELD_TYPE = 'cfg_type';
  const FIELD_USAGE = 'cfg_usage';
  const FIELD_VALUE = 'cfg_value';
  const FIELD_VALUE_SET = 'cfg_value_set';
  const FIELD_VALUE_SET_ORDER = 'cfg_value_set_order';
  const FIELD_VALUE_PAGE = 'cfg_value_page';
  const FIELD_LABEL = 'cfg_label';
  const FIELD_HINT = 'cfg_hint';
  const FIELD_MODULE_NAME = 'cfg_module_name';
  const FIELD_MODULE_DIRECTORY = 'cfg_module_directory';
  const FIELD_MODULE_GROUP = 'cfg_module_group';
  const FIELD_VERSION = 'cfg_version';
  const FIELD_UPDATE = 'cfg_update';
  const FIELD_TIMESTAMP = 'cfg_timestamp';

  const TYPE_ARRAY = 'ARRAY';
  const TYPE_BOOLEAN = 'BOOLEAN';
  const TYPE_EMAIL = 'EMAIL';
  const TYPE_FLOAT = 'FLOAT';
  const TYPE_INTEGER = 'INTEGER';
  const TYPE_LIST = 'LIST';
  const TYPE_STRING = 'STRING';
  const TYPE_TEXT = 'TEXT';

  const USAGE_REGULAR = 'REGULAR';
  const USAGE_HIDDEN = 'HIDDEN';
  const USAGE_READONLY = 'READONLY';

  const UPDATE_INSERT = 'INSERT';
  const UPDATE_DELETE = 'DELETE';
  const UPDATE_OVERWRITE = 'OVERWRITE';

  const FORMAT_OUTPUT = 'OUTPUT';
  const FORMAT_SAVE = 'SAVE';
  const FORMAT_PROCESS = 'PROCESS';

  private $format_array = array(
      self::FORMAT_OUTPUT,
      self::FORMAT_PROCESS,
      self::FORMAT_SAVE
      );

  private $table_name = null;

  private $message = '';
  private $error = '';
  private $module_directory = null;

  protected $lang = NULL;

  private $field_array = array(
      self::FIELD_ID => -1,
      self::FIELD_NAME => '',
      self::FIELD_TYPE => self::TYPE_STRING,
      self::FIELD_USAGE => self::USAGE_REGULAR,
      self::FIELD_VALUE => '',
      self::FIELD_VALUE_SET => 'NONE',
      self::FIELD_VALUE_PAGE => 'NONE',
      self::FIELD_LABEL => '',
      self::FIELD_HINT => '',
      self::FIELD_MODULE_NAME => '',
      self::FIELD_MODULE_DIRECTORY => '',
      self::FIELD_MODULE_GROUP => '',
      self::FIELD_VERSION => '',
      self::FIELD_UPDATE => '',
      self::FIELD_TIMESTAMP => 'timestamp');

  private $must_fields = array(
      self::FIELD_NAME,
      self::FIELD_TYPE,
      self::FIELD_VALUE,
      self::FIELD_LABEL
  );

  /**
   * Constructor for manufakturConfig. If a module_directory is specified the
   * the constructor init the class directly for this addon.
   *
   * @param string $module_directory
   */
  public function __construct($module_directory = null) {
    global $I18n;
    global $database;

    if (method_exists('database', 'prompt_on_error'))
      // we dont't need the direct error prompt of LEPTON
      $database->prompt_on_error(false);

    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $I18n;
    $this->table_name = TABLE_PREFIX.'mod_manufaktur_config';
    $this->module_directory = $module_directory;
  } // __construct()

  /**
   * Create the database table for manufakturConfig.
   * Set error message at any SQL problem.
   *
   * @return boolean
   */
  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".$this->getTableName()."` ( ".
        "`cfg_id` INT(11) NOT NULL AUTO_INCREMENT, ".
        "`cfg_name` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`cfg_type` ENUM('ARRAY','BOOLEAN','EMAIL','FLOAT','INTEGER','LIST','STRING','TEXT') NOT NULL DEFAULT 'STRING', ".
        "`cfg_usage`ENUM('REGULAR','HIDDEN','READONLY') NOT NULL DEFAULT 'REGULAR', ".
        "`cfg_value` TEXT, ".
        "`cfg_value_set` VARCHAR(64) NOT NULL DEFAULT 'NONE', ".
        "`cfg_value_set_order` INT(11) NOT NULL DEFAULT '0', ".
        "`cfg_value_page` VARCHAR(64) NOT NULL DEFAULT 'NONE', ".
        "`cfg_label` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`cfg_hint` TEXT, ".
        "`cfg_module_name` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`cfg_module_directory` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`cfg_module_group` VARCHAR(64) NOT NULL DEFAULT '', ".
        "`cfg_version` VARCHAR(16) NOT NULL DEFAULT '', ".
        "`cfg_update` ENUM('INSERT','DELETE','OVERWRITE') NOT NULL DEFAULT 'INSERT', ".
        "`cfg_timestamp` TIMESTAMP, "."PRIMARY KEY (`cfg_id`), KEY (`cfg_name`)".
        " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
    $database->query($SQL);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // createTable()

  /**
   * Delete the database table of manufakturConfig.
   * Set error message at any SQL problem.
   *
   * @return boolean
   */
  public function deleteTable() {
    global $database;
    $database->query('DROP TABLE IF EXISTS `'.$this->getTableName().'`');
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // deleteTable()

  /**
   * Return the complete table name with the table prefix
   *
   * @param string $table_name
   */
  protected function getTableName() {
    return $this->table_name;
  } // getTableName();

  /**
   * @return string $message
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param string $message
   */
  protected function setMessage($message='') {
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
  protected function setError($error='') {
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
   * Return a readable error string from the given XML error level.
   *
   * @param integer $error_level LIBXML_ERR_WARNING|LIBXML_ERR_ERROR|LIBXML_ERR_FATAL
   * @return string XML error level
   */
  protected function XMLerrorLevel2string($error_level) {
    switch ($error_level) :
    case LIBXML_ERR_WARNING:
      $error_str = 'WARNING';
    break;
    case LIBXML_ERR_ERROR:
      $error_str = 'ERROR';
      break;
    case LIBXML_ERR_FATAL:
      $error_str = 'FATAL ERROR';
      break;
    default:
      $error_str = 'UNKNOWN ERROR LEVEL';
      endswitch;
      return $error_str;
  } // XMLerrorLevel2string()

  /**
   * Return the version of manufakturConfig
   *
   * @return float module version
   */
  public function getVersion() {
    global $database;
    $version = $database->get_one("SELECT `version` FROM ".TABLE_PREFIX."addons WHERE `directory`='manufaktur_config'", MYSQL_ASSOC);
    return floatval($version);
  } // getVersion()


  /**
   * Setter for the module directory which should be used.
   *
   * @param string $module_directory the name of the module directory
   */
  public function setModuleDirectory($module_directory) {
    $this->module_directory = $module_directory;
  } // setModuleDirectory()

  /**
   * Getter for the used module directory
   *
   * @return string module directory
   */
  public function getModuleDirectory() {
    return $this->module_directory;
  } // getModuleDirectory()

  /**
   * Sanitize any given value.
   *
   * @param string $item
   * @return string santized value
   */
  public static function sanitize($item) {
    if (!is_array($item)) {
      // undoing 'magic_quotes_gpc = On' directive
      if (get_magic_quotes_gpc()) $item = stripcslashes($item);
      $item = str_replace("<", "&lt;", $item);
      $item = str_replace(">", "&gt;", $item);
      $item = str_replace("\"", "&quot;", $item);
      $item = str_replace("'", "&#039;", $item);
      $item = mysql_real_escape_string($item);
    }
    return $item;
  } // sanitize()

  /**
   * Unsanitize the given value.
   *
   * @param string $item
   * @return string unsanitized value
   */
  public static function unsanitize($item) {
    $item =  stripcslashes($item);
    $item = str_replace("&#039;", "'", $item);
    $item = str_replace("&gt;", ">", $item);
    $item = str_replace("&quot;", "\"", $item);
    $item = str_replace("&lt;", "<", $item);
    return $item;
  } // unsanitize()

  /**
    * Generate a password with the desired length
    *
    * @param integer $length
    * @return string
    */
  public static function generatePassword($length=7) {
    $new_pass = '';
    $salt = 'abcdefghjkmnpqrstuvwxyz123456789';
    srand((double)microtime()*1000000);
    $i=0;
    while ($i <= $length) {
      $num = rand() % 33;
      $tmp = substr($salt, $num, 1);
      $new_pass = $new_pass . $tmp;
      $i++;
    }
    return $new_pass;
  } // generatePassword()


  /**
   * Check if to use the module group instead of the module directory to gather
   * the settings for the module
   *
   * @param string $module_directory
   * @param string reference $group
   * @return boolean
   */
  protected function useModuleGroup($module_directory, &$group='') {
    global $database;
    $group = '';
    // check if to use group instead of module_directory...
    $SQL = "SELECT `cfg_value`, `cfg_module_group` FROM `".$this->getTableName()."` WHERE ".
        "`cfg_module_directory`='$module_directory' AND `cfg_name`='mcUseGroup'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query->numRows() > 0) {
      if (false !== ($result = $query->fetchRow(MYSQL_ASSOC))) {
        $group = $result[self::FIELD_MODULE_GROUP];
        return (bool) $result[self::FIELD_VALUE];
      }
    }
    return false;
  } // useModuleGroup()

  /**
   * Count the Pages for the desired module. Each page can be displayed by
   * the config dialog in a separate tabulator.
   * If mcPagesOrder exists for the $module_directory the function will return
   * the groups in order of mcPagesOrder.
   * if mcPagesIgnore exists for the $module_directory and is set to true (1)
   * the function will return 1 to simulate a single page for the settings.
   *
   * @param string $module_directory
   * @param array reference $pages
   * @return boolean|number
   */
  public function countPages($module_directory, &$pages=array()) {
    global $database;

    $pages = array();
    // if mcPagesIgnore exists and is set to true we dont't count the pages...
    $SQL = "SELECT `cfg_value` FROM `".$this->getTableName()."` WHERE ".
        "`cfg_module_directory`='$module_directory' AND `cfg_name`='mcPagesIgnore'";
    $ignore_pages = $database->get_one($SQL, MYSQL_ASSOC);
    if (!is_null($ignore_pages) && ($ignore_pages == 1)) {
      // pages should be ignored, return 1 to indicate a single page ...
      return 1;
    }
    $group = '';
    // check if to use group instead of module directory
    $use_group = $this->useModuleGroup($module_directory, $group);

    // check if a mcPagesOrder exists!
    if ($use_group) {
      $SQL = "SELECT `cfg_value` FROM `".$this->getTableName()."` WHERE ".
          "`cfg_module_group`='$group' AND `cfg_name`='mcPagesOrder'";
    }
    else {
      $SQL = "SELECT `cfg_value` FROM `".$this->getTableName()."` WHERE ".
          "`cfg_module_directory`='$module_directory' AND `cfg_name`='mcPagesOrder'";
    }
    $order = $database->get_one($SQL, MYSQL_ASSOC);
    // get the pages
    if ($use_group) {
      $SQL = "SELECT DISTINCT `cfg_value_page` FROM `".$this->getTableName()."` WHERE ".
          "`cfg_module_group`='$group' AND `cfg_usage`!='HIDDEN' AND ".
          "`cfg_value_page`!='NONE' ORDER BY `cfg_value_page` ASC";
    }
    else {
      $SQL = "SELECT DISTINCT `cfg_value_page` FROM `".$this->getTableName()."` WHERE ".
          "`cfg_module_directory`='$module_directory' AND `cfg_usage`!='HIDDEN' AND ".
          "`cfg_value_page`!='NONE' ORDER BY `cfg_value_page` ASC";
    }
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if (!is_null($order)) {
      $pages = explode(',', $order);
    }
    else {
      while (false !== ($page = $query->fetchRow(MYSQL_ASSOC))) {
        $pages[] = trim($page[self::FIELD_VALUE_PAGE]);
      }
    }
    return $query->numRows();
  } // countPages()

  /**
   * Get the settings for the specified module from the database table.
   * Sorts the fields by field cfg_value_set in alphabetical order. If the special
   * field mcFieldsetOrder exists, order the fieldsets in the specified manner.
   *
   * @param string $module_directory name of the module directory
   * @param array $settings reference to the returned array with the setting records for the module
   * @param null|string $page if specified return only the settings for this page
   * @param boolean $group_by_sets if true order the result first by sets and then by name
   * @return boolean set error message at any problem and return false
   */
  public function getSettingsForModule($module_directory, &$settings=array(), $page=null, $group_by_sets=false) {
    global $database;

    $group = '';
    if ($this->useModuleGroup($module_directory, $group)) {
      $where = "`cfg_module_group`='$group'";
    }
    else {
      $where = "`cfg_module_directory`='$module_directory'";
    }
    $select_page = (is_null($page)) ? '' : " AND `cfg_value_page`='$page'";
    if ($group_by_sets) {
      // first check if a mcFieldsetOrder exists!
      $SQL = "SELECT `cfg_value` FROM `".$this->getTableName()."` WHERE ".
          "`cfg_module_directory`='$module_directory' AND `cfg_name`='mcFieldsetOrder'";
      $order = $database->get_one($SQL, MYSQL_ASSOC);
      if (is_null($order)) {
        $fieldset = "`cfg_value_set` ASC, `cfg_value_set_order` ASC";
      }
      else {
        $sets = explode(',', $order);
        $fieldset = "FIELD(`cfg_value_set`";
        $start = true;
        foreach ($sets as $set) {
          $start ? $fieldset .= "," : $start = false;
          $fieldset .= "'$set'";
        }
        $fieldset .= "), `cfg_value_set_order` ASC";
      }
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE $where AND ".
          "`cfg_usage`='REGULAR'$select_page ORDER BY $fieldset, `cfg_name` ASC";
    }
    else {
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE $where AND ".
          "`cfg_usage`='REGULAR'$select_page ORDER BY `cfg_name` ASC";
    }
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $settings = array();
    while (false !== ($setting = $query->fetchRow(MYSQL_ASSOC))) {
      $settings[] = $setting;
    }
    return true;
  } // getSettingsForModule()

  /**
   * Delete the setting for the module in $module_directory
   *
   * @param string $module_directory
   * @return boolean
   */
  public function deleteSettingsForModule($module_directory) {
    global $database;

    $SQL = sprintf("DELETE FROM `%s` WHERE `cfg_module_directory`='%s'", $this->getTableName(), $module_directory);
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // deleteSettingsForModule()

  /**
   * Check the settings and update database record.
   *
   * @param array $id_array
   * @param boolean reference $changed_settings
   * @return boolean
   */
  public function checkSettings($id_array=array(), &$changed_settings=false) {
    global $database;

    $message = '';
    // reset message
    $this->setMessage();
    if (count($id_array) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Got no IDs for processing the settings!')));
      return false;
    }
    $SQL = sprintf('SELECT * FROM `%1$s` WHERE FIND_IN_SET(`cfg_id`, \'%2$s\') ORDER BY FIND_IN_SET(`cfg_id`, \'%2$s\')',
        $this->getTableName(), implode(',', $id_array));
    $query = $database->query($SQL);
    if ($database->is_error()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    while (false !== ($setting = $query->fetchRow(MYSQL_BOTH))) {
      if (isset($_REQUEST[$setting[self::FIELD_NAME]])) {
        if (null == ($value = $this->formatValue($_REQUEST[$setting[self::FIELD_NAME]], $setting[self::FIELD_TYPE], self::FORMAT_SAVE))) {
          if ($this->isError()) {
            // error while checking the value by type ...
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
          }
          elseif ($this->isMessage()) {
            // problem while checking the value by type ...
            $message .= $this->getMessage();
            $this->setMessage();
            return false;
          }
        }
        if ($value != $setting[self::FIELD_VALUE]) {
          $changed_settings = true;
          $SQL = sprintf("UPDATE `%s` SET `cfg_value`='%s' WHERE `cfg_id`='%d'",
              $this->getTableName(), $value, $setting[self::FIELD_ID]);
          if (null == ($database->query($SQL))) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return false;
          }
          $message .= $this->lang->translate('<p>Set value of <b>{{ field_name }}</b> to <i>{{ field_value }}</i>.</p>',
              array('field_name' => $setting[self::FIELD_NAME], 'field_value' => self::unsanitize($value)));
        }
        unset($_REQUEST[$setting[self::FIELD_NAME]]);
      }
    }
    $this->setMessage($message);
    return true;
  } // checkSettings()

  /**
   * Check a given email address for logical errors
   *
   * @param string $email
   * @return boolean
   */
  public static function checkEMailAddress($email) {
    $result = (preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) ? true : false;
    return $result;
  } // checkEMailAddress()

  /**
   * Return a given string as float value. Uses the the manufakturI18n language
   * configuration settings or the specified values for the thousand and
   * decimal parameter
   *
   * @param string $string to convert as float value
   * @param null|string $thousand_separator if specified will be used
   * @param null|string $decimal_separator if specified will be used
   * @return float
   */
  public static function str2float($string, $thousand_separator=null, $decimal_separator=null) {
    $thousand_separator = is_null($thousand_separator) ? CFG_THOUSAND_SEPARATOR : $thousand_separator;
    $decimal_separator = is_null($decimal_separator) ? CFG_DECIMAL_SEPARATOR : $decimal_separator;
    $string = str_replace($thousand_separator, '', $string);
    $string = str_replace($decimal_separator, '.', $string);
    return floatval($string);
  } // str2float()

  /**
   * Return a given string as integer value. Uses the the manufakturI18n language
   * configuration settings or the specified values for the thousand and
   * decimal parameter
   *
   * @param string $string to convert as integer value
   * @param null|string $thousand_separator if specified will be used
   * @param null|string $decimal_separator if specified will be used
   * @return integer
   */
  public static function str2int($string, $thousand_separator=null, $decimal_separator=null) {
    $thousand_separator = is_null($thousand_separator) ? CFG_THOUSAND_SEPARATOR : $thousand_separator;
    $decimal_separator = is_null($decimal_separator) ? CFG_DECIMAL_SEPARATOR : $decimal_separator;
    $string = str_replace($thousand_separator, '', $string);
    $string = str_replace($decimal_separator, '.', $string);
    return intval($string);
  } // str2int()

  /**
   * Expects a value as string and return a formatted value depending on the type
   * of the value and the needed format.
   *
   * @param string $value
   * @param string $type constant (ARRAY, BOOLEAN ...)
   * @param string $format constant (OUTPUT, PROCESS, SAVE)
   * @return mixed formatted value, return NULL on error!
   */
  public function formatValue($value, $type, $format) {
    if (!in_array($format, $this->format_array)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('The format <b>{{ format }}</b> is not defined!', array('format' => $format))));
      return false;
    }
    switch ($type):
    case self::TYPE_ARRAY:
      // handle ARRAY values
      switch ($format):
      case self::FORMAT_OUTPUT:
        return $value;
      case self::FORMAT_PROCESS:
        return explode(',', $value);
      case self::FORMAT_SAVE:
        $dummy = explode(',', $value);
        $worker = array();
        foreach ($dummy as $item) {
          $item = strip_tags($item);
          $worker[] = trim($item);
        }
        return implode(',', $worker);
      endswitch;
      break;
    case self::TYPE_BOOLEAN:
      // handle BOOLEAN value
      switch ($format):
      case self::FORMAT_OUTPUT:
        return $value;
      case self::FORMAT_PROCESS:
        return (bool) $value;
      case self::FORMAT_SAVE:
        return (int) $value;
      endswitch;
      break;
    case self::TYPE_EMAIL:
      // handle EMAIL values
      switch ($format):
      case self::FORMAT_OUTPUT:
        return $value;
      case self::FORMAT_PROCESS:
        return $value;
      case self::FORMAT_SAVE:
        $value = strtolower(trim($value));
        if (!self::checkEMailAddress($value)) {
          $this->setMessage($this->lang->translate('<p>The email address <b>{{ email }}</b> is not valid!</p>',
              array('email' => $value)));
          return null;
        }
        return $value;
      endswitch;
    case self::TYPE_FLOAT:
      // handle FLOAT values
      switch ($format):
      case self::FORMAT_OUTPUT:
        return number_format(floatval($value), 2, CFG_DECIMAL_SEPARATOR, CFG_THOUSAND_SEPARATOR);
      case self::FORMAT_PROCESS:
        return floatval($value);
      case self::FORMAT_SAVE:
        return self::str2float($value, CFG_THOUSAND_SEPARATOR, CFG_DECIMAL_SEPARATOR);
      endswitch;
      break;
    case self::TYPE_INTEGER:
      // handle INTEGER values
      switch ($format):
      case self::FORMAT_OUTPUT:
        return number_format(intval($value), 0, CFG_DECIMAL_SEPARATOR, CFG_THOUSAND_SEPARATOR);
      case self::FORMAT_PROCESS:
        return intval($value);
      case self::FORMAT_SAVE:
        return self::str2int($value, CFG_THOUSAND_SEPARATOR, CFG_DECIMAL_SEPARATOR);
      endswitch;
      break;
    case self::TYPE_LIST:
      // handle LIST values
      switch ($format):
      case self::FORMAT_OUTPUT:
        return str_replace(",", "\n", $value);
      case self::FORMAT_PROCESS:
        return explode(',', $value);
      case self::FORMAT_SAVE:
        $value = strip_tags($value);
        $lines = nl2br($value);
        $lines = explode('<br />', $lines);
        $value = array();
        foreach ($lines as $line) {
          $line = trim($line);
          if (!empty($line)) $value[] = $line;
        }
        return implode(',', $value);
      endswitch;
      break;
    case self::TYPE_TEXT:
    case self::TYPE_STRING:
      // handle TEXT and STRING values
      switch ($format):
      case self::FORMAT_OUTPUT:
        return self::unsanitize($value);
      case self::FORMAT_PROCESS:
        return self::unsanitize($value);
      case self::FORMAT_SAVE:
        return self::sanitize($value);
      endswitch;
      break;
    default:
      // problem: undefined type!
      if (empty($type)) $value_type = 'NULL';
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('The type <b>{{ type }}</b> is not defined!', array('type' => $type))));
    endswitch;
    return null;
  } // formatValue

  /**
   * Insert a new value to the database or update an existing value
   *
   * @param array $data
   * @param integer $id
   * @param boolean $check_values
   */
  public function setValue($data, $id = null) {
    global $database;

    $message = '';
    $this->setMessage();
    // get a ID for the record
    if (is_null($id)) {
      $id = (isset($data[self::FIELD_ID])) ? (int) $data[self::FIELD_ID] : -1;
    }
    // unset ID and TIMESET
    if (isset($data[self::FIELD_ID])) unset($data[self::FIELD_ID]);
    if (isset($data[self::FIELD_TIMESTAMP])) unset($data[self::FIELD_TIMESTAMP]);
    if ($id > 0) {
      // Update an existing record
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE `cfg_id`='$id'";
      $query = $database->query($SQL);
      if ($database->is_error()) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      if ($query->numRows() < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate('The configuration record with the <b>ID {{ id }}</b> does not exist!',
                array('id', $id))));
        return false;
      }
      $old_data = $query->fetchRow(MYSQL_ASSOC);
    }
    else {
      // no ID, check if NAME is already in use
      if ((!isset($data[self::FIELD_NAME]) || empty($data[self::FIELD_NAME])) ||
          ((!isset($data[self::FIELD_MODULE_DIRECTORY]) || empty($data[self::FIELD_MODULE_DIRECTORY])) ||
              (!isset($data[self::FIELD_MODULE_NAME]) || empty($data[self::FIELD_MODULE_NAME])))) {
        // the field NAME and the MODULE NAME or the MODULE DIRECTORY must be set
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate('The configuration record is not valid, a <b>name</b> and the <b>module name</b> or the <b>module directory</b> is needed for identify')));
        return false;
      }
      $SQL = sprintf("SELECT * FROM `%s` WHERE `cfg_name`='%s' AND `cfg_module_directory`='%s'",
          $this->getTableName(),
          $data[self::FIELD_NAME],
          $data[self::FIELD_MODULE_DIRECTORY]);
      $query = $database->query($SQL);
      if ($database->is_error()) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      if ($query->numRows() > 0) {
        // the NAME is already in use, get the ID
        $old_data = $query->fetchRow(MYSQL_ASSOC);
        $id = $old_data[self::FIELD_ID];
      }
      else {
        // the NAME is not in use, create a new record
        $old_data = $this->field_array;
      }
    }

    if ($id > 0) {
      // if ID > 1 only the VALUE must be set
      if (!isset($data[self::FIELD_VALUE])) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate('Missing the <b>value</b> for the configuration record with the <b>ID {{ id }}</b>.',
                array('id', $id))));
        return false;
      }
      $changed = false;
      $type = (isset($data[self::FIELD_TYPE])) ? $data[self::FIELD_TYPE] : $old_data[self::FIELD_TYPE];

      foreach ($data as $key => $value) {
        if ($old_data[$key] != $value) $changed = true;
      }

      if (isset($data[self::FIELD_UPDATE]) && ($data[self::FIELD_UPDATE] == self::UPDATE_DELETE)) {
        // this setting must be deleted
        $SQL = "DELETE FROM `".$this->getTableName()."` WHERE `cfg_id`='$id'";
        $database->query($SQL);
        if ($database->is_error()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        $message .= $this->lang->translate('<p>The configuration record <b>{{ name }}</b> was successfull deleted.</p>',
            array('name' => $data[self::FIELD_NAME]));
      }
      elseif (($changed && !isset($data[self::FIELD_UPDATE])) ||
          ($changed && (isset($data[self::FIELD_UPDATE]) && ($data[self::FIELD_UPDATE] != self::UPDATE_INSERT))) ||
          (isset($data[self::FIELD_UPDATE]) && ($data[self::FIELD_UPDATE] == self::UPDATE_OVERWRITE))) {
        // update the setting
        $data[self::FIELD_UPDATE] = self::UPDATE_INSERT;
        $update = '';
        $start = true;
        foreach ($data as $key => $value) {
          if (!$start) $update .= ', ';
          $update .= "`$key`='$value'";
          if ($start) $start = false;
        }
        $SQL = "UPDATE `".$this->getTableName()."` SET $update WHERE `cfg_id`='$id'";
        $database->query($SQL);
        if ($database->is_error()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
          return false;
        }
        $message .= $this->lang->translate('<p>The configuration record <b>{{ name }}</b> was successfull updated.</p>',
            array('name' => $data[self::FIELD_NAME]));
      }
    }
    elseif (isset($data[self::FIELD_UPDATE]) && ($data[self::FIELD_UPDATE] == self::UPDATE_DELETE)) {
      // field is already deleted, nothing to do ...
    }
    else {
      // new record - all important fields must be set...
      if (isset($data[self::FIELD_USAGE]) && ($data[self::FIELD_USAGE] == self::USAGE_HIDDEN)) {
        // special case: setting only for the internal usage, need only NAME, TYPE and VALUE
        if (!isset($data[self::FIELD_NAME]) || !isset($data[self::FIELD_TYPE]) || !isset($data[self::FIELD_VALUE])) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
              $this->lang->translate('At minimun the fields NAME, TYPE and VALUE must set for configuration records of type HIDDEN.')));
          return false;
        }
        $data[self::FIELD_HINT] = '';
        $data[self::FIELD_LABEL] = '';
      }
      else {
        // regular record - check all fields
        foreach ($this->must_fields as $must) {
          if (!isset($data[$must])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                $this->lang->translate('The field <b>{{ field }}</b> must be defined!', array('field' => $must))));
            return false;
          }
        }
      }
      $data[self::FIELD_UPDATE] = self::UPDATE_INSERT;
      $fields = '';
      $values = '';
      $start = true;
      foreach ($data as $key => $value) {
        if (!$start) {
          $fields .= ', ';
          $values .= ', ';
        }
        else $start = false;
        $fields .= "`$key`";
        $values .= "'$value'";
      }
      $SQL = sprintf("INSERT INTO `%s` (%s) VALUES (%s)",
          $this->getTableName(), $fields, $values);
      $database->query($SQL);
      if ($database->is_error()) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
        return false;
      }
      $message .= $this->lang->translate('<p>The configuration record <b>{{ name }}</b> was successfull inserted.</p>',
          array('name' => $data[self::FIELD_NAME]));
    }
    $this->setMessage($message);
    return true;
  } // setValue()

  public function updateValue($name, $module_directory, $new_value) {
    global $database;

    $SQL = "SELECT `cfg_id` FROM `".$this->getTableName()."` WHERE `cfg_name`='$name' AND `cfg_module_directory`='$module_directory'";
    if (false === ($id = $database->get_one($SQL, MYSQL_ASSOC))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('The configuration key <b>{{ name }}</b> for the module directory <b>{{ directory }}</b> does not exists!',
              array('name' => $name, 'directory' => $module_directory))));
      return false;
    }
    // create a data array
    $data = array(
        self::FIELD_NAME => $name,
        self::FIELD_MODULE_DIRECTORY => $module_directory,
        self::FIELD_VALUE => $new_value
        );
    // exec the regular set value function
    return $this->setValue($data, $id);
  } // updateValue()

  /**
   * Master function to get values from the settings by the desired name for the
   * specified module directory.
   * This function returns the values in the specified TYPE (i.e. a float value
   * as FLOAT) a.s.o.
   *
   * @param string $name of the setting
   * @param string $module_directory
   * @return mixed value and NULL on error
   */
  public function getValue($name, $module_directory) {
    global $database;
    $SQL = "SELECT `cfg_value`, `cfg_type` FROM `".$this->getTableName()."` WHERE ".
        "`cfg_name`='$name' AND `cfg_module_directory`='$module_directory'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return null;
    }
    if ($query->numRows() < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('The configuration key <b>{{ name }}</b> for the module directory <b>{{ directory }}</b> does not exists!',
              array('name' => $name, 'directory' => $module_directory))));
      return null;
    }
    $setting = $query->fetchRow(MYSQL_ASSOC);
    if ((null == ($value = $this->formatValue($setting[self::FIELD_VALUE], $setting[self::FIELD_TYPE], self::FORMAT_PROCESS)))
        && $this->isError()) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
      return null;
    }
    return $value;
  } // getValue()

  /**
   * Transforms XML errors to an regular error string
   *
   * @param string $method the calling method
   * @param string $line the line number of the source code
   */
  protected function setXMLerror($method, $line) {
    $err_str = '<p>Failed loading XML<br />';
    foreach (libxml_get_errors() as $error) {
      $err_str .= sprintf("[%s at %d:%d] %s<br />", $this->XMLerrorLevel2string($error->level), $error->line, $error->column, $error->message);
    }
    $err_str .= "</p>";
    $this->setError(sprintf('[%s - %s] %s', $method, $line, $err_str));
  } // setXMLerror()

  /** Prettifies an XML string into a human-readable and indented work of art
   *
   *  @param string $xml The XML as a string
   *  @param boolean $html_output True if the output should be escaped (for use in HTML)
   */
  protected static function xmlPrettyPrint($xml, $html_output = false) {
    $xml_obj = new SimpleXMLElement($xml);
    $tab_width = 2; // tabulator width
    $indent = 0; // current indentation level
    $pretty = array();

    // get an array containing each XML element
    $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

    // shift off opening XML tag if present
    if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
      $pretty[] = array_shift($xml);
    }

    foreach ($xml as $el) {
      if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
        // opening tag, increase indent
        $pretty[] = str_repeat(' ', $indent).$el;
        $indent += $tab_width;
      }
      else {
        if (preg_match('/^<\/.+>$/', $el)) {
          $indent -= $tab_width; // closing tag, decrease indent
        }
        if ($indent < 0) {
          $indent += $tab_width;
        }
        $pretty[] = str_repeat(' ', $indent).$el;
      }
    }
    $xml = implode("\n", $pretty);
    return ($html_output) ? htmlentities($xml) : $xml;
  } // xmlPrettyPrint()

  /**
   * Process a XML file for the specified module with options
   *
   * @param string $path the path to the XML file
   * @param string $module_directory if not null (default) import only the settings for the named module
   * @param boolean $reset_values if true import all settings with the default default values of the XML file
   * @return boolean true on success, return status as $message
   */
  public function readXMLfile($path, $module_directory=null, $reset_values=false) {
    if (!file_exists($path)) {
      $this->setError(sprintf('[%s - %s] %s', $this->lang->translate('The XML file <b>{{ file }}</b> does not exist!',
          array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    // catch the XML errors
    libxml_use_internal_errors(true);
    // create XML iterator object
    try {
      $xmlIterator = new SimpleXMLIterator($path, 0, true);
    } catch (Exception $e) {
      $this->setXMLerror(__METHOD__, $e->getLine(), - 1);
      return false;
    }
    $message = '';
    if ($xmlIterator->getName() != 'xmcfg') {
      // no valid XMCFG file!
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('The file <b>{{ file }}</b> is no valid manufakturConfig file, missing the XML element <b>xmcfg</b>.',
              array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    if (!isset($xmlIterator->attributes()->version)) {
      // missing the version information for the XMCFG file!
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('The file <b>{{ file }}</b> is no valid manufakturConfig file, missing the <b>version attribute</b> in the XML element <b>xmcfg</b>.',
              array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    $mcfg_version = (string) $xmlIterator->attributes()->version;

    for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
      // we need only childs of type "module" ...
      if ($xmlIterator->key() != 'module')
        continue;
      // ok - get the attributes for the module
      $module = $xmlIterator->current()->attributes();
      if (!isset($module->name) || !isset($module->directory) || !isset($module->group) || !isset($module->date)) {
        // missing module attributes
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate('The file <b>{{ file }}</b> is no valid manufakturConfig file, missing one or more <b>module attribute</b> in the XML element <b>module</b>, needed are <i>name, directory, group and date</i>.',
                array('file' => substr($path, strlen(LEPTON_PATH))))));
        return false;
      }

      if (!is_null($module_directory) && ($module->directory != $module_directory)) {
        // process only settings for the desired module
        continue;
      }

      foreach ($xmlIterator->getChildren() as $setting) {
        // we need only childs of type 'setting'
        if ($setting->getName() != 'setting') continue;
        $data = array();
        $attr = $setting->attributes();
        if (!isset($attr->name) || !isset($attr->usage) || ! isset($attr->update)) {
          // no valid attributes for this setting
          $message .= $this->lang->translate('<p>Missing attributes for the setting <b>{{ name }}</b>, needed are <i>name, usage</i> and <i>update</i>, skipped entry!</p>',
              array('name' => isset($attr->name) ? $attr->name : '-unknown-'));
          continue;
        }
        $data[self::FIELD_VERSION] = $mcfg_version;
        $data[self::FIELD_MODULE_NAME] = (string) $module->name;
        $data[self::FIELD_MODULE_DIRECTORY] = (string) $module->directory;
        $data[self::FIELD_MODULE_GROUP] = (string) $module->group;
        $data[self::FIELD_NAME] = (string) $attr->name;
        $data[self::FIELD_USAGE] = strtoupper($attr->usage);
        $data[self::FIELD_UPDATE] = strtoupper($attr->update);
        $old_level = error_reporting(0);
        if (!isset($setting->value->attributes()->type)) {
          // missing the type for the value
          $message .= $this->lang->translate('<p>Missing the attribute <b>type</b> for the value of <b>{{ name }}</b>, skipped entry!</p>',
              array('name' => $attr->name));
          continue;
        }
        error_reporting($old_level);
        $data[self::FIELD_TYPE] = strtoupper($setting->value->attributes()->type);
        $data[self::FIELD_VALUE] = (string) $setting->value;
        $data[self::FIELD_VALUE_PAGE] = 'NONE';
        $data[self::FIELD_VALUE_SET] = 'NONE';
        $data[self::FIELD_VALUE_SET_ORDER] = 0;
        $data[self::FIELD_HINT] = '';
        $data[self::FIELD_LABEL] = '';
        if (strtoupper($attr->usage) != self::USAGE_HIDDEN) {
          // need additional informations about presenting the value in the dialog
          if (!isset($setting->dialog)) {
            // missing tag 'dialog'
            $message .= $this->lang->translate('<p>Missing the tag <b>dialog</b> for <b>{{ name }}</b>, skipped entry!</p>',
                array('name' => $attr->name));
            continue;
          }
          if (!isset($setting->dialog->attributes()->set) || !isset($setting->dialog->attributes()->page)) {
            $message .= $this->lang->translate('<p>Missing attributes for the <b>dialog</b> tag, needed are <i>set</i> and <i>page</i>, skipped entry!</p>');
            continue;
          }
          $data[self::FIELD_VALUE_PAGE] = (string) $setting->dialog->attributes()->page;
          $data[self::FIELD_VALUE_SET] = (string) $setting->dialog->attributes()->set;
          $data[self::FIELD_VALUE_SET_ORDER] = isset($setting->dialog->attributes()->set_order) ? (int) $setting->dialog->attributes()->set_order : 0;
          $data[self::FIELD_HINT] = (string) $setting->dialog->hint;
          $data[self::FIELD_LABEL] = (string) $setting->dialog->label;
        }
        if ($reset_values && (!isset($data[self::FIELD_UPDATE]) ||
            isset($data[self::FIELD_UPDATE]) && ($data[self::FIELD_UPDATE] != self::UPDATE_DELETE))) {
          // set settings to default values
          $data[self::FIELD_UPDATE] = self::UPDATE_OVERWRITE;
        }
        if (!$this->setValue($data, null)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
          return false;
        }
        $message .= $this->getMessage();
      }
    }
    if (empty($message)) {
      $message = $this->lang->translate('There were no settings to process!');
    }
    $this->setMessage($message);
    return true;
  } // readXMLfile()


  /**
   * Save the settings for the desired module as XML file in the specified $path.
   * If $module_directory is NULL the function will save the settings for all
   * modules.
   *
   * @param string $path to the XML file to save
   * @param string|null $module_directory if not NULL only the specified module will be saved
   * @return boolean result of operation
   */
  public function writeXMLfile($path, $module_directory=null) {
    global $database;

    // select all available modules in alphabetic order
    $SQL = "SELECT DISTINCT `cfg_module_directory`, `cfg_module_name`, `cfg_module_group` FROM `".
      TABLE_PREFIX."mod_manufaktur_config` ORDER BY `cfg_module_directory` ASC";
    if (null == ($query_module = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query_module->numRows() > 0) {
      $count = 0;
      // set the XML file header
      $xml_header = sprintf('<?xml version="1.0" encoding="UTF-8"?><xmcfg version="%01.2f"></xmcfg>', $this->getVersion());
      // create XML object
      $xml = new SimpleXMLElement($xml_header);

      while (false !== ($modules = $query_module->fetchRow(MYSQL_ASSOC))) {
        // walk through the settings for each module
        if (is_null($module_directory) || (!is_null($module_directory) &&
            ($module_directory == $modules[self::FIELD_MODULE_DIRECTORY]))) {
          $module = $xml->addChild('module');
          $module->addAttribute('name', $modules[self::FIELD_MODULE_NAME]);
          $module->addAttribute('directory', $modules[self::FIELD_MODULE_DIRECTORY]);
          $module->addAttribute('group', $modules[self::FIELD_MODULE_GROUP]);
          $module->addAttribute('date', date('Y-m-d H:i:s'));
          // select all setting records for this module
          $SQL = "SELECT * FROM `".TABLE_PREFIX."mod_manufaktur_config` WHERE `cfg_module_directory`='".$modules[self::FIELD_MODULE_DIRECTORY]."' ORDER BY `cfg_name` ASC";
          if (null == ($query = $database->query($SQL))) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return false;
          }

          while (false !== ($settings = $query->fetchRow(MYSQL_ASSOC))) {
            $setting = $module->addChild('setting');
            $setting->addAttribute('name', $settings[self::FIELD_NAME]);
            $setting->addAttribute('usage', $settings[self::FIELD_USAGE]);
            $setting->addAttribute('update', $settings[self::FIELD_UPDATE]);
            $value = $setting->addChild('value', $settings[self::FIELD_VALUE]);
            $value->addAttribute('type', $settings[self::FIELD_TYPE]);
            if ($settings[self::FIELD_USAGE] != self::USAGE_HIDDEN) {
              // this value is also displayed in the config dialog
              $dialog = $setting->addChild('dialog');
              $dialog->addAttribute('set', $settings[self::FIELD_VALUE_SET]);
              $dialog->addAttribute('page', $settings[self::FIELD_VALUE_PAGE]);
              $label = $dialog->addChild('label', $settings[self::FIELD_LABEL]);
              $hint = $dialog->addChild('hint', $settings[self::FIELD_HINT]);
            }
            $count++;
          }
        }
      }
      // save XML object as string
      $result = $xml->asXML();
      // prettyfy the output
      $result = $this->xmlPrettyPrint($result);
      // save the XML file
      if (!file_put_contents($path, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error writing the XML file {{ file }}.',
            array('file' => substr($path, strlen(LEPTON_PATH))))));
        return false;
      }
      // ready
      $this->setMessage($this->lang->translate('<p>Saved <b>{{ count }}</b> configuration records as XML file at <b>{{ file }}</b></p>',
          array('count' => $count, 'file' => substr($path, strlen(LEPTON_PATH)))));
    }
    else {
      // no entries found!
      $this->setMessage($this->lang->translate('<p>There are no configuration records to save as XML file.</p>'));
      return true;
    }
    return true;
  } // saveXMLfile()

} // class manufakturConfig
