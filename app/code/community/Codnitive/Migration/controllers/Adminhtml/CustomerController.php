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

class Codnitive_Migration_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    
    const SEND_EMAIL_SLEEP_TIME = 5;
    
    protected $_code;
    protected $_migrationModel;
    protected $_migrationAddressModel;

    protected function _construct()
    {
        $this->_code  = '1';
        $this->_migrationModel = Mage::getModel('migration/customer', array('code' => $this->_code));
        $this->_migrationAddressModel = Mage::getModel('migration/customer_address', array('code' => $this->_code));
    }
    
    protected function _getHelper()
    {
        return Mage::helper('migration');
    }
    
    protected function _getCustomerApi()
    {
        return Mage::getModel('customer/customer_api');
    }
    
    protected function _getCustomerAddressApi()
    {
        return Mage::getModel('customer/address_api');
    }
    
    protected function _getMigrationCustomerModel()
    {
        return $this->_migrationModel;
//        return Mage::getModel('migration/customer');
    }
    
    protected function _getMigrationCustomerAddressModel()
    {
        return $this->_migrationAddressModel;
//        return Mage::getModel('migration/customer_address');
    }
    
    protected function _getCustomerModel()
    {
        return Mage::getModel('customer/customer');
    }

    public function indexAction()
    {
        try {
            var_dump('hello customer migration admin!');
            $this->customerAction();
        }
        catch (Exception $e) {
            var_dump($e->getMessage());
            echo '<br /><br /><br />';
            var_dump($e);
        }
    }
    
    public function customerAction()
    {
        try {
            echo '<a href="'.$this->getUrl('*/*/add').'">1. Add Customers</a><br />';
        }
        catch (Exception $e) {
            var_dump($e->getMessage());
            echo '<br /><br /><br />';
            var_dump($e);
        }
    }
    
    public function addAction()
    {
        try {
            $timeStart = time();
            
            $api        = $this->_getCustomerApi();
            $addressApi = $this->_getCustomerAddressApi();
            
            $addressModel = $this->_getMigrationCustomerAddressModel();
            $model        = $this->_getMigrationCustomerModel();
            $model->alterTabel();
            $customers    = $model->getCustomers('id', 'LIMIT 0, 10'); // @todo: remove or change limit
            
            echo 'Remove Me';// @todo: remove
            exit;// @todo: remove
            
            $i = 0;
//            $addedCustomerIds = array();
            foreach ($customers as $customer) {
                set_time_limit(0);
                $customer = $this->_corrector($customer);
                
                $customerModel = $this->_getCustomerModel();
                $idField = $model->getFieldName('id');
                $availabelCustomer = $customerModel->load($customer->$idField);
                if ($availabelCustomer->getId()) {
                    continue;
                }
                
                $emailField = $model->getFieldName('email');
                $user = $model->getUser($customer->$idField);
                $customerByEmail = Mage::getModel('customer/customer')
                        ->setWebsiteId(Codnitive_Migration_Model_Resource::WEBSITE_ID)
                        ->loadByEmail($user->$emailField);
                if ($customerByEmail->getId()) {
                    continue;
                }
                
                $model->setCustomer($customer);
                $data = $model->getCustomerData(/*$customer*/);
                $filter = $model->filterCustomer(/*$customer*/);
                if (!$data || !$filter) {
                    continue;
                }
                
                $id = $api->create($data);
//                $addedCustomerIds[] = $id;
                
                $addressModel->setCustomerModel($this->_getCustomerModel()->load($id));
                $addressData = $addressModel->getAddressData($customer->$idField);
                $addressId = $addressApi->create($id, $addressData);
                
                $newCustomer = $customerModel->load($id);
                $newPassword = $newCustomer->generatePassword();
                $newCustomer->changePassword($newPassword);
                $model->sendNewSiteAccountInfoEmail($newCustomer, $newPassword);
                
                sleep(self::SEND_EMAIL_SLEEP_TIME);
                
                ++$i;
                $message = $i . ' - ' . $newCustomer->getName() . ' added - ID: ' . $id . '<br />';
                Mage::log($message, null, 'Codnitive_Migration_add-customers.log', true);
                echo $message;
                echo '<br />';
            }
            
            Mage::log($i . ' Customers added Successfully<br />', null, 'Codnitive_Migration_add-customers.log', true);
            echo '<br />';
            echo $i . ' Customers added Successfully';
            echo '<br />';
            
            $timeEnd = time();
            $timeMsg  = Mage::helper('migration')->getExecutionTimes($timeStart, $timeEnd);
            Mage::log($timeMsg, null, 'Codnitive_Migration_add-customers.log', true);
            echo $timeMsg;
        }
        catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log('<pre>', null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log($e->getTraceAsString(), null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log('<br /><br /><br />', null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log($e->getTrace(), null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log('</pre>', null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log('<br /><br /><br />', null, 'Codnitive_Migration_add-customers_Error.log', true);
            Mage::log($e, null, 'Codnitive_Migration_add-customers_Error.log', true);
            
            var_dump($e->getMessage());
            echo '<pre>';
            print_r($e->getTraceAsString());
            echo '<br /><br /><br />';
            print_r($e->getTrace());
            echo '</pre>';
            echo '<br /><br /><br />';
            var_dump($e);
        }
    }
    
    protected function _corrector($customer)
    {
        $emailField = $this->_migrationModel->getFieldName('email');
        
        $customer->$emailField = preg_replace('/^www./i', '', $customer->$emailField);
        
        return $customer;
    }
            
    
}