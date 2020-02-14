<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Setup;

use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Attribute installation
 */
class ProductSetup extends EavSetup
{

    protected $_productFactory;

    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        ProductFactory $productFactory
    ) {
    
        $this->_productFactory = $productFactory;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /*
     * @return void
     */

    public function getDefaultEntities()
    {
        $attributes = [];
        
        $attributes["product_weight"] = [
            'group' => "Elasticsearch",
            'label' => "Product weight",
            'default' => '1',
            'note' => '',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'unique' => false,
            "frontend_class" => "",
            "used_in_product_listing" => 1,
            "input" => "select",
            "type" => "int",
            "source" => "Wyomind\Elasticsearch\Model\Config\Source\ProductWeight",
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
        ];

        return [
            'catalog_product' => [
                'entity_model' => 'Magento\Catalog\Model\ResourceModel\Product',
                'attribute_model' => 'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
                'table' => 'catalog_product_entity',
                'additional_attribute_table' => 'catalog_eav_attribute',
                'entity_attribute_collection' => 'Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection',
                'attributes' => $attributes
            ]
        ];
    }
}
