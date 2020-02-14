<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Indexer;

use Wyomind\Elasticsearch\Helper\Interfaces\CategoryInterface as CategoryHelperInterface;
use Wyomind\Elasticsearch\Helper\Interfaces\IndexerInterface as IndexerHelperInterface;
use Wyomind\Elasticsearch\Model\Request\Dimension;
use Wyomind\Elasticsearch\Model\Request\DimensionFactory;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Symfony\Component\Console\Output\ConsoleOutputFactory;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface
{

    /**
     * @var string
     */
    protected $type;

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var IndexerHandlerFactory
     */
    protected $indexerHandlerFactory;

    /**
     * @var IndexerHelperInterface
     */
    protected $indexerHelper;

    /**
     * @var CategoryHelperInterface
     */
    protected $categoryHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DimensionFactory
     */
    protected $dimensionFactory;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutputFactory
     */
    protected $consoleOutputFactory = null;
    

    /**
     * @var array
     */
    protected $data;

    public function __construct(
    $type,
            ObjectManagerInterface $objectManager,
            IndexerHandlerFactory $indexerHandlerFactory,
            IndexerHelperInterface $indexerHelper,
            CategoryHelperInterface $categoryHelper,
            StoreManagerInterface $storeManager,
            DimensionFactory $dimensionFactory,
            EntityFactory $entityFactory,
            EavConfig $eavConfig,
            ResourceConnection $resource,
            TaxHelper $taxHelper,
            CatalogHelper $catalogHelper,
            EventManagerInterface $eventManager,
            PriceCurrencyInterface $priceCurrency,
            \Symfony\Component\Console\Output\ConsoleOutputFactory $consoleOutputFactory,
            array $data = []
    )
    {

        $this->type = $type;
        $this->objectManager = $objectManager;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->indexerHelper = $indexerHelper;
        $this->categoryHelper = $categoryHelper;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->entityFactory = $entityFactory;
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
        $this->eventManager = $eventManager;
        $this->priceCurrency = $priceCurrency;
        $this->consoleOutputFactory = $consoleOutputFactory;
        $this->data = $data;
    }

    /**
     * @param int $storeId
     * @param array $ids
     * @return \Generator
     */
    abstract public function export(
    $storeId,
            $ids = []
    );

    public function handleError($msg)
    {
        if (php_sapi_name() !== "cli") {
            $this->messageManager->addError($msg);
        } else {
            $this->consoleOutputFactory->create()->writeln($msg);
        }
    }

    public function handleLog($msg)
    {
        if (php_sapi_name() === "cli") {
            $this->consoleOutputFactory->create()->writeln($msg);
        }
    }

    public function handleSuccess($msg)
    {
        if (php_sapi_name() !== "cli") {
            $this->messageManager->addSuccess($msg);
        } else {
            $this->consoleOutputFactory->create()->writeln($msg);
        }
    }

    /**
     * @return CategoryCollection
     */
    protected function createCategoryCollection()
    {
        return $this->objectManager->create(CategoryCollection::class);
    }

    /**
     * @param $entity
     * @return Entity
     */
    protected function createEntity($entity)
    {
        return $this->entityFactory->create()->setType($entity);
    }

    /**
     * @return IndexerHandler
     */
    protected function createIndexerHandler()
    {
        return $this->indexerHandlerFactory->create(['data' => $this->data]);
    }

    /**
     * @param int $storeId
     * @return Dimension
     */
    protected function createStoreDimension($storeId)
    {
        $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
        $dimension->setType($this->type);

        return $dimension;
    }

    /**
     * Deletes multiple documents
     *
     * @param array $ids
     */
    public function delete($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->createIndexerHandler();
        foreach ($storeIds as $storeId) {
            $dimension = $this->createStoreDimension($storeId);
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($ids));
        }
    }

    /**
     * Deletes an unique document
     *
     * @param int $id
     */
    public function deleteRow($id)
    {
        $this->delete([$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->createIndexerHandler();
        foreach ($storeIds as $storeId) {
            $dimension = $this->createStoreDimension($storeId);
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($ids));
            $saveHandler->saveIndex([$dimension], $this->export($storeId, $ids));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->createIndexerHandler();
        foreach ($storeIds as $storeId) {
            $dimension = $this->createStoreDimension($storeId)->setFull();
            $saveHandler->cleanIndex([$dimension]);
            $saveHandler->saveIndex([$dimension], $this->export($storeId));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }

}
