<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Wyomind\Elasticsearch\Helper\Interfaces\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryObject;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class Category extends AbstractHelper implements CategoryInterface
{

    /**
     * Cache for category collections
     *
     * @var CategoryCollection[]
     */
    protected $categoriesWithPathNames = [];

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
    
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * @return CategoryCollection
     */
    protected function createCategoryCollection()
    {
        return $this->objectManager->create(CategoryCollection::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesWithPathNames($storeId)
    {
        if (!isset($this->categoriesWithPathNames[$storeId])) {
            $collection = $this->createCategoryCollection()
                ->addAttributeToSelect('name')
                ->setStoreId($storeId);

            foreach ($collection as $category) {
                /** @var CategoryObject $category */
                $category->setData('path_names', new \ArrayObject());
                $pathIds = array_slice($category->getPathIds(), 2);

                if (!empty($pathIds)) {
                    foreach ($pathIds as $pathId) {
                        /** @var CategoryObject $item */
                        if ($item = $collection->getItemById($pathId)) {
                            $category->getData('path_names')->append($item->getName());
                        }
                    }
                }
            }

            $this->categoriesWithPathNames[$storeId] = $collection;
        }

        return $this->categoriesWithPathNames[$storeId];
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryPathName(CategoryObject $category, $separator = ' > ')
    {
        $categoryWithPathNames = $this->getCategoriesWithPathNames($category->getStoreId())
            ->getItemById($category->getId());

        if ($categoryWithPathNames) {
            return implode($separator, (array) $categoryWithPathNames->getData('path_names'));
        }

        return $category->getName();
    }
}
