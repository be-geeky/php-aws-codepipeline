<?php
 
namespace Intesols\Distributor\Model\Resource;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Distributor extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('reseller_distributor', 'id');
    }
}