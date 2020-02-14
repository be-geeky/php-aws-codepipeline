<?php
 
namespace Intesols\Distributor\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Distributor extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Intesols\Distributor\Model\Resource\Distributor');
    }
}