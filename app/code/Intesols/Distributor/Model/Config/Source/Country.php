<?php
namespace Intesols\Distributor\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
class Country implements OptionSourceInterface
{
    /**
     * Get Grid row status type labels array.
     * @return array
     */
    public function getOptionArray()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		//$countryHelper = $objectManager->get('Magento\Directory\Model\Config\Source\Country'); 
		$countryFactory = $objectManager->get('\Magento\Directory\Model\ResourceModel\Country\CollectionFactory')->create()->loadByStore();
		
        //$options = ['1' => __('Reseller'),'2' => __('Distributor')];
        $options = $countryFactory->toOptionArray();
        return $options;
    }
 
    /**
     * Get Grid row status labels array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }
 
    /**
     * Get Grid row type array for option element.
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}