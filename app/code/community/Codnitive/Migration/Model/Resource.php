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

abstract class Codnitive_Migration_Model_Resource extends Codnitive_Migration_Model_Db
{
    
    const WEBSITE_ID = 1;
    const STORE_ID   = 1;
    const GROUP_ID   = 1;
    
    protected $_code = null;
    
    protected $_dbConfig = array(
        'host'     => 'localhost',
        'username' => '',
        'password' => '',
        'dbname'   => '',
        'pref'     => '',
        'charset'  => 'utf8',
        'encrypt'  => array ()
    );
    
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function setCode($code)
    {
        $this->_code = (string)$code;
        return $this;
    }
    
    public function getCode()
    {
        if (null === $this->_code) {
            $this->setCode(Mage::app()->getRequest()->getParam('code'));
        }
        return (string)$this->_code;
    }

    public function getDataBaseName($code = null)
    {
        $code = (null === $code) ? $this->getCode() : (string)$code;
        return $this->_databases[$code];
    }
    
    public function getTableName($code = null)
    {
        $code = (null === $code) ? $this->getCode() : (string)$code;
        return $this->_tables[$code];
    }
    
    public function getPrimaryField($code = null)
    {
        $code = (null === $code) ? $this->getCode() : (string)$code;
        return $this->_primaryField[$code];
    }
    
    public function getFieldName($field, $code = null)
    {
        $code = (null === $code) ? $this->getCode() : (string)$code;
        $name = $this->_fieldsList[$field][$code];
        if (empty($name)) {
            $name = 'empty-' . $field;
        }
        return $name;
    }
    
    public function alterTabel()
    {
        $sql = "\n"
            . " ALTER TABLE `{$this->getTableName()}`\n"
            . " ORDER BY `{$this->getPrimaryField()}`";
            
        return $this->query($sql);
    }
    
}
