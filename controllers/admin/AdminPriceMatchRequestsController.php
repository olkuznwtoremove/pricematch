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

require_once dirname(__FILE__).'/../../models/MatchRequestModel.php';
require_once dirname(__FILE__).'/../../pricematch.php';

class AdminPriceMatchRequestsController extends ModuleAdminController
{
    protected static $translationStatuses = array();

    /**
     * AdminPriceMatchRequestsController constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'odev_price_match';
        $this->identifier = 'id_odev_price_match';
        $this->className = 'MatchRequestModel';
        $this->lang = false;
        
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        
        parent::__construct();
        $this->fields_list = array(
            'name' => array(
                'title' => $this->l('Product'),
                'filter_key' => 'pl!name'
            ),
            'customer_name' => array(
                'title' => $this->l('Customer name')
            ),
            'customer_email' => array(
                'title' => $this->l('Customer email')
            ),
            'competitor_price' => array(
                'title' => $this->l('Competitor price'),
                'type' => 'price'
            ),
            'state' => array(
                'title' => $this->l('State'),
                'list' => $this->getTranslatedStatus(),
                'filter_key' => 'state',
                'type' => 'select',
                'callback' => 'displayTranslatedStatus',
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'align' => 'text-right',
                'type' => 'datetime',
            ),
        );
        $this->_select = ' pl.name';
        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                ON pl.`id_product` = a.`id_product` AND pl.`id_lang` = '.$this->context->language->id.'
                    AND pl.`id_shop` = '.$this->context->shop->id.'
        ';
        $this->_where = ' AND a.`active` = 1';
        $this->_orderBy = 'date_add';
        $this->_orderWay = 'desc';
    }

    /**
     * Set media
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addJqueryUI('ui.datepicker');
    }

    /**
     * Precess delete
     *
     * @return MatchRequestModel
     */
    public function processDelete()
    {
        $object = $this->loadObject();
        
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            $object->active = 0;
            $object->save();
        }
        return $object;
    }
    
    /**
     * AdminController::renderForm() override
     *
     * @see AdminController::renderForm()
     */
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Update request state'),
                'icon' => 'icon-info-sign'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Product'),
                    'name' => 'product',
                    'disabled' => 'disabled',
                    'col' => '4',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Customer name'),
                    'name' => 'customer_name',
                    'disabled' => 'disabled',
                    'col' => '4',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Customer email'),
                    'name' => 'customer_email',
                    'disabled' => 'disabled',
                    'col' => '4',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Customer phone'),
                    'name' => 'customer_phone',
                    'disabled' => 'disabled',
                    'col' => '4',
                ),
                
                array(
                    'type' => 'text',
                    'label' => $this->l('Competitor price'),
                    'name' => 'competitor_price',
                    'disabled' => 'disabled',
                    'string_format' => '%.2f',
                    'col' => '4',
                ),
                array(
                    'type' => 'href',
                    'label' => $this->l('Competitor url'),
                    'name' => 'competitor_url',
                ),
                array(
                    'type' => 'comment',
                    'label' => $this->l('Comment'),
                    'name' => 'comment',
                    'disabled' => 'disabled',
                    'col' => '4',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Date'),
                    'name' => 'date_add',
                    'disabled' => 'disabled',
                    'col' => '4',
                ),
                array(
                    'type' => 'dropdown',
                    'label' => $this->l('State'),
                    'name' => 'state',
                    'options' => $this->getTranslatedStatus(),
                )
            )
        );

        $product = new Product($this->object->id_product, false, $this->context->language->id);
        $this->fields_value = array();
        $this->fields_value['product'] = $product->name;
        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
        );
        return parent::renderForm();
    }

    /**
     * Precess toolbar
     */
    public function initToolbar()
    {
        parent::initToolbar();
        if (isset($this->toolbar_btn['new'])) {
            unset($this->toolbar_btn['new']);
        }
    }

    /**
     * Bulk delete process
     *
     * @return bool
     */
    protected function processBulkDelete()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $result = true;
            foreach ($this->boxes as $id) {
                $to_delete = new $this->className($id);
                $to_delete->active = 0;
                if (!$to_delete->update()) {
                    $result = false;
                    $this->errors[] = sprintf(Tools::displayError('Can\'t delete #%d'), $id);
                }
            }
            if ($result) {
                $this->redirect_after = self::$currentIndex.'&conf=2&token='.$this->token;
            } else {
                $this->errors[] = Tools::displayError('An error occurred while deleting this selection.');
            }
        } else {
            $this->errors[] = Tools::displayError('You must select at least one element to delete.');
        }
        return isset($result);
    }

    /**
     * After update method
     * send customer, admin emails
     *
     * @param $object
     * @return bool
     */
    public function afterUpdate($object)
    {
        if (Configuration::get('ODEV_PRICEMATCH_SEND_CUSTOMER_EMAIL')) {
            $module = new PriceMatch();
            switch ($object->state) {
                case MatchRequestModel::STATE_ACCEPTED:
                    $mailSubject = Mail::l('You price match request was accepted.', $this->context->language->id);
                    $mailTemplate = 'pricematche_request_customer_accepted';
                    break;

                case MatchRequestModel::STATE_REJECTED:
                    $mailSubject = Mail::l('You price match request was rejected.', $this->context->language->id);
                    $mailTemplate = 'pricematche_request_customer_rejected';
                    break;
                default:
                    return true;
            }
            if (!Mail::Send(
                $this->context->language->id,
                $mailTemplate,
                $mailSubject,
                array(
                    '{customer}'    => $object->customer_name,
                    '{id_request}'    => $object->id,
                ),
                $object->customer_email,
                $object->customer_name,
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                $module->getMailsDir(),
                false,
                $this->context->shop->id
            )) {
                $logger = new FileLogger();
                $logger->setFilename($module->getLogFile());
                $logger->logError(sprintf(Tools::displayError('Price match request #%s: error while sending customer\'s email.'), $object->id));
            }
        }
        return true;
    }

    /**
     * Get translated statuses
     *
     * @param bool $useKey
     * @return array
     */
    public function getTranslatedStatus($useKey = false)
    {
        if (empty(self::$translationStatuses)) {
            $translations = array(
                $this->l('Processing'),
                $this->l('Accepted'),
                $this->l('Rejected'),
            );
            $statuses = MatchRequestModel::getStatuses($useKey);
            self::$translationStatuses = array_combine(array_keys($statuses), $translations);
        }
        return self::$translationStatuses;
    }

    /**
     * Show translated label state
     *
     * @param $value
     * @param $row
     * @return string
     */
    public function displayTranslatedStatus($value, $row)
    {
        $statuses = $this->getTranslatedStatus();
        return $statuses[$value];
    }
}
