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

$module_directory = 'manufaktur_config';
$module_name = 'manufakturConfig';
$module_function = (defined('LEPTON_VERSION')) ? 'library' : 'snippet';
$module_version = '0.16';
$module_status = 'STABLE';
$module_platform = '2.8';
$module_author = 'Ralf Hertsch - Berlin (Germany)';
$module_license = 'MIT license (MIT)';
$module_description = 'Configuration tool for WebsiteBaker and LEPTON CMS addons';
$module_home = 'https://addons.phpmanufaktur.de/manufakturConfig';
$module_guid = 'BAC7FC79-6BA3-41F4-AE94-2DB7D7B1A4B1';
