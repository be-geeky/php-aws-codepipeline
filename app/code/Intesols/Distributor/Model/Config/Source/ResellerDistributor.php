<?php
namespace Intesols\Distributor\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
class ResellerDistributor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{
       
    /**
     * @return array
     */
    public function toArray()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$RDCollection = $objectManager->create('Intesols\Distributor\Model\Resource\Distributor\Collection');
		//$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');		
		$RDCollection->load();
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
                'value' => $key,
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