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

class PriceMatchMatchModuleFrontController extends ModuleFrontController
{
    /**
     * PriceMatchMatchModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->className = 'MatchRequestModel';
    }

    /**
     * Process pricematch request
     */
    public function postProcess()
    {
        $matchRequest = new $this->className();
        $matchRequest->copyFromPost();
        $errors = $matchRequest->validateController();
        $result = array(
            'hasError' => true,
            'messages' => Tools::displayError('Some error occurred.'),
        );
        if (empty($errors)) {
            if ($matchRequest->save()) {
                // send email for adim and customer
                $this->sendAdminEmail($matchRequest);
                $this->sendCustomerEmail($matchRequest);
                $result = array(
                    'hasError' => false,
                    'messages' => Tools::displayError('Thank you for request!'),
                );
            }
        } else {
            $result['messages'] = implode('<br/>', $result);
        }
        die(Tools::jsonEncode($result));
    }

    /**
     * Send email for administrator
     *
     * @param MatchRequestModel $matchRequest
     */
    protected function sendAdminEmail(MatchRequestModel $matchRequest)
    {
        if (Configuration::get('ODEV_PRICEMATCH_SEND_ADMIN_EMAIL')) {
            $product = new Product($matchRequest->id_product, false, $this->context->language->id);
            if (!Mail::Send(
                $this->context->language->id,
                'pricematche_request_admin',
                Mail::l('New price match request!', $this->context->language->id),
                array(
                    '{product}'        => $product->name,
                    '{customer}'    => $matchRequest->customer_name,
                ),
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                null,
                null,
                $this->module->getMailsDir(),
                false,
                $this->context->shop->id
            )) {
                $logger    = new FileLogger();
                $logger->setFilename($this->module->getLogFile());
                $logger->logError(sprintf(Tools::displayError('Price match request #%s: error while sending admin\'s email.'), $matchRequest->id));
            }
        }
    }

    /**
     * Send email for customer
     *
     * @param MatchRequestModel $matchRequest
     */
    protected function sendCustomerEmail(MatchRequestModel $matchRequest)
    {
        if (Configuration::get('ODEV_PRICEMATCH_SEND_CUSTOMER_EMAIL')) {
            if (!Mail::Send(
                $this->context->language->id,
                'pricematche_request_customer',
                Mail::l('Thank you for request.', $this->context->language->id),
                array(
                    '{customer}'    => $matchRequest->customer_name,
                    '{id_request}'    => $matchRequest->id,
                ),
                $matchRequest->customer_email,
                $matchRequest->customer_name,
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                $this->module->getMailsDir(),
                false,
                $this->context->shop->id
            )) {
                $logger    = new FileLogger();
                $logger->setFilename($this->module->getLogFile());
                $logger->logError(sprintf(Tools::displayError('Price match request #%s: error while sending customer\'s email.'), $matchRequest->id));
            }
        }
    }
}
