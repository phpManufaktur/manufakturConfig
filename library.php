<?php

/**
 * manufakturConfig
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 - phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
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

require_once LEPTON_PATH.'/modules/manufaktur_config/class.config.php';

class manufakturConfig extends dbManufakturConfig {

  private $module_directory = null;

  public function __construct($module_directory=null) {
    parent::__construct();
    $this->module_directory = $module_directory;
  } // __construct()

  public function setModuleDirectory($module_directory) {
    $this->module_directory = $module_directory;
  } // setModuleDirectory()

  public function getModuleDirectory() {
    return $this->module_directory;
  } // getModuleDirectory()


} // class manufakturConfig


global $manufakturConfig;
if (!is_object($manufakturConfig)) $manufakturConfig = new manufakturConfig();