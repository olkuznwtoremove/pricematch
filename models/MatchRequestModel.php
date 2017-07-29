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

class MatchRequestModel extends ObjectModel
{
    public $id_odev_price_match;
        
    public $id_product;
    
    public $id_shop;

    public $id_customer;
    
    public $customer_name;
    
    public $customer_email;

    public $customer_phone;
    
    public $competitor_price;
    
    public $competitor_url;
    
    public $comment;

    public $date_add;

    /**
     * Describes the match request state
     * @var enum 'processing'|'accepted'|'rejected'
     */
    public $state = 'processing';

    public $active = true;

    const STATE_PROCESSING = 'processing';
    const STATE_ACCEPTED = 'accepted';
    const STATE_REJECTED = 'rejected';
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'odev_price_match',
        'primary' => 'id_odev_price_match',
        'fields' => array(
            'id_odev_price_match' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'customer_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 128),
            'customer_email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128),
            'customer_phone' => array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),
            'competitor_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'competitor_url' => array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'comment' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage'),
            'state' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    /**
     *  Copy from post
     */
    public function copyFromPost()
    {
        /* Classical fields */
        foreach ($_POST as $key => $value) {
            if (array_key_exists($key, $this) and $key != 'id_'.self::$definition['table']) {
                $this->{$key} = htmlspecialchars(trim($value));
            }
        }

        /* Multilingual fields */
        if (sizeof(self::$definition['fields'])) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                foreach (self::$definition['fields'] as $field => $validation) {
                    if (Tools::getIsset($field.'_'.(int)($language['id_lang']))) {
                        $this->{$field}[(int)($language['id_lang'])] = htmlspecialchars(trim(Tools::getValue($field.'_'.(int)($language['id_lang']))));
                    }
                }
            }
        }
    }

    /**
     *  Get possible request' statuses
     *
     * @param bool $useKey
     * @return array
     */
    public static function getStatuses($useKey = false)
    {
        $oClass = new ReflectionClass(__CLASS__);
        $constants = $oClass->getConstants();
        $states = array();
        foreach ($constants as $key => $constant) {
            if (false !== strpos($key, 'STATE')) {
                $states[$useKey ? $key : $constant] = $constant;
            }
        }
        return $states;
    }
}
