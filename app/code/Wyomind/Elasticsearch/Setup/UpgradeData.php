<?php

namespace Wyomind\Elasticsearch\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{

    protected $_productSetupFactory;
    protected $_coreHelper;

    public function __construct(
        ProductSetupFactory $productSetupFactory,
        \Wyomind\Core\Helper\Data $coreHelper
    ) {
    
        $this->_productSetupFactory = $productSetupFactory;
        $this->_coreHelper = $coreHelper;
    }

    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
    

        // update to 5.2.0
        if (version_compare($context->getVersion(), '5.2.0') < 0) {
            $productSetup = $this->_productSetupFactory->create(['setup' => $setup]);
            $productSetup->installEntities();
            $installer = $setup;
            $installer->startSetup();
            $installer->endSetup();
        }

        // update to 5.3.0
        if (version_compare($context->getVersion(), '5.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $productTemplate = $this->_coreHelper->getDefaultConfig("elasticsearch/types/product/autocomplete_template");
            if ($productTemplate != "") {
                $productTemplate = str_replace(["doc._url", "doc._prices"], ["doc.url", "doc.prices"], $productTemplate);
                $this->_coreHelper->setDefaultConfig("elasticsearch/types/product/autocomplete_template", $productTemplate);
            }
            $categoryTemplate = $this->_coreHelper->getDefaultConfig("elasticsearch/types/category/autocomplete_template");
            if ($categoryTemplate != "") {
                $categoryTemplate = str_replace(["doc._url", "doc._path"], ["doc.url", "doc.path"], $categoryTemplate);
                $this->_coreHelper->setDefaultConfig("elasticsearch/types/category/autocomplete_template", $categoryTemplate);
            }
            $installer->endSetup();
        }
        
        
    }
}
