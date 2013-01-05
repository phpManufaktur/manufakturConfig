<?php

/**
 * manufakturConfig
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/manufakturConfig
 * @copyright 2012 - 2013 phpManufaktur by Ralf Hertsch
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

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

global $database;
global $admin;

if (defined('LEPTON_VERSION'))
  $database->prompt_on_error(false);

/**
 * Check if the specified $field in the $table exists
 *
 * @param string $table name without prefix
 * @param string $field the required field
 * @return boolean
 */
function fieldExists($table, $field) {
  global $database;
  global $admin;
  if (null === ($query = $database->query("DESCRIBE `".TABLE_PREFIX.$table."`")))
    $admin->print_error($database->get_error());
  while (false !== ($data = $query->fetchRow(MYSQL_ASSOC)))
    if ($data['Field'] == $field) return true;
  return false;
} // sqlFieldExists()

if (!fieldExists('mod_manufaktur_config', 'cfg_value_set_order')) {
  $SQL = "ALTER TABLE `".TABLE_PREFIX."mod_manufaktur_config` ADD `cfg_value_set_order` INT(11) NOT NULL DEFAULT '0' AFTER `cfg_value_set`";
  if (!$database->query($SQL))
    $admin->print_error($database->get_error());
}

// Release 0.17

@unlink(LEPTON_PATH.'/modules/manufaktur_config/templates/backend/body.lte');
@unlink(LEPTON_PATH.'/modules/manufaktur_config/templates/backend/config.lte');
@unlink(LEPTON_PATH.'/modules/manufaktur_config/templates/backend/error.lte');
@unlink(LEPTON_PATH.'/modules/manufaktur_config/templates/backend/load.xml.lte');
