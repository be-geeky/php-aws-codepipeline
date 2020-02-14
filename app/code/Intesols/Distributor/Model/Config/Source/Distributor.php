<?php
namespace Intesols\Distributor\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
class Distributor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
       
    /**
     * @return array
     */
    public function toArray()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();		
		$RDCollection = $objectManager->create('Intesols\Distributor\Model\DistributorFactory');
		$RDCollection = $RDCollection->create();		
		$RDCollection = $RDCollection->getCollection()->addFieldToFilter('type', array('eq' => 2))->load();
		$list = [];
		foreach($RDCollection as $rd){
			$list[$rd->getId()] = $rd->getName();
		}
		
        return $list;
    }

    /**
     * Options getter
     * @return array
     */
    final public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value) {
            $ret[] = [
                // Always return a string:
                'value' => (string) $key,
                'label' => $value
            ];
        }

        return $ret;
    }
 
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}