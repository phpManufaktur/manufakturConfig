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
if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

if (!class_exists('manufaktur_I18n'))
  require_once LEPTON_PATH.'/modules/manufaktur_i18n/library.php';
global $lang;
if (!is_object($lang))
  $lang = new manufaktur_I18n('manufaktur_config', LANGUAGE);

class dbManufakturConfig {

  const FIELD_ID = 'cfg_id';
  const FIELD_NAME = 'cfg_name';
  const FIELD_TYPE = 'cfg_type';
  const FIELD_USAGE = 'cfg_usage';
  const FIELD_VALUE = 'cfg_value';
  const FIELD_VALUE_SET = 'cfg_value_set';
  const FIELD_VALUE_GROUP = 'cfg_value_group';
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

  const USAGE_REGULAR = 'REGULAR';
  const USAGE_HIDDEN = 'HIDDEN';
  const USAGE_READONLY = 'READONLY';

  const UPDATE_INSERT = 'INSERT';
  const UPDATE_DELETE = 'DELETE';
  const UPDATE_OVERWRITE = 'OVERWRITE';

  private $table_name = null;

  private $message = '';
  private $error = '';
  private $module_name = null;

  protected $lang = NULL;

  private $field_array = array(
      self::FIELD_ID => -1,
      self::FIELD_NAME => '',
      self::FIELD_TYPE => self::TYPE_STRING,
      self::FIELD_USAGE => self::USAGE_REGULAR,
      self::FIELD_VALUE => '',
      self::FIELD_VALUE_SET => 'NONE',
      self::FIELD_VALUE_GROUP => 'NONE',
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

  public function __construct($module_name = null) {
    global $lang;
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->lang = $lang;
    $this->table_name = TABLE_PREFIX.'mod_manufaktur_config';
    $this->module_directory = $module_directory;
  } // __construct()

  public function createTable() {
    global $database;
    $SQL = "CREATE TABLE IF NOT EXISTS `".$this->getTableName()."` ( ".
      "`cfg_id` INT(11) NOT NULL AUTO_INCREMENT, ".
      "`cfg_name` VARCHAR(64) NOT NULL DEFAULT '', ".
      "`cfg_type` ENUM('ARRAY','BOOLEAN','EMAIL','FLOAT','INTEGER','LIST','STRING') NOT NULL DEFAULT 'STRING', ".
      "`cfg_usage`ENUM('REGULAR','HIDDEN','READONLY') NOT NULL DEFAULT 'REGULAR', ".
      "`cfg_value` TEXT, ".
      "`cfg_value_set` VARCHAR(64) NOT NULL DEFAULT 'NONE', ".
      "`cfg_value_group` VARCHAR(64) NOT NULL DEFAULT 'NONE', ".
      "`cfg_label` VARCHAR(64) NOT NULL DEFAULT '- undefined -', ".
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
   * @param unknown_type $table_name
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

  public function setModuleDirectory($module_directory) {
    $this->module_directory = $module_directory;
  } // setModuleDirectory()

  public function getModuleDirectory() {
    return $this->module_directory;
  } // getModuleDirectory()

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

  public static function unsanitize($item) {
    $item =  stripcslashes($item);
    $item = str_replace("&#039;", "'", $item);
    $item = str_replace("&gt;", ">", $item);
    $item = str_replace("&quot;", "\"", $item);
    $item = str_replace("&lt;", "<", $item);
    return $item;
  } // unsanitize()

  /**
   * Count the Groups for the desired module. Each group can be displayed by
   * the config dialog in a separate tabulator.
   * if cfgGroupOrder exists for the $module_directory the function will return
   * the groups in order of cfgGroupOrder
   *
   * @param string $module_directory
   * @return boolean|number
   */
  public function countGroups($module_directory, &$groups=array()) {
    global $database;

    // first check if a cfgGroupOrder exists!
    $SQL = "SELECT `cfg_value` FROM `".$this->getTableName()."` WHERE ".
      "`cfg_module_directory`='$module_directory' AND `cfg_name`='cfgGroupOrder'";
    $order = $database->get_one($SQL, MYSQL_ASSOC);
    // get the groups
    $SQL = "SELECT DISTINCT `cfg_value_group` FROM `".$this->getTableName()."` WHERE ".
      "`cfg_module_directory`='$module_directory' AND `cfg_usage`='REGULAR' AND ".
      "`cfg_value_group`!='NONE' ORDER BY `cfg_value_group` ASC";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    $groups = array();
    if (!is_null($order)) {
      $groups = explode(',', $order);
    }
    else {
      while (false !== ($group = $query->fetchRow(MYSQL_ASSOC))) {
        $groups[] = $group[self::FIELD_VALUE_GROUP];
      }
    }
    return $query->numRows();
  } // countGroups()

  /**
   * Count the sets for the desired module. Each set can be displayed by
   * the config dialog a separated fieldset.
   *
   * @param string $module_directory
   * @return boolean|number
   */
  public function countSets($module_directory) { echo '??';
    global $database;
    $SQL = "SELECT DISTINCT `cfg_value_set` FROM `".$this->getTableName()."` WHERE ".
      "`cfg_module_directory`='$module_directory' AND `cfg_usage`='REGULAR' AND `cfg_value_set`!='NONE'";
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return $query->numRows();
  } // countSets()

  public function getSettingsForModule($module_directory, &$settings=array(), $group=null, $group_by_sets=false) {
    global $database;

    if (($group == null) && ($group_by_sets == true)) {
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE `cfg_module_directory`='$module_directory' AND ".
          "`cfg_usage`='REGULAR' ORDER BY `cfg_value_set` ASC, `cfg_name` ASC";
    }
    elseif ((strlen($group) > 0) && ($group_by_sets == false)) {
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE `cfg_module_directory`='$module_directory' AND ".
          "`cfg_usage`='REGULAR' AND `cfg_value_group`='$group' ORDER BY `cfg_name` ASC";
    }
    elseif ((strlen($group) > 0) && ($group_by_sets == true)) {
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE `cfg_module_directory`='$module_directory' AND ".
          "`cfg_usage`='REGULAR' AND `cfg_value_group`='$group' ORDER BY `cfg_value_set` ASC, `cfg_name` ASC";
    }
    else {
      // $group == null && $group_by_sets == false
      $SQL = "SELECT * FROM `".$this->getTableName()."` WHERE `cfg_module_directory`='$module_directory' AND ".
          "`cfg_usage`='REGULAR' ORDER BY `cfg_name` ASC";
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
  } // getConfigValuesForModule()

  protected function checkValue($type, &$value, &$message='') {
    $message = '';
    return true;
  } // checkValue()

  public function setValue($data, $id = null, $check_values=true) {
    global $database;

    $message = '';

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
            $this->lang->I18n('The configuration record with the <b>ID {{ id }}</b> does not exist!',
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
            $this->lang->I18n('The configuration record is not valid, a <b>name</b> and the <b>module name</b> or the <b>module directory</b> is needed for identify')));
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
            $this->lang->I18n('Missing the <b>value</b> for the configuration record with the <b>ID {{ id }}</b>.',
                array('id', $id))));
        return false;
      }
      $changed = false;
      $type = (isset($data[self::FIELD_TYPE])) ? $data[self::FIELD_TYPE] : $old_data[self::FIELD_TYPE];
      $msg = '';
      if ($check_values) $this->checkValue($type, $data[self::FIELD_VALUE], $msg);
      $message .= $msg;

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
        $message .= $this->lang->I18n('<p>The configuration record <b>{{ name }}</b> was successfull deleted.</p>',
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
        $message .= $this->lang->I18n('<p>The configuration record <b>{{ name }}</b> was successfull updated.</p>',
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
              $this->lang->I18n('At minimun the fields NAME, TYPE and VALUE must set for configuration records of type HIDDEN.')));
          return false;
        }
        $msg = '';
        if ($check_values) $this->checkValue($data[self::FIELD_TYPE], $data[self::FIELD_VALUE], $msg);
        $message .= $msg;
        $data[self::FIELD_HINT] = '';
        $data[self::FIELD_LABEL] = '';
      }
      else {
        // regular record - check all fields
        foreach ($this->must_fields as $must) {
          if (!isset($data[$must]) || empty($data[$must])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                $this->lang->I18n('The field <b>{{ field }}</b> must be defined!', array('field' => $must))));
            return false;
          }
        }
        $msg = '';
        if ($check_values) $this->checkValue($data[self::FIELD_TYPE], $data[self::FIELD_VALUE], $msg);
        $message .= $msg;
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
      $message .= $this->lang->I18n('<p>The configuration record <b>{{ name }}</b> was successfull inserted.</p>',
          array('name' => $data[self::FIELD_NAME]));
    }
    $this->setMessage($message);
    return true;
  } // setValue()

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

  public function readXMLfile($path) {
    if (!file_exists($path)) {
      $this->setError(sprintf('[%s - %s] %s', $this->lang->I18n('The XML file <b>{{ file }}</b> does not exist!',
          array('file' => substr($path, strlen(LEPTON_PATH))))));
      return false;
    }
    // catch the XML errors
    libxml_use_internal_errors(true);
    // create XML iterator object
    if (false === ($xmlIterator = new SimpleXMLIterator($path, 0, true))) {
      $this->setXMLerror(__METHOD__, __LINE__ - 1);
      return false;
    }
    $message = '';
    // walk through the settings
    for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
      // we need only childs of type "setting" ...
      if ($xmlIterator->key() != 'setting')
        continue;
      $data = array();
      foreach ($xmlIterator->getChildren() as $name => $value) {
        $data[$name] = (string) $value;
      }
      if (!$this->setValue($data, null, false)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
        return false;
      }
      $message .= $this->getMessage();
    }
    $this->setMessage($message);
    return true;
  }

  public function saveXMLfile($path) {
    global $database;

    $SQL = sprintf("SELECT * FROM `%s`", $this->getTableName());
    if (null == ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query->numRows() > 0) {
      // set the XML file header
      $xml_header = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><configuration></configuration>';
      // create XML object
      $xml = new SimpleXMLElement($xml_header);
      // loop through the records
      while (false !== ($data = $query->fetchRow(MYSQL_ASSOC))) {
        $setting = $xml->addChild('setting');
        foreach ($data as $key => $value) {
          if ($key == self::FIELD_ID) continue;
          $setting->addChild($key, $value);
        }
      }
      // save XML object as string
      $result = $xml->asXML();
      // prettyfy the output
      $result = $this->xmlPrettyPrint($result);
      // save the XML file
      if (!file_put_contents($path, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->I18n('Error writing the XML file {{ file }}.',
            array('file' => substr($path, strlen(LEPTON_PATH))))));
        return false;
      }
      // ready
      $this->setMessage($this->lang->I18n('<p>Saved <b>{{ count }}</b> configuration records as XML file at <b>{{ file }}</b></p>',
          array('count' => $query->numRows(), 'file' => substr($path, strlen(LEPTON_PATH)))));
    }
    else {
      // nothing to do
      $this->setMessage($this->lang->I18n('<p>There are no configuration records to save as XML file.</p>'));
    }
    return true;
  } // saveXMLfile()

} // class dbManufakturConfig
