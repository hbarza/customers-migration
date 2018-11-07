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

class Codnitive_Migration_Helper_Data extends Mage_Core_Helper_Data
{
    
    public function getExecutionTimes($timeStart, $timeEnd)
    {
        $executionTime = ($timeEnd - $timeStart);
        
        $timeMsg  = '<br /><b>Total Execution Time:</b><br />';
        $timeMsg .= $executionTime . ' Secs<br />';
        $timeMsg .= $executionTime / 60 . ' Mins<br />';
        $timeMsg .= $executionTime / 3600 . ' Hurs<br /><br />';
        
        return $timeMsg;
    }
    
}
