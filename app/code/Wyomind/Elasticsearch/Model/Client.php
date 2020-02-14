<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Client\ConfigInterface;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

class Client implements ClientInterface
{

    /**
     * Client configuration
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * @var \Elasticsearch\Client
     */
    protected $client;
    protected $_serverVersion = "";

    /**
     * @param ClientBuilder $clientBuilder
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
    ClientBuilder $clientBuilder,
            ConfigInterface $config,
            LoggerInterface $logger
    )
    {

        $this->clientBuilder = $clientBuilder;
        $this->config = $config;
        if (!$this->config->getEnableDebugMode()) {
            $this->logger = new \Psr\Log\NullLogger();
        } else {
            $this->logger = $logger;
        }
        $this->init();
    }

    /**
     * @param array $params
     * @return array
     */
    protected function buildParams(array $params = [])
    {
        return array_merge($this->getParams(), $params);
    }

    /**
     * {@inheritdoc}
     */
    public function createAlias(
    $index,
            $alias
    )
    {

        $indices = $this->client->indices();
        $params = [
            'index' => $index,
            'name' => $alias
        ];

        // Remove old alias if needed
        /* if ($indices->existsAlias($params)) {
          $indices->deleteAlias($params);
          } */

        return $indices->putAlias($params);
    }

    public function suggest($params = [])
    {
        return $this->client->suggest($params);
    }

    public function count($params = [])
    {
        return $this->client->count($params);
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(
    $index,
            array $params = []
    )
    {

        $params['index'] = $index;

        return $this->client->indices()->create($params);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
    array $ids,
            $index,
            $type
    )
    {

        // Delete all documents from given type if no $ids is empty
        if (empty($ids)) {
            return $this->deleteType($index, $type);
        }

        $params = ['body' => []];
        foreach ($ids as $id) {
            $params['body'][] = [
                'delete' => [ // bulk action = delete
                    '_index' => $index,
                    '_type' => $type,
                    '_id' => intval($id),
                ],
            ];
        }

        return $this->client->bulk($this->buildParams($params));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($index)
    {
        return $this->client->indices()->delete(['index' => $index]);
    }

    /**
     * Deletes all documents of given type from specified index
     *
     * @param string $index
     * @param string $type
     * @return array
     */
    protected function deleteType(
    $index,
            $type
    )
    {

        $params = [
            'scroll' => '30s',
            'size' => 500,
            'index' => $index,
            'type' => $type,
            'sort' => ['_doc'], // recommended for fast process
            'body' => [
                'query' => [
                    'match_all' => new \stdClass(), // ES 5.x
                //'match_all' => [] // ES 2.x
                ],
                'stored_fields' => [], // we only need _id
            ],
        ];

        if (version_compare($this->_serverVersion, "5.0.0") < 0) {
            $params['body']['query']['match_all'] = [];
            unset($params['body']['stored_fields']);
            $params['body']['fields'] = [];
        }

        $response = $this->client->search($this->buildParams($params));

        while (true) {
            if (!count($response['hits']['hits'])) {
                break;
            }

            $ids = array_map(function ($value) {
                return $value['_id'];
            }, $response['hits']['hits']);

            if (!empty($ids)) {
                $this->delete($ids, $index, $type);
            }

            $params = [
                'scroll_id' => $response['_scroll_id'],
                'scroll' => '30s',
            ];
            $response = $this->client->scroll($this->buildParams($params));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function existsAlias($alias)
    {
        $params = ['name' => $alias];

        return $this->client->indices()->existsAlias($this->buildParams($params));
    }

    /**
     * {@inheritdoc}
     */
    public function existsIndex($index)
    {
        $params = ['index' => $index];

        return $this->client->indices()->exists($this->buildParams($params));
    }

    /**
     * {@inheritdoc}
     */
    public function getByIds(
    $indices,
            $type,
            array $ids = []
    )
    {

        $params['index'] = implode(',', (array) $indices);
        $params['type'] = [$type];
        $params['body'] = ['ids' => $ids];

        return $this->client->mget($this->buildParams($params));
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexAlias($name,
            $type = "product")
    {
        return $this->config->getIndexPrefix() . $name . "_" . $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(
    $name,
            $new = false,
            $type = "product"
    )
    {

        $alias = $this->getIndexAlias($name, $type);
        $name = $alias . '_idx1'; // index name must be different than alias name

        $indices = $this->getIndicesWithAlias($alias);
        if (!empty($indices)) {
            // Retrieve first indice because we should not have more than 1 indice per alias in our context
            $index = current($indices);
            if ($new) {
                $name = $index != $name ? $name : $alias . '_idx2';
            } else {
                $name = $index;
            }
        }

        return $name;
    }

    public function indices()
    {
        return $this->client->indices();
    }

    /**
     * {@inheritdoc}
     */
    public function getIndicesWithAlias($alias)
    {
        $indices = [];

        try {
            $params = ['name' => $alias];
            $aliasInfo = $this->client->indices()->getAlias($this->buildParams($params));
            if (is_array($aliasInfo) && count($aliasInfo)) {
                $indices = array_keys($aliasInfo);
            }
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            // Alias does not exist
            return [];
        }

        return $indices;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return [
            'client' => [
                'verify' => $this->config->isVerifyHost(),
                'connect_timeout' => $this->config->getConnectTimeout(),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function index(
    array $docs,
            $index,
            $type
    )
    {

        if (empty($docs)) {
            return;
        }

        $params = ['body' => []];
        foreach ($docs as $id => $doc) {
            $params['body'][] = [
                'index' => [ // bulk action = index
                    '_index' => $index,
                    '_type' => $type,
                    '_id' => intval($id),
                ],
            ];
            $params['body'][] = $doc;
        }

        return $this->client->bulk($this->buildParams($params));
    }

    /**
     * Initializes client
     */
    protected function init()
    {
        $this->client = $this->clientBuilder
                ->setHosts($this->config->getHosts())
                ->setLogger($this->logger)
                ->build();
        $info = $this->info();
        $this->_serverVersion = $info['version']['number'];
    }

    public function info()
    {
        return $this->client->info();
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        try {
            $this->client->ping($this->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }

        return true;
    }

    public function isProductWeightEnabled()
    {
        return $this->config->isProductWeightEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function query(
    $indices,
            $types,
            array $params = []
    )
    {



        $params['index'] = implode(',', (array) $indices);
        $params['type'] = implode(',', (array) $types);


        if (!isset($params['from_admin']) || (isset($params['from_admin']) && !$params['from_admin'])) {
            
            if ($this->isProductWeightEnabled() && isset($params["body"]["query"])) {
                
                $query = $params["body"]["query"];

                $newQuery = [
                    "function_score" => [
                        "query" => $query,
                        "boost_mode" => "sum",
                        "functions" => [
                            [
                                "field_value_factor" => [
                                    "field" => "product_weight",
                                    "factor" => 10,
                                    "modifier" => "square",
                                    "missing" => 0
                                ]
                            ]
                        ]
                    ]
                ];

                $params["body"]["query"] = $newQuery;
            }
        }
        unset($params['from_admin']);

        return $this->client->search($this->buildParams($params));
    }

    /**
     * {@inheritdoc}
     */
    public function setMapping(
    $index,
            $type,
            array $params
    )
    {

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => $params,
        ];

        $this->client->indices()->putMapping($this->buildParams($params));
    }

}
