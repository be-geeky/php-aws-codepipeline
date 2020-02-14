<?php
 
namespace Intesols\Distributor\Model\Resource\Distributor;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Intesols\Distributor\Model\Distributor',
            'Intesols\Distributor\Model\Resource\Distributor'
        );
    }
}