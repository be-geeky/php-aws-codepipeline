<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Ui\Component\Products;

class Columns extends \Magento\Ui\Component\Listing\Columns
{

    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    protected $attributeRepository = null;
    protected $indexerHelper = null;
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Wyomind\Elasticsearch\Helper\Interfaces\IndexerInterface $indexerHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $components, $data);
        $this->objectManager = $objectManager;
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->indexerHelper = $indexerHelper;
    }

    public function prepare()
    {

        $order = $this->objectManager->create('\Magento\Framework\Api\SortOrder');
        $order->setField('frontend_label');
            $order->setDirection('ASC');

        $searchCriteria = $this->objectManager->create('\Magento\Framework\Api\SearchCriteria');
        $searchCriteria->setSortOrders([$order]);

        $attributesList = $this->attributeRepository->getList(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $searchCriteria);

        foreach ($attributesList->getItems() as $attribute) {
            if (!$this->indexerHelper->isAttributeIndexable($attribute) || in_array($attribute->getAttributeCode(), ['image','price','sku','name','visibility'])) {
                continue;
            }
            $config = [];
            $config['filter'] = $this->getFilterType($attribute->getFrontendInput());
            $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
            $column->prepare();
            $this->addComponent($attribute->getAttributeCode(), $column);
        }
        parent::prepare();
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
