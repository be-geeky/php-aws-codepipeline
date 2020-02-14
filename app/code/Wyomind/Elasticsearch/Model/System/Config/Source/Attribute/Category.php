<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\System\Config\Source\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Category implements ArrayInterface
{

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @param EavConfig $eavConfig
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        EavConfig $eavConfig,
        AttributeCollectionFactory $attributeCollectionFactory,
        EventManagerInterface $eventManager
    ) {
    
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Return list of searchable attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $entityType = $this->eavConfig->getEntityType('catalog_category');

        $collection = $this->attributeCollectionFactory->create();
        $collection->setEntityTypeFilter($entityType->getEntityTypeId())
                ->addFieldToFilter('source_model', [
                    ['neq' => 'eav/entity_attribute_source_boolean'],
                    ['null' => true]
                ])
                ->addFieldToFilter(
                    ['frontend_input', 'is_searchable'],
                    [['in' => ['text', 'textarea']], '1']
                )
                ->addFieldToFilter('backend_type', ['nin' => ['static', 'decimal']])
                ->addFieldToFilter('attribute_code', ['neq' => 'custom_layout_update'])
                ->setOrder('frontend_label', 'ASC');

        $this->eventManager->dispatch('wyomind_elasticsearch_category_attributes', [
            'collection' => $collection
        ]);

        foreach ($collection as $attribute) {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            if ($attribute->getFrontendLabel()) {
                $options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                ];
            }
        }

        return $options;
    }
}
