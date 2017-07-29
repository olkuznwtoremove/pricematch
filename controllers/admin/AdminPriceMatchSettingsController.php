<?php

class AdminPriceMatchSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        Tools::redirectAdmin($this->module->getConfigurationUrl());
    }
}
