<?php

use Bitrix\Main\ModuleManager;

class Magnifico_Phinx extends \CModule
{
    public $MODULE_ID = 'magnifico.phinx';

    public $MODULE_VERSION = '1.0.0';

    public $MODULE_VERSION_DATE = '2014-06-19 17:49:00';

    public $MODULE_NAME = 'Database migrations';

    public $MODULE_DESCRIPTION = 'Integration of phinx and bitrix';

    public $PARTNER_NAME = 'Magnifico';

    public $PARTNER_URI = 'https://magnifico.pro';

    public function doInstall()
    {
        ModuleManager::registerModule('magnifico.phinx');
    }

    public function doUninstall()
    {
        ModuleManager::unRegisterModule('magnifico.phinx');
    }
}
