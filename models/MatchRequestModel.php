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

    /**
     *  Integratin with datakick data export module
     *
     * @return array
     */
    public static function getDatakickSchema()
    {
      return array(
        'priceMatchRequests' => array(
          'id' => 'priceMatchRequests',
          'singular' => 'priceMatchRequest',
          'description' => 'Price Match Requests',
          'parameters' => array('shop', 'language', 'defaultCurrency'),
          'key' => array('id'),
          'display' => 'name',
          'category' => 'relationships',
          'tables' => array(
            'pmr' => array(
              'table' => 'odev_price_match',
              'conditions' => array(
                'pmr.id_shop = <param:shop>'
              )
            ),
            'c' => array(
              'table' => 'customer',
              'require' => array('pmr'),
              'join' => array(
                'type' => 'LEFT',
                'conditions' => array(
                  'c.id_customer = pmr.id_customer'
                )
              )
            ),
            'pl' => array(
              'table' => 'product_lang',
              'require' => array('pmr'),
              'join' => array(
                'type' => 'LEFT',
                'conditions' => array(
                  'pl.id_shop = pmr.id_shop',
                  'pl.id_product = pmr.id_product',
                  'pl.id_lang = <param:language>'
                )
              )
            )
          ),
          'fields' => array(
            'id' => array(
              'type' => 'number',
              'description' => 'id',
              'require' => array('pmr'),
              'sql' => 'pmr.id_odev_price_match',
              'selectRecord' => 'priceMatchRequests',
              'update' => false
            ),
            'productId' => array(
              'type' => 'number',
              'description' => 'product id',
              'require' => array('pmr'),
              'sql' => 'pmr.id_product',
              'selectRecord' => 'products',
              'update' => false
            ),
            'product' => array(
              'type' => 'string',
              'description' => 'product',
              'require' => array('pl'),
              'sql' => 'pl.name',
              'update' => false
            ),
            'shopId' => array(
              'type' => 'number',
              'description' => 'shop id',
              'require' => array('pmr'),
              'sql' => 'pmr.id_shop',
              'selectRecord' => 'shops',
              'update' => false
            ),
            'customerId' => array(
              'type' => 'number',
              'description' => 'customer id',
              'require' => array('pmr'),
              'sql' => 'pmr.id_customer',
              'selectRecord' => 'customers',
              'update' => false
            ),
            'name' => array(
              'type' => 'string',
              'description' => 'name',
              'require' => array('pmr'),
              'sql' => 'pmr.customer_name',
              'update' => array(
                'pmr' => 'customer_name'
              )
            ),
            'customerName' => array(
              'type' => 'string',
              'description' => 'customer name',
              'require' => array('c'),
              'sql' => 'TRIM(CONCAT(c.firstname, " ", c.lastname))',
              'update' => false
            ),
            'email' => array(
              'type' => 'string',
              'description' => 'email',
              'require' => array('pmr'),
              'sql' => 'pmr.customer_email',
              'update' => array(
                'pmr' => 'customer_email'
              )
            ),
            'phone' => array(
              'type' => 'string',
              'description' => 'phone',
              'require' => array('pmr'),
              'sql' => 'pmr.customer_phone',
              'update' => array(
                'pmr' => 'customer_phone'
              )
            ),
            'competitorPrice' => array(
              'type' => 'currency',
              'description' => 'competitor price',
              'require' => array('pmr'),
              'sql' => array(
                'value' => 'pmr.competitor_price',
                'currency' => '<param:defaultCurrency>'
              ),
              'fixedCurrency' => true,
              'update' => array(
                'pmr' => array(
                  'field' => array(
                    'value' => 'competitor_price'
                  )
                )
              )
            ),
            'competitorUrl' => array(
              'type' => 'string',
              'description' => 'competitor url',
              'require' => array('pmr'),
              'sql' => 'pmr.competitor_url',
              'update' => array(
                'pmr' => 'competitor_url'
              )
            ),
            'comment' => array(
              'type' => 'string',
              'description' => 'comments',
              'require' => array('pmr'),
              'sql' => 'pmr.comment',
              'update' => array(
                'pmr' => 'comment'
              )
            ),
            'active' => array(
              'type' => 'boolean',
              'description' => 'is active',
              'require' => array('pmr'),
              'sql' => 'pmr.active',
              'update' => array(
                'pmr' => 'active'
              )
            ),
            'created' => array(
              'type' => 'datetime',
              'description' => 'date created',
              'require' => array('pmr'),
              'sql' => 'pmr.date_add',
              'update' => array(
                'pmr' => 'date_add'
              )
            ),
            'state' => array(
              'type' => 'string',
              'description' => 'state',
              'require' => array('pmr'),
              'sql' => 'pmr.state',
              'update' => array(
                'pmr' => 'state'
              ),
              'values' => self::getStatuses(false)
            )
          ),
          'expressions' => array(
            'name' => array(
              'type' => 'string',
              'description' => 'customer name',
              'expression' => 'coalesce(<field:customerName>, <field:name>)'
            ),
            'price' => array(
              'type' => 'currency',
              'description' => 'our price',
              'expression' => 'productPrice(<field:productId>, 0, true)'
            ),
            'difference' => array(
              'type' => 'currency',
              'description' => 'difference',
              'expression' => 'productPrice(<field:productId>, 0, true) - <field:competitorPrice>'
            ),
          ),
          'links' => array(
            'product' => array(
              'description' => 'Product',
              'collection' => 'products',
              'type' => 'BELONGS_TO',
              'sourceFields' => array('productId'),
              'targetFields' => array('id')
            ),
            'customer' => array(
              'description' => 'Customer',
              'collection' => 'customers',
              'type' => 'HAS_ONE',
              'sourceFields' => array('customerId'),
              'targetFields' => array('id')
            ),
          ),
          'list' => array(
            'columns' => array('id', 'name', 'product', 'competitorUrl', 'competitorPrice', 'price', 'difference', 'state'),
            'sorts' => array('id')
          )
        ),
        'products' => array(
          'links' => array(
            'priceMatchRequests' => array(
              'description' => 'Price Match Requests',
              'collection' => 'priceMatchRequests',
              'type' => 'HAS_MANY',
              'sourceFields' => array('id'),
              'targetFields' => array('productId')
            )
          )
        ),
        'customers' => array(
          'links' => array(
            'priceAlert' => array(
              'description' => 'Price Match Requests',
              'collection' => 'priceMatchRequests',
              'type' => 'HAS_MANY',
              'sourceFields' => array('id'),
              'targetFields' => array('customerId')
            )
          )
        )
      );
    }
}
