<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Setup;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{

    
    private $_coreHelper = null;

    public function __construct(
    \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->_coreHelper = $coreHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        
        $files = [
            "etc/indexer.xml",
            "view/adminhtml/ui_component/elasticsearch_categories_listing.xml",
            "view/adminhtml/ui_component/elasticsearch_products_listing.xml",
            "view/adminhtml/ui_component/elasticsearch_cms_listing.xml"
        ];
        $this->_coreHelper->copyFilesByMagentoVersion(__FILE__, $files);
    }

}
