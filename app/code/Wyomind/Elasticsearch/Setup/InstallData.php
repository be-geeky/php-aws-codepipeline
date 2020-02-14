<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\Elasticsearch\Setup;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{


    protected $_productSetupFactory;

    public function __construct(ProductSetupFactory $productSetupFactory)
    {
        $this->_productSetupFactory = $productSetupFactory;
    }
    
    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
    
        
        
        unset($context);

        $productSetup = $this->_productSetupFactory->create(['setup' => $setup]);
        $productSetup->installEntities();
        $installer = $setup;
        $installer->startSetup();
        $installer->endSetup();
    }
}
