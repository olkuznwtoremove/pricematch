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

require_once dirname(__FILE__).'/models/MatchRequestModel.php';
class PriceMatch extends Module
{
    protected $log_file = '';
    protected $mails_dir = '';

    public function __construct()
    {
        $this->name = 'pricematch';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Odevme';
        $this->controllers = array('match');
        parent::__construct();

        $this->bootstrap = true;
        $this->displayName = $this->l('Price Match');
        $this->description = $this->l('Product Price Match Module.');

        $this->log_file = _PS_ROOT_DIR_.'/log/pricematch.log';
        $this->mails_dir = $this->local_path.'/mails/';
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module?');
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        $tableSql = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.MatchRequestModel::$definition['table']."`(
                `id_odev_price_match` int(11) NOT NULL AUTO_INCREMENT,
                `id_product` int(11) NOT NULL,
                `id_shop` int(11) NOT NULL,
                `id_customer` int(11) NULL,  
                `customer_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
                `customer_email` varchar(128) NOT NULL,
                `customer_phone` varchar(32) NULL,
                `competitor_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `competitor_url` varchar(255) NOT NULL,
                `comment` text NOT NULL,
                `date_add` datetime NOT NULL,
                `state` enum('". implode("','", MatchRequestModel::getStatuses(true))."') NOT NULL DEFAULT 'processing',
                `active` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id_odev_price_match`),
                KEY `id_product` (`id_product`)
            ) 
            ENGINE=InnoDB 
            DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ";

        return (
            $this->createAdminTabs() &&
            parent::install() &&
            Db::getInstance()->Execute($tableSql) &&
            Configuration::updateValue('ODEV_PRICEMATCH_SEND_ADMIN_EMAIL') &&
            Configuration::updateValue('ODEV_PRICEMATCH_SEND_CUSTOMER_EMAIL') &&
            Configuration::updateValue('ODEV_PRICEMATCH_DESCRIPTION') &&
            $this->registerHook('header') &&
            $this->registerHook('displayProductButtons') &&
            $this->registerHook('displayFooter')
        );
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        $this->removeAdminTabs();
        return (
            Configuration::deleteByName('ODEV_PRICEMATCH_SEND_ADMIN_EMAIL') &&
            Configuration::deleteByName('ODEV_PRICEMATCH_SEND_CUSTOMER_EMAIL') &&
            Configuration::deleteByName('ODEV_PRICEMATCH_DESCRIPTION') &&
            DB::getInstance()->execute("DROP TABLE IF EXISTS `"._DB_PREFIX_.MatchRequestModel::$definition['table']."`") &&
            parent::uninstall()
        );
    }

    /**
     * Create module configuration pages
     *
     * @return string
     */
    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('save'.$this->name)) {
            $description = array();
            $value = '';
            foreach ($this->context->controller->getLanguages() as $language) {
                $value = trim(Tools::getValue('description_'.(int)$language['id_lang'], ''));
                if (!Validate::isCleanHtml($value)) {
                    $this->_errors[] = $this->l('Description can\'t contain any scripts!');
                } else {
                    $description[(int)$language['id_lang']] = $value;
                }
            }
            if (!count($this->_errors)) {
                Configuration::updateValue('ODEV_PRICEMATCH_DESCRIPTION', $description, true);
                Configuration::updateValue('ODEV_PRICEMATCH_SEND_ADMIN_EMAIL', (int)Tools::getValue('admin_email'));
                Configuration::updateValue('ODEV_PRICEMATCH_SEND_CUSTOMER_EMAIL', (int)Tools::getValue('customer_email'));
                Tools::redirectAdmin($this->getConfigurationUrl().'&conf=4');
            } else {
                $html .= $this->displayError(implode('</br>', $this->_errors));
            }
        }

        $helper = $this->initConfigurationForm();
        $helper->fields_value = array(
            'admin_email' => Configuration::get('ODEV_PRICEMATCH_SEND_ADMIN_EMAIL'),
            'customer_email' => Configuration::get('ODEV_PRICEMATCH_SEND_CUSTOMER_EMAIL'),
            'description' => Configuration::getInt('ODEV_PRICEMATCH_DESCRIPTION'),
        );
        return $html.$helper->generateForm($this->fields_form);
    }

    /**
     * Prepare pricematch popup template
     *
     * @return string
     */
    public function hookDisplayFooter()
    {
        // add pricematch popup
        if ($this->showPopup()) {
            $product = $this->context->controller->getProduct();
            $data = array(
                'pricematchUrl' => $this->context->link->getModuleLink('pricematch', 'match'),
                'id_product' => $product->id,
                'productName' => $product->name,
                'id_shop' => $this->context->shop->id,
                'id_customer' => $this->context->customer->id,
                'customer_name' => $this->context->customer->firstname,
                'customer_email' => $this->context->customer->email,
                'description' => Configuration::get('ODEV_PRICEMATCH_DESCRIPTION', $this->context->language->id),
            );
            $this->smarty->assign($data);
            return $this->display(__FILE__, 'pricematchpopup.tpl');
        }
        return '';
    }

    /**
     * Add module styles and scripts
     *
     * @param array $params
     */
    public function hookHeader($params = array())
    {
        // add module styles
        if ($this->showPopup()) {
            $this->context->controller->addCSS($this->_path.'views/css/pricematch.css', 'all');
            $this->context->controller->addJs($this->_path.'views/js/pricematch.js');
        }
    }

    /**
     * Add prepare pricematch buttons
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayProductButtons($params = array())
    {
        // add 'Show popup' button
        if ($this->showPopup()) {
            return $this->display(__FILE__, 'pricematchblock.tpl');
        }
        return '';
    }

    /**
     * Prepare module configuration url
     *
     * @return string
     */
    public function getConfigurationUrl()
    {
        return $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    }

    /**
     * Check should we show popup
     *
     * @return bool
     */
    private function showPopup()
    {
        return (isset($this->context->controller->php_self) && 'product' == $this->context->controller->php_self) && !Tools::getValue('content_only');
    }

    /**
     * Create tabs in admin panel
     *
     * @return bool
     */
    private function createAdminTabs()
    {
        $languages = Language::getLanguages();
        $tab = new Tab();
        $tab->class_name = 'AdminPriceMatchMain';
        $tab->module = $this->name;
        $tab->id_parent = 0;
        foreach ($languages as $language) {
            $tab->name[$language['id_lang']] = $this->l('Price match module');
        }
        $tab->save();
        $settingsTab = new Tab();
        $settingsTab->class_name = 'AdminPriceMatchSettings';
        $settingsTab->module = $this->name;
        $settingsTab->id_parent = $tab->id;
        $requestsTab = new Tab();
        $requestsTab->class_name = 'AdminPriceMatchRequests';
        $requestsTab->module = $this->name;
        $requestsTab->id_parent = $tab->id;
        foreach ($languages as $language) {
            $settingsTab->name[$language['id_lang']] = $this->l('Price match settings');
            $requestsTab->name[$language['id_lang']] = $this->l('Price match requests');
        }
        return $requestsTab->save() && $settingsTab->save();
    }

    /**
     *  Remove tabs from admin panel
     *
     *  @return bool
     */
    private function removeAdminTabs()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminPriceMatchMain');
        $id_settingsTab = (int)Tab::getIdFromClassName('AdminPriceMatchSettings');
        $id_requestsTab = (int)Tab::getIdFromClassName('AdminPriceMatchRequests');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
            $settingsTab = new Tab($id_settingsTab);
            $requestsTab = new Tab($id_requestsTab);
            return $settingsTab->delete() && $requestsTab->delete();
        }
        return true;
    }

    /**
     * Init configuration form
     *
     * @return HelperForm
     */
    private function initConfigurationForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Price macth settings'),
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description on pricematch form'),
                    'lang' => true,
                    'name' => 'description',
                    'cols' => 40,
                    'rows' => 10,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Send email for administrator'),
                    'name' => 'admin_email',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Send email for customer'),
                    'name' => 'customer_email',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'pricamatch';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach ($this->context->controller->getLanguages() as $language) {
            $helper->languages[] = array(
                'id_lang' => $language['id_lang'],
                'iso_code' => $language['iso_code'],
                'name' => $language['name'],
                'is_default' => $default_lang == $language['id_lang'] ? 1 : 0,
            );
        }
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'save'.$this->name;
        $helper->show_cancel_button = true;

        return $helper;
    }

    /**
     * Return path to mails folder
     *
     * @return string
     */
    public function getMailsDir()
    {
        return $this->mails_dir;
    }

    /**
     * Return path to log file
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->log_file;
    }
}
