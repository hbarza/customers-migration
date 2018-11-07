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

class Codnitive_Migration_Model_Customer extends Codnitive_Migration_Model_Resource
{
//    const WEBSITE_ID = 1;
//    const STORE_ID   = 1;
//    const GROUP_ID   = 1;
    
    const XML_PATH_NEWSITEACCOUNT_EMAIL_TEMPLATE = 'migration/customer/newsiteaccount_email_template';
    const XML_PATH_NEWSITEACCOUNT_EMAIL_IDENTITY = 'migration/customer/newsiteaccount_email_identity';

    protected $_code = null;
    protected $_customer;
//    protected $_sourceCustomer;

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
        '1'  => 'mos_users',
    );
    
    protected $_primaryField = array(
//        'code' => 'fieldName'
        '1'  => 'id',
    );
    
    protected $_fieldsList = array(
        'id' => array(
            '1'  => 'id',
        ),
        'full_name' => array(
            '1'  => 'name',
        ),
        'created_at' => array(
            '1'  => 'registerDate',
        ),
        'email' => array(
            '1'  => 'email',
        ),
        'firstname' => array(
            '1'  => '',
        ),
        'lastname' => array(
            '1'  => '',
        ),
        'password' => array(
            '1'  => 'password',
        ),
        'group_id' => array(
            '1'  => 'gid',
        ),
    );
    
    public function __construct($config = array())
    {
        $this->setCode($config['code']);
        unset($config['code']);
        $this->_dbConfig['dbname'] = $this->getDataBaseName();
        
        parent::__construct($config);
    }

//    public function setCode($code)
//    {
//        $this->_code = (string)$code;
//        return $this;
//    }
//    
//    public function getCode()
//    {
//        if (null === $this->_code) {
//            $this->setCode(Mage::app()->getRequest()->getParam('code'));
//        }
//        return (string)$this->_code;
//    }
    
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
        return $this;
    }
    
    public function getCustomer()
    {
        return $this->_customer;
    }

//    public function getDataBaseName($code = null)
//    {
//        $code = (null === $code) ? $this->getCode() : (string)$code;
//        return $this->_databases[$code];
//    }
//    
//    public function getTableName($code = null)
//    {
//        $code = (null === $code) ? $this->getCode() : (string)$code;
//        return $this->_tables[$code];
//    }
//    
//    public function getPrimaryField($code = null)
//    {
//        $code = (null === $code) ? $this->getCode() : (string)$code;
//        return $this->_primaryField[$code];
//    }
//    
//    public function getFieldName($field, $code = null)
//    {
//        $code = (null === $code) ? $this->getCode() : (string)$code;
//        $name = $this->_fieldsList[$field][$code];
//        if (empty($name)) {
//            $name = 'empty-' . $field;
//        }
//        return $name;
//    }
//    
//    public function alterTabel()
//    {
//        $sql = "\n"
//            . " ALTER TABLE `{$this->getTableName()}`\n"
//            . " ORDER BY `{$this->getPrimaryField()}`";
//            
//        return $this->query($sql);
//    }
    
    public function getCustomers($orderFild, $limit = '', $sc = 'ASC')
    {
        $qu = $this->quSelect($this->getTableName());
        $qu->quOrder($orderFild, $sc);
        $query = $qu->getQuery() . ' ' . $limit;
        $query = rtrim($query, ' ');
        
        return $this->fetchObj($query);
    }
    
    public function getUser($id, $field = 'email')
    {
        $selectedField = ($field == '*') ? $field : 
            $this->getFieldName((string)$field);
            
        $qu = $this->quSelect($this->getTableName(), array($selectedField));
        $qu->quWhere(array($this->getFieldName('id'), '=', $id));
        
        return $this->fetchRow($qu->getQuery(), array(), Zend_Db::FETCH_OBJ);
    }
    
    public function getCustomerData(/*$customer*/)
    {
        $customer = $this->getCustomer();
        $idField = $this->getFieldName('id');
        $emailField = $this->getFieldName('email');
        
        $user = $this->getUser($customer->$idField, '*');
        
        if (!$user || empty($user->$emailField)) {
            return false;
        }
        
        $passwordField = $this->getFieldName('password');
        $createdAtField = $this->getFieldName('created_at');
        $nameArray = $this->getCustomerNameFromFullName();
        
        $data = array(
            'customer_id' => (int)$customer->$idField ? (int)$customer->$idField : null,
            'created_at' => ($customer->$createdAtField == '0000-00-00 00:00:00') ? date('Y-m-d H:i:s') : $customer->$createdAtField,
            'email' => $customer->$emailField, 
            'password' => $customer->$passwordField,
            'firstname' => $nameArray['first_name'], 
            'lastname' => $nameArray['last_name'],
            'website_id' => self::WEBSITE_ID,
            'store_id' => self::STORE_ID,
            'group_id' => self::GROUP_ID,
        );
        
        return $data;
    }
    
    public function getCustomerNameFromFullName()
    {
        $fullNameField = $this->getFieldName('full_name');
        $fullName = $this->getCustomer()->$fullNameField;
        if (empty($fullName)) {
            return array('first_name' => 'unknown', 'last_name' => 'unknown');
        }
        $array = explode(' ', $fullName);
        $nameArray['first_name'] = empty($array[0]) ? 'unknown' : $array[0];
        unset($array[0]);
        $lastName = implode(' ', $array);
        $nameArray['last_name']  = empty($lastName) ? 'unknown' : $lastName;
        
        return $nameArray;
    }

    public function sendNewSiteAccountInfoEmail($cutomer, $password)
    {
        $storeId = self::STORE_ID;
        $this->_customer = $cutomer;

        $this->_sendEmailTemplate(self::XML_PATH_NEWSITEACCOUNT_EMAIL_TEMPLATE, self::XML_PATH_NEWSITEACCOUNT_EMAIL_IDENTITY,
            array('customer' => $cutomer, 'password' => $password), $storeId);

        return $this;
    }
    
    protected function _sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null)
    {
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->_customer->getEmail(), $this->_customer->getName());
        $mailer->addEmailInfo($emailInfo);

        $mailer->setSender(Mage::getStoreConfig($sender, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId(Mage::getStoreConfig($template, $storeId));
        $mailer->setTemplateParams($templateParams);
        $mailer->send();
        return $this;
    }
    
    public function filterCustomer(/*$customer*/)
    {
        $customer = $this->getCustomer();
        $fullNameField = $this->getFieldName('full_name');
        $fullName = $customer->$fullNameField;
//        $user      = $this->getUser($customer->fldUID, '*');
        
//        if ($user->lastvisitDate == '0000-00-00 00:00:00') {
//            return false;
//        }
        
        $condition = (empty($fullName)/* && empty($customer->fldFamily)*/)
            || (strlen($fullName) <= 1/* && strlen($customer->fldFamily) <= 1*/)
            || (is_numeric($fullName)/* || is_numeric($customer->fldFamily)*/);
//            || (preg_match('/(.)\\1{2}/', $fullName)/* || preg_match('/(.)\\1{2}/', $customer->fldFamily)*/);
        
        if ($condition) {
            return false;
        }
        
        $pattern = '_\~|\`|\!|\@|\#|\$|\%|\^|\&|\*|\,|\;|\:|\\|\/_';
        if (preg_match($pattern, $fullName)/* || preg_match($pattern, $customer->fldFamily)*/) {
            return false;
        }
        
        $emailField = $this->getFieldName('email');
        $email = $customer->$emailField;
        $validator = new Zend_Validate_EmailAddress();
        if(!$validator->isValid($email)) {
            return false;
        }
        
        return true;
    }

}