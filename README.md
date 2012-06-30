### manufakturConfig

**manufakturConfig** is a library for the Content Management Systems [WebsiteBaker] [1] and [LEPTON CMS] [2]. It offers addons an easy to use configuration and setting management and a "just call it" dialog. 

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1] _or_ using [LEPTON CMS] [2]
* [Dwoo] [6] installed

#### Installation

* download the actual [manufakturConfig] [3] installation archive
* in CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"

#### First Steps

**manufakturConfig** is an easy to use configuration tool for your own WebsiteBaker and LEPTON add-ons.

Create a XML file to define and explain your settings, sample:

    <?xml version="1.0" encoding="UTF-8"?>
    <xmcfg version="0.10">
      <module name="extendedWYSIWYG" directory="wysiwyg" group="" date="2012-06-25 19:06:03">
        <setting name="cfgShowInfoBanner" usage="HIDDEN" update="INSERT">
          <value type="BOOLEAN">0</value>
        </setting>
        <setting name="cfgUpdateModifiedPage" usage="REGULAR" update="INSERT">
          <value type="BOOLEAN">1</value>
          <dialog set="" page="Settings">
            <label>Update page information</label>
            <hint>A change of a WYSIWYG section will update the last modified field of the page (recommend!)</hint>
          </dialog>
        </setting>
        <setting name="cfgArchiveIdSelectLimit" usage="REGULAR" update="INSERT">
          <value type="INTEGER">10</value>
          <dialog set="" page="Settings">
            <label>max. Archives in Selection</label>
            <hint>The maximum number of archives that will be shown in the selection list</hint>
          </dialog>
        </setting>
        <setting name="cfgCreateArchiveFiles" usage="REGULAR" update="INSERT">
          <value type="BOOLEAN">0</value>
          <dialog set="" page="Settings">
            <label>Create Archive Files</label>
            <hint>If activated extendedWYSIWYG will create a protected directory in the /MEDIA path and create a HTML page of each content that get the status BACKUP. The embedded images will be also saved.</hint>
          </dialog>
        </setting>    
      </module>
    </xmcfg>
        
* use the tag `module` to define the `name`, `directory` and - if needed - the `group` of modules which will use this settings (leave it empty if not needed).
* use the tag `setting` to define the `name` of the setting value
* with `usage` in `setting` you can specify if the setting is shown in the dialog = `REGULAR` or if this setting is `HIDDEN`
* `update` can be `INSERT` (as new setting), `OVERWRITE` (existing setting with changed values) or `DELETE` (remove an existing setting)
* the `value` tag `type` can be `ARRAY`, `BOOLEAN`, `EMAIL`, `FLOAT`, `INTEGER`, `LIST`, `STRING` or `TEXT`
* if the `usage` is `REGULAR` this setting will be shown in the configuration `dialog`, 
* you can define `set`'s (group of values), one or more `page`'s
* define a `label` for the setting
* and define a `hint` for each setting.

Save this XML file in your add-on /modules directory. In your install.php or upgrade.php cause **manufakturConfig** to read this file:

    // initialize the configuration
    require_once LEPTON_PATH.'/modules/manufaktur_config/library.php';
    $config = new manufakturConfig();
    if (!$config->readXMLfile(LEPTON_PATH.'/modules/wysiwyg/config/extendedWYSIWYG.xml', 'wysiwyg', true)) {
      $admin->print_error($config->getError());
    }
        
Within your add-on you can access the settings:

    $config = new manufakturConfig();
    self::$cfg_updateModifiedPage = $config->getValue('cfgUpdateModifiedPage', 'wysiwyg');
    self::$cfg_archiveIdSelectLimit = $config->getValue('cfgArchiveIdSelectLimit', 'wysiwyg');
    
**manufakturConfig** will return the values in the specified format.

You can use the **manufakturConfig** settings dialog also very easy:

    // exec manufakturConfig
    $dialog = new manufakturConfigDialog('wysiwyg', 'extendedWYSIWYG', $link, $abort);
    return $dialog->action();
    
this will return a complete settings dialog as you have specified it in your XML. The `$link` must be the URL to call the dialog within your add-on, `$abort` can be any URL to which the user should be redirected if he aborts the settings dialog.

That's all! Try it, it's easy - you will find an working example in the add-on [extendedWYSIWYG] [7].

Please visit the [phpManufaktur] [5] to get more informations about **manufakturConfig** and join the [Addons Support Group] [4].

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://addons.phpmanufaktur.de/download.php?file=manufakturConfig
[4]: https://phpmanufaktur.de/support
[5]: https://addons.phpmanufaktur.de/manufakturConfig
[6]: https://addons.phpmanufaktur.de/download.php?file=Dwoo
[7]: https://addons.phpmanufaktur.de/extendedWYSIWYG
