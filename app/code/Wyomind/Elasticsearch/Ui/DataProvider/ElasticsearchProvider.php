<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Ui\DataProvider;

class ElasticsearchProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $_dataHelper = null;
    protected $_size = 20;
    protected $_offset = 1;
    protected $_likeFilters = [];
    protected $_rangeFilters = [];
    protected $_sortField = 'id';
    protected $_sortDir = 'asc';
    protected $_type = 'product';

    public function __construct(
    $name,
            $primaryFieldName,
            $requestFieldName,
            \Wyomind\Elasticsearch\Helper\Data $dataHelper,
            array $meta = [],
            array $data = []
    )
    {

        $this->_dataHelper = $dataHelper;
        $this->_type = $data['type'];
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function setLimit(
    $offset,
            $size
    )
    {

        $this->_size = $size;
        $this->_offset = $offset;
    }

    public function getData()
    {

        // default store
        $store = $this->_dataHelper->getFirstStoreviewCode();

        $query = [];

        foreach ($this->_likeFilters as $field => $value) {
            if ($field == "indice") {
                $store = $this->_dataHelper->getStoreCode($value);
                continue;
            }
            $query['bool']['must'][] = [
                'match' => [
                    $field => $value
                ]
            ];
        }


        foreach ($this->_rangeFilters as $field => $fromTo) {
            $query['bool']['filter'][] = [
                'range' => [
                    $field => $fromTo
                ]
            ];
        }

//        if (isset($query['bool']['filter'])) {
//            $query['sort'] = [
//                $this->_sortField => $this->_sortDir
//            ];
//        }


        $params = [
            'body' => [
                'from' => ($this->_offset - 1) * $this->_size,
                'size' => $this->_size,
                'query' => $query
            ],
        ];

        if (!isset($query['bool']['filter'])) {
            $params['body']['sort'] = [
                $this->_sortField => $this->_sortDir
            ];
        }

        try {
            $configHandler = new \Wyomind\Elasticsearch\Autocomplete\Config\JsonHandler($store);
            $config = new \Wyomind\Elasticsearch\Autocomplete\Config($configHandler->load());

            if (!$config->getData()) {
                throw new \Exception('Could not find config');
            }

            $client = new \Wyomind\Elasticsearch\Model\Client(new \Elasticsearch\ClientBuilder, $config, new \Psr\Log\NullLogger());
            $index = $client->getIndexAlias($store, $this->_type);
            $code = $this->_type;
            $type = new \Wyomind\Elasticsearch\Autocomplete\Index\Type($code, []);

            $params['from_admin'] = true;

            $info = $client->info();
            $serverVersion = $info['version']['number'];

            if (empty($params['body']['query'])) {
                // ES 5.x
                $query = new \stdClass();
                $params['body']['query'] = ["match_all" => $query];
                // ES 2.x
                if (version_compare($serverVersion, "5.0.0") < 0) {
                    unset($params['body']['query']);
                }
            }


            $response = $client->query($index, $type->getCode(), $params);
            

            $docs = [];
            foreach ($response['hits']['hits'] as $doc) {
                $docs[] = $doc['_source'];
            }
            
            $count = $response['hits']['total'];

            $result = [
                'count' => $count,
                'docs' => $docs
            ];
        } catch (\Exception $e) {
            return [
                'totalRecords' => 0,
                'items' => [],
            ];
        }

        return [
            'totalRecords' => $result['count'],
            'items' => array_values($result['docs']),
        ];
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getConditionType() == "like") {
            $this->_likeFilters[$filter->getField()] = substr($filter->getValue(), 1, -1);
        } elseif ($filter->getConditionType() == "eq") {
            $this->_likeFilters[$filter->getField()] = $filter->getValue();
        } elseif ($filter->getConditionType() == "gteq") {
            $this->_rangeFilters[$filter->getField()]['from'] = $filter->getValue();
        } elseif ($filter->getConditionType() == "lteq") {
            $this->_rangeFilters[$filter->getField()]['to'] = $filter->getValue();
        }
    }

    public function addOrder(
    $field,
            $direction
    )
    {



        $this->_sortField = $field;
        $this->_sortDir = strtolower($direction);
    }

    ############################################################################

    public function addField(
    $field,
            $alias = null
    )
    {
        
    }

    public function count()
    {
        
    }

    public function getSearchResult()
    {
        
    }

    public function removeField(
    $field,
            $isAlias = false
    )
    {
        
    }

    public function removeAllFields()
    {
        
    }

}
