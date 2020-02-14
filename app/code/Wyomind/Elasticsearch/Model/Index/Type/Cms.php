<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Index\Type;

use Wyomind\Elasticsearch\Helper\Attribute as AttributeHelper;
use Wyomind\Elasticsearch\Helper\Interfaces\IndexerInterface as IndexerHelperInterface;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class Cms extends AbstractType
{

    /**
     * @var PageResource
     */
    protected $pageResource;

    /**
     * @param PageResource $pageResource
     * @param EventManagerInterface $eventManager
     * @param AttributeHelper $attributeHelper
     * @param IndexerHelperInterface $indexerHelper
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param string $code
     */
    public function __construct(
    PageResource $pageResource,
            EventManagerInterface $eventManager,
            AttributeHelper $attributeHelper,
            IndexerHelperInterface $indexerHelper,
            \Wyomind\Elasticsearch\Helper\Config $configHelper,
            $code
    )
    {

        parent::__construct($eventManager, $attributeHelper, $indexerHelper, $configHelper, $code);

        $this->pageResource = $pageResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(
    $store = null,
            $withBoost = false
    )
    {

        $properties = [];

        $mainTable = $this->pageResource->getMainTable();
        $tableInfo = $this->pageResource
                ->getConnection()
                ->describeTable($mainTable);

        $compatibility = $this->configHelper->getCompatibility($store);

        foreach ($this->getEntitySearchableAttributes($store) as $field) {
            if (isset($tableInfo[$field])) {
                if ($compatibility == 6) {
                    $properties[$field] = [
                        'type' => 'text',
                        'analyzer' => $this->getLanguageAnalyzer($store),
                        'copy_to' => 'all',
                    ];
                    if ($tableInfo[$field]['DATA_TYPE'] == 'varchar') {
                        $properties[$field]['fields']['prefix'] = [
                            'type' => 'text',
                            'analyzer' => 'text_prefix',
                            'search_analyzer' => 'std',
                        ];
                        $properties[$field]['fields']['suffix'] = [
                            'type' => 'text',
                            'analyzer' => 'text_suffix',
                            'search_analyzer' => 'std',
                        ];
                    }
                    if ($field == "title") {
                        $properties[$field . "_suggester"] = [
                            'type' => 'completion',
                            'analyzer' => 'std',
                            'search_analyzer' => 'std',
                        ];
                    }
                } elseif ($compatibility < 6) {
                    $properties[$field] = [
                        'type' => 'string',
                        'analyzer' => $this->getLanguageAnalyzer($store),
                        'include_in_all' => true
                    ];
                    if ($tableInfo[$field]['DATA_TYPE'] == 'varchar') {
                        $properties[$field]['fields']['prefix'] = [
                            'type' => 'string',
                            'analyzer' => 'text_prefix',
                            'search_analyzer' => 'std',
                        ];
                        $properties[$field]['fields']['suffix'] = [
                            'type' => 'string',
                            'analyzer' => 'text_suffix',
                            'search_analyzer' => 'std',
                        ];
                    }
                    if ($field == "title") {
                        $properties[$field . "_suggester"] = [
                            'type' => 'completion',
                            'analyzer' => 'std',
                            'search_analyzer' => 'std',
                        ];
                    }
                }
            }
        }

        if ($compatibility == 6) {
            $properties["all"] = [
                "type" => "text"
            ];
        }

        $properties["id"] = [
            "type" => "long"
        ];
        

        
        $properties = new DataObject($properties);

        $this->eventManager->dispatch('wyomind_elasticsearch_cms_index_properties', [
            'indexer' => $this,
            'store' => $store,
            'properties' => $properties,
        ]);

        return $properties->getData();
    }

}
