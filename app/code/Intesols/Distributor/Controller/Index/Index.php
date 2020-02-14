<?php
 
namespace Intesols\Distributor\Controller\Index;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Intesols\Distributor\Model\DistributorFactory;
 
class Index extends Action
{
    /**
     * @var \Tutorial\SimpleNews\Model\DistributorFactory
     */
    protected $_modelDistributorFactory;
 
    /**
     * @param Context $context
     * @param DistributorFactory $_modelDistributorFactory
     */
    public function __construct(
        Context $context,
        DistributorFactory $_modelDistributorFactory
    ) {
        parent::__construct($context);
        $this->__modelDistributorFactory = $_modelDistributorFactory;
    }
 
    public function execute()
    {
        /**
         * When Magento get your model, it will generate a Factory class
         * for your model at var/generaton folder and we can get your
         * model by this way
         */
		 
		 $distributorModel = $this->__modelDistributorFactory->create();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();        
		$DCollection = $objectManager->create('Intesols\Distributor\Model\Config\Source\Distributor');	
		$RCollection = $objectManager->create('Intesols\Distributor\Model\Config\Source\Reseller');	
		//var_dump($DCollection->toArray());
		//var_dump($RCollection->toArray());
		//$RDType = $objectManager->create('Intesols\Distributor\Model\Config\Source\Type');	
		//$RDCollectionOrg = $objectManager->create('Intesols\Distributor\Model\Distributor');
		
        // Load the item with ID is 1        
		echo "<pre>";		
		$productCollection = $objectManager->create('Magento\Catalog\Model\Product');		
		$productCollection->load(1);
		
		$idArray = explode(',', $productCollection->getTest());
		//print_r($idArray);
		$distributorModel1 = $distributorModel->getCollection()->addFieldToFilter('id', array('in' => $idArray))->addFieldToFilter('type', array('eq' => 1))->load();
		
        //var_dump($distributorModel1->getData());
    }
}