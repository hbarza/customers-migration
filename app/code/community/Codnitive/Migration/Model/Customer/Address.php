<?php
/**
 * CODNITIVE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE_EULA.html.
 * It is also available through the world-wide-web at this URL:
 * http://www.codnitive.com/en/terms-of-service-softwares/
 * http://www.codnitive.com/fa/terms-of-service-softwares/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   Codnitive
 * @package    Codnitive_Migration
 * @author     Hassan Barza <support@codnitive.com>
 * @copyright  Copyright (c) 2012 CODNITIVE Co. (http://www.codnitive.com)
 * @license    http://www.codnitive.com/en/terms-of-service-softwares/ End User License Agreement (EULA 1.0)
 */

class Codnitive_Migration_Model_Customer_Address extends Codnitive_Migration_Model_Resource
{
    
    const COUNTRY_ID = 'IR';

//    protected $_code = null;
//    protected $_sourceCustomer;
    protected $_customerModel;

    protected $_dbConfig = array(
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname'   => '',
        'pref'     => '',
        'charset'  => 'latin1',
        'encrypt'  => array ()
    );
    
    protected $_databases = array(
//        'code' => 'dbName'
        '1'  => 'bgco_users',
    );
    
    protected $_tables = array(
//        'code' => 'tableName'
        '1'  => 'mos_user_extended',
    );
    
    protected $_primaryField = array(
//        'code' => 'fieldName'
        '1'  => 'id',
    );
    
    protected $_fieldsList = array(
        'id' => array(
            '1'  => 'id',
        ),
        'user_id' => array(
            '1'  => 'user_id',
        ),
        'street' => array(
            '1'  => 'user5',
        ),
        'city' => array(
            '1'  => 'user6',
        ),
        'region' => array(
            '1'  => '',
        ),
        'postcode' => array(
            '1'  => '',
        ),
        'telephone' => array(
            '1'  => 'user7',
        ),
        'fax' => array(
            '1'  => '',
        ),
    );
    
    public function __construct($config = array())
    {
        $this->setCode($config['code']);
        unset($config['code']);
        $this->_dbConfig['dbname'] = $this->getDataBaseName();
        
        parent::__construct($config);
    }
    
//    public function setSourceCustomer($customer)
//    {
//        $this->_sourceCustomer = $customer;
//        return $this;
//    }
//    
//    public function getSourceCustomer()
//    {
//        return $this->_sourceCustomer;
//    }
    
    public function setCustomerModel($customerModel)
    {
        $this->_customerModel = $customerModel;
        return $this;
    }
    
    public function getCustomerModel()
    {
        return $this->_customerModel;
    }
    
    public function getAddresses($orderFild, $limit = '', $sc = 'ASC')
    {
        $qu = $this->quSelect($this->getTableName());
        $qu->quOrder($orderFild, $sc);
        $query = $qu->getQuery() . ' ' . $limit;
        $query = rtrim($query, ' ');
        
        return $this->fetchObj($query);
    }
    
    public function getAddress($id, $field = '*')
    {
        $selectedField = ($field == '*') ? $field : 
            $this->getFieldName((string)$field);
            
        $qu = $this->quSelect($this->getTableName(), array($selectedField));
        $qu->quWhere(array($this->getFieldName('id'), '=', $id));
        
        return $this->fetchRow($qu->getQuery(), array(), Zend_Db::FETCH_OBJ);
    }
    
    public function getAddressByUserId($userId, $field = '*')
    {
        $selectedField = ($field == '*') ? $field : 
            $this->getFieldName((string)$field);
            
        $qu = $this->quSelect($this->getTableName(), array($selectedField));
        $qu->quWhere(array($this->getFieldName('user_id'), '=', $userId));
        
        return $this->fetchRow($qu->getQuery(), array(), Zend_Db::FETCH_OBJ);
    }
    
    public function getAddressData($oldId)
    {
        $address = $this->getAddressByUserId($oldId);
        
        $streetField = $this->getFieldName('street');
        $street    = preg_replace('/\s/', '', $address->$streetField);
        
        $telephoneField = $this->getFieldName('telephone');
        $telephone = preg_replace('/\s/', '', $address->$telephoneField);
        
        $postcodeField = $this->getFieldName('postcode');
        $postcode = preg_replace('/\s/', '', $address->$postcodeField);
        
        $faxField = $this->getFieldName('fax');
        
        $data = array(
            'firstname' => $this->getCustomerModel()->getFirstname(), 
            'lastname' => $this->getCustomerModel()->getLastname(),
            'street' => array(
                (!empty($street) && $address->$streetField) ? $address->$streetField : 'Unknown'
            ), 
            'city' => $this->getCity($address), 
            'country_id' => self::COUNTRY_ID, 
            'region' => $this->getRegion($address),
            'postcode' => (!empty($postcode) && $address->$postcodeField) ? $address->$postcodeField : '91',
            'telephone' => (!empty($telephone) && $address->$telephoneField) ? $address->$telephoneField : '+98', 
            'fax' => $address->$faxField,
            'is_default_billing' => true, 
            'is_default_shipping' => true,
        );
        
        return $data;
    }
    
    public function getCity($address)
    {
        $cityField = $this->getFieldName('city');
        $city = $address->$cityField;
        
        return empty($city) ? 'Unknown' : $city;
    }
    
    public function getRegion($address)
    {
        $regionField = $this->getFieldName('region');
        $region = $address->$regionField;
        return empty($region) ? null : $region;
    }

}