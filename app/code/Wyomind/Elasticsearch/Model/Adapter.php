<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Helper\Interfaces\AdapterInterface as AdapterHelperInterface;
use Wyomind\Elasticsearch\Model\Index\MappingBuilderInterface;
use Wyomind\Elasticsearch\Model\Request\Dimension;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\Adapter\Mysql\Mapper;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Adapter implements AdapterInterface
{

    /**
     * @var AdapterHelperInterface
     */
    protected $adapterHelper;

    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var QueryBuilderInterface
     */
    protected $queryBuilder;

    /**
     * @var MappingBuilderInterface
     */
    protected $mappingBuilder;

    /**
     * @var DocumentsBuilderInterface
     */
    protected $documentsBuilder;

    /**
     * @var AggregationBuilder
     */
    protected $aggregationBuilder;

    /**
     * @var TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $ping = [];

    /**
     * @param AdapterHelperInterface $adapterHelper
     * @param ClientRegistry $clientRegistry
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param ResourceConnection $resource
     * @param QueryBuilderInterface $queryBuilder
     * @param MappingBuilderInterface $mappingBuilder
     * @param DocumentsBuilderInterface $documentsBuilder
     * @param AggregationBuilder $aggregationBuilder
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param StoreManagerInterface $storeManager
     * @param EventManagerInterface $eventManager
     * @param LoggerInterface $logger
     */
    public function __construct(
    AdapterHelperInterface $adapterHelper,
        ClientRegistry $clientRegistry,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        ResourceConnection $resource,
        QueryBuilderInterface $queryBuilder,
        MappingBuilderInterface $mappingBuilder,
        DocumentsBuilderInterface $documentsBuilder,
        AggregationBuilder $aggregationBuilder,
        TemporaryStorageFactory $temporaryStorageFactory,
        StoreManagerInterface $storeManager,
        EventManagerInterface $eventManager,
        LoggerInterface $logger
    )
    {


        $this->adapterHelper = $adapterHelper;
        $this->clientRegistry = $clientRegistry;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->resource = $resource;
        $this->queryBuilder = $queryBuilder;
        $this->mappingBuilder = $mappingBuilder;
        $this->documentsBuilder = $documentsBuilder;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addDocs(
    Dimension $dimension,
        \Traversable $documents
    )
    {

        // Initialize some variables
        $storeId = $dimension->getStoreId();
        $type = $dimension->getType();
        $full = $dimension->isFull();
        $client = $this->getClient($storeId);

        // Create a new index if full product reindexation is needed
        $new = $this->isCreateNewIndex($dimension);

        // Retrieve store index (create it if not exists)
        $index = $this->getIndex($storeId, $new, $type);

        // Clear all documents if full reindexation required and if type is not 'product'
        if ($full && $type != 'product') {
            $client->delete([], $index, $type);
        }

        // Index documents (this is a bulk indexation according to indexer batch size)
        foreach ($documents as $docs) {
            $client->index($docs, $index, $type);
        }

        if ($new) {
            // Switch alias to the new index when indexation has ended
            $this->switchIndex($index, $storeId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDocs(
    Dimension $dimension,
        array $ids
    )
    {

        // Don't remove anything if full indexation requires a new index creation because useless
        if (empty($ids) && $this->isCreateNewIndex($dimension)) {
            return;
        }

        $storeId = $dimension->getStoreId();
        $type = $dimension->getType();
        $index = $this->getIndex($storeId, false, $type);
        $client = $this->getClient($storeId);
        if (!empty($ids)) {
            $client->delete($ids, $index, $type);
        }
    }

    /**
     * @param int $storeId
     * @return ClientInterface
     */
    public function getClient($storeId)
    {
        return $this->clientRegistry->get($storeId);
    }

    /**
     * Returns current store
     *
     * @return StoreInterface
     */
    protected function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param Table $table
     * @return array
     * @throws \Zend_Db_Exception
     */
    protected function getDocuments(Table $table)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();
        $select->from($table->getName(), ['entity_id', 'score']);

        return $connection->fetchAssoc($select);
    }

    /**
     * @param $storeId
     * @param bool $new
     * @return string
     */
    protected function getIndex(
    $storeId,
        $new = false,
        $type = "product"
    )
    {


        $index = $this->getIndexName($storeId, $new, $type);
        $client = $this->getClient($storeId);

        // Delete index if exists and if we are indexing all documents
        $indexExists = $client->existsIndex($index);
        if ($new && $indexExists) {
            $client->deleteIndex($index);
            $indexExists = false;
        }

        // If index doesn't exist, create it
        if (!$indexExists) {

            $this->eventManager->dispatch('wyomind_elasticsearch_create_index_before', [
                'index' => $index,
                'store' => $storeId,
            ]);

            $client->createIndex($index, $this->getIndexParams($storeId, $type));
            if (!$new) {
                $client->createAlias($index, $this->getIndexAlias($storeId, $type));
            }

            $this->eventManager->dispatch('wyomind_elasticsearch_create_index_after', [
                'index' => $index,
                'store' => $storeId,
            ]);
        }

        return $index;
    }

    /**
     * @param int $storeId
     * @return string
     */
    protected function getIndexAlias($storeId,
        $type = "product")
    {
        $client = $this->getClient($storeId);
        $store = $this->storeManager->getStore($storeId);

        return $client->getIndexAlias($store->getCode(), $type);
    }

    /**
     * @param int $storeId
     * @param bool $new
     * @return string
     */
    protected function getIndexName(
    $storeId,
        $new = false,
        $type = "product"
    )
    {


        $client = $this->getClient($storeId);
        $store = $this->storeManager->getStore($storeId);

        return $client->getIndexName($store->getCode(), $new, $type);
    }

    /**
     * @param int $storeId
     * @return array
     */
    protected function getIndexParams($storeId,
        $type = "product")
    {
        return [
            'custom' => [
                'update_all_types' => true,
            ],
            'body' => [
                'settings' => $this->adapterHelper->getIndexSettings($storeId),
                'mappings' => [
                    $type => $this->mappingBuilder->build($storeId)[$type]
                ]
            ],
        ];
    }

    /**
     * Indicates whether given dimension makes new index creation needed or not
     *
     * @param Dimension $dimension
     * @return bool
     */
    protected function isCreateNewIndex(Dimension $dimension)
    {
        $storeId = $dimension->getStoreId();
        $type = $dimension->getType();
        $full = $dimension->isFull();

        return $full && $type == 'product' && $this->adapterHelper->isSafeReindexEnabled($storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function ping($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getCurrentStore()->getId();
        }

        if (!isset($this->ping[$storeId])) {
            $this->ping[$storeId] = $this->getClient($storeId)->ping();
        }

        return $this->ping[$storeId];
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request, $type = 'product')
    {
        if($_SERVER["REMOTE_ADDR"] == "103.217.167.22") {
//            echo "hi";
//            die();
        }
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        //$logger->info('Wyomind');

        $catalog = false;
        $boolQuery = $request->getQuery();
        if ($boolQuery->getName() == "catalog_view_container") {
            $catalog = true;
        }
        
        try {
            $temporaryStorage = $this->temporaryStorageFactory->create();

            //$must = $request->getQuery()->getMust();
            /* if ((!empty($must) && $must['category'] instanceof \Magento\Framework\Search\Request\Query\Filter) || $request->getQuery()->getType() === QueryInterface::TYPE_FILTER) {
              // We don't handle filter request with Elasticsearch, only full-text search
              $query = $this->mapper->buildQuery($request);
              $table = $temporaryStorage->storeDocumentsFromSelect($query);
              } else { */
            // Send query to Elasticsearch, build results and store them in a temp database table
            $response = $this->request($request);
            $type = $this->mappingBuilder->getType($type);
            $documents = $this->documentsBuilder->build($response, $type, $catalog);


            $table = $temporaryStorage->storeDocuments($documents);
            //}

            $documents = $this->getDocuments($table);
            $aggregations = $this->aggregationBuilder->build($request, $table);

//            foreach ($aggregations as $bucket => $agg) {
//                if ($bucket != "price_bucket" && $bucket != "category_bucket") {
//                    foreach ($agg as $option => $info) {
//                        $aggregations[$bucket][$option]['count'] = 0;
//                    }
//                }
//            }
//            
//            foreach ($response['hits']['hits'] as $product) {
//                if ($product['_source']['visibility'] > 1) {
//                    foreach ($aggregations as $bucket => $agg) {
//                        if ($bucket != "price_bucket" && $bucket != "category_bucket") {
//                            if (isset($product['_source'][str_replace("_bucket","",$bucket)."_ids"])) {
//                                $values = $product['_source'][str_replace("_bucket","",$bucket)."_ids"];
//                                
//                                if (!is_array($values)) {
//                                    $value = $values;
//                                    if (isset($aggregations[$bucket][$value]['count'])) {
//                                        $aggregations[$bucket][$value]['count'] ++;
//                                    }
//                                } else {
//                                    foreach ($values as $value) {
//                                        if (isset($aggregations[$bucket][$value]['count'])) {
//                                            $aggregations[$bucket][$value]['count'] ++;
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
            
            $response = [
                'documents' => $documents,
                'aggregations' => $aggregations,
            ];
            
            return $this->responseFactory->create($response);
        } catch (\Exception $e) {
            $temporaryStorage = $this->temporaryStorageFactory->create();

            $query = $this->mapper->buildQuery($request);
            $table = $temporaryStorage->storeDocumentsFromSelect($query);


            $documents = $this->getDocuments($table);
            $aggregations = $this->aggregationBuilder->build($request, $table);

            $response = [
                'documents' => $documents,
                'aggregations' => $aggregations,
            ];

            return $this->responseFactory->create($response);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function request(
    RequestInterface $request,
        $type = 'product'
    )
    {

        $dimension = current($request->getDimensions());
        if ($dimension && $dimension->getName() == 'scope') {
            $storeId = $dimension->getValue();
        } else {
            $storeId = $this->getCurrentStore()->getId();
        }

        $client = $this->getClient($storeId);
        $index = $this->getIndex($storeId, false, $type);
        $type = $this->mappingBuilder->getType($type);

        /** @var BoolQuery $boolQuery */
        $boolQuery = $request->getQuery();
        $should = $boolQuery->getShould();

        $params = [];
        if ($boolQuery->getName() == "catalog_view_container") {
            $params = $this->queryBuilder->buildCat($boolQuery, $type, $storeId);
        } elseif ($boolQuery->getName() == "advanced_search_container") {
            $params = $this->queryBuilder->buildAdv($boolQuery, $type, $storeId);
        } elseif ($boolQuery->getName() == "quick_search_container" && isset($should['search'])) {
            $matchQuery = $should['search'];
//            $params = $this->queryBuilder->build($matchQuery->getValue(), $type, $storeId);
            $params = $this->queryBuilder->buildQuick($matchQuery->getValue(), $type, $boolQuery, $storeId);
        } elseif (isset($should['search'])) {
            $matchQuery = $should['search'];
            $params = $this->queryBuilder->build($matchQuery->getValue(), $type, $storeId);
        }

        $response = $client->query($index, $type->getCode(), $params);
        return $response;
    }

    public function suggest($q,
        $type)
    {
        $storeId = $this->getCurrentStore()->getId();
        $index = $this->getIndex($storeId, false, $type);
        $suggestParams = [
            "index" => $index,
            "body" => [
                "suggest" => [
                    "name" => [
                        "text" => $q,
                        "phrase" => [
                            "field" => "name",
                            "gram_size" => 1,
                            "max_errors" => 0.9,
                            "direct_generator" => [
                                [
                                    "field" => "name",
                                    "min_word_length" => 3
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $client = $this->getClient($storeId);
        $response = $client->query($index, $type, $suggestParams);

        foreach ($response['suggest']['name'] as $suggests) {
            foreach ($suggests['options'] as $option) {
                if ($option['text'] != $q) {
                    return $option['text'];
                }
            }
        }
    }

    /**
     * @param string $index
     * @param int $storeId
     */
    protected function switchIndex(
    $index,
        $storeId
    )
    {


        $client = $this->getClient($storeId);
        $alias = $this->getIndexAlias($storeId);
        $indices = $client->getIndicesWithAlias($alias);
        foreach ($indices as $indexName) {
            if ($indexName != $index) {
                $client->deleteIndex($indexName); // remove old index that was linked to the alias
            }
        }
        $client->createAlias($index, $alias);
    }

}
