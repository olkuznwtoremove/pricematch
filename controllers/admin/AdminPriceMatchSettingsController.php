<?php
/**
 *  @author    Olga Kuznetsova <olkuznw@gmail.com>
 *  @copyright odev.me 2017
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  @version   1.0.0
 *
 * Languages: EN
 * PS version: 1.6
 **/

class AdminPriceMatchSettingsController extends ModuleAdminController
{
    /**
     * AdminPriceMatchSettingsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        Tools::redirectAdmin($this->module->getConfigurationUrl());
    }
}
