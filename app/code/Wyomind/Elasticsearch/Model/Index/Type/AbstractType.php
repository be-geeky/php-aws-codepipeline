<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Index\Type;

use Wyomind\Elasticsearch\Helper\Attribute as AttributeHelper;
use Wyomind\Elasticsearch\Helper\Interfaces\IndexerInterface as IndexerHelperInterface;
use Wyomind\Elasticsearch\Model\Index\TypeInterface;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

abstract class AbstractType implements TypeInterface
{

    /**
     * @var array
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html
     */
    protected $languages = [
        'arabic', 'armenian', 'basque', 'brazilian', 'bulgarian', 'catalan', 'cjk', 'czech', 'danish', 'dutch',
        'english', 'finnish', 'french', 'galician', 'german', 'greek', 'hindi', 'hungarian', 'indonesian', 'irish',
        'italian', 'latvian', 'lithuanian', 'norwegian', 'persian', 'portuguese', 'romanian', 'russian', 'sorani',
        'spanish', 'swedish', 'turkish', 'thai'
    ];

    /**
     * Core event manager proxy
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var AttributeHelper
     */
    protected $attributeHelper;

    /**
     * @var IndexerHelperInterface
     */
    protected $indexerHelper;
    protected $configHelper = null;

    /**
     * @var string
     */
    protected $code;

    /**
     * @param EventManagerInterface $eventManager
     * @param AttributeHelper $attributeHelper
     * @param IndexerHelperInterface $indexerHelper
     * @param \Wyomind\Elasticsearch\Helper\Config $configHelper
     * @param string $code
     */
    public function __construct(
    EventManagerInterface $eventManager,
            AttributeHelper $attributeHelper,
            IndexerHelperInterface $indexerHelper,
            \Wyomind\Elasticsearch\Helper\Config $configHelper,
            $code
    )
    {

        $this->eventManager = $eventManager;
        $this->attributeHelper = $attributeHelper;
        $this->indexerHelper = $indexerHelper;
        $this->configHelper = $configHelper;
        $this->code = $code;
    }

    /**
     * Returns attribute properties for indexation
     *
     * @param EntityAttribute $attribute
     * @param mixed $store
     * @param bool $withBoost
     * @return array
     */
    public function getAttributeProperties(EntityAttribute $attribute,
            $store = null,
            $withBoost = false)
    {
        $analyzer = $this->getLanguageAnalyzer($store);
        $type = $this->getAttributeType($attribute);

        $compatibility = $this->configHelper->getCompatibility($store);

        if ($compatibility == 6) {
            if ($type === 'option') {
                // Attribute options
                $properties = [
                    'type' => 'text',
                    'analyzer' => $analyzer,
                    'index_options' => 'docs', // do not use tf/idf for options
                    'norms' => ['enabled' => false], // useless for options
                ];
                if ((bool) $attribute->getData('is_searchable')) {
                    $properties['copy_to'] = "all";
                }
            } elseif ($type !== 'string') {
                // Non-string types
                $properties = [
                    'type' => 'text',
                    'index' => 'false', // do not analyze integers, decimals, dates and booleans
                ];
                if ($attribute->getAttributeCode() == "visibility") {
                    $properties['index'] = 'true';
                }
            } else {
                // String type
                $properties = [
                    'type' => 'text',
                    'analyzer' => $analyzer,
                ];
                if ((bool) $attribute->getData('is_searchable')) {
                    $properties['copy_to'] = "all";
                }

                if ($attribute->getBackendType() != 'text') {
                    $properties['fields']['prefix'] = [
                        'type' => 'text',
                        'analyzer' => 'text_prefix',
                        'search_analyzer' => 'std',
                    ];
                    $properties['fields']['suffix'] = [
                        'type' => 'text',
                        'analyzer' => 'text_suffix',
                        'search_analyzer' => 'std',
                    ];
                }
            }
        } elseif ($compatibility < 6) {
            if ($type === 'option') {
                // Attribute options
                $properties = [
                    'type' => 'string',
                    'analyzer' => $analyzer,
                    'index_options' => 'docs', // do not use tf/idf for options
                    'norms' => ['enabled' => false], // useless for options
                    'include_in_all' => (bool) $attribute->getData('is_searchable'),
                ];
            } elseif ($type !== 'string') {
                // Non-string types
                $properties = [
                    'type' => $type,
                    'index' => 'not_analyzed', // do not analyze integers, decimals, dates and booleans
                    'include_in_all' => false, // do not include this kind of field in _all field (used in fuzzy queries)
                ];
                if ($type === 'integer') {
                    $properties['ignore_malformed'] = true;
                }
            } else {
                // String type
                $properties = [
                    'type' => 'string',
                    'analyzer' => $analyzer,
                    'include_in_all' => (bool) $attribute->getData('is_searchable'),
                ];

                if ($attribute->getBackendType() != 'text') {
                    $properties['fields']['prefix'] = [
                        'type' => 'string',
                        'analyzer' => 'text_prefix',
                        'search_analyzer' => 'std',
                    ];
                    $properties['fields']['suffix'] = [
                        'type' => 'string',
                        'analyzer' => 'text_suffix',
                        'search_analyzer' => 'std',
                    ];
                }
            }
        }



        if ($withBoost) {
            $boost = (int) $attribute->getData('search_weight');
            if ($boost > 1) {
                $properties['boost'] = $boost;
            }
        }

        return $properties;
    }

    /**
     * Returns attribute type for indexation
     *
     * @param EntityAttribute $attribute
     * @return string
     */
    protected function getAttributeType(EntityAttribute $attribute)
    {
        $type = 'string';
        if ($this->attributeHelper->isDecimal($attribute)) {
            $type = 'double';
        } elseif ($this->attributeHelper->isBool($attribute)) {
            $type = 'boolean';
        } elseif ($this->attributeHelper->isDate($attribute)) {
            $type = 'date';
        } elseif ($this->indexerHelper->isAttributeUsingOptions($attribute)) {
            $type = 'option'; // custom type
        } elseif ($this->attributeHelper->isInteger($attribute)) {
            $type = 'integer';
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $store
     * @return array
     */
    protected function getEntitySearchableAttributes($store = null)
    {
        return $this->indexerHelper->getEntitySearchableAttributes($this->getCode(), $store);
    }

    /**
     * Returns store language if handled by Elasticsearch
     *
     * @param mixed $store
     * @return string|false
     */
    protected function getLanguage($store = null)
    {
        $language = strtolower($this->indexerHelper->getLanguage($store));

        if (!in_array($language, $this->languages)) {
            $parts = explode(' ', $language); // try with potential first string
            $language = $parts[0];
            if (!in_array($language, $this->languages)) {
                $language = false; // language not present by default in elasticsearch
            }
        }

        return $language;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguageAnalyzer($store = null)
    {
        $language = $this->getLanguage($store); // use built-in language analyzer if possible

        return $language ? : 'std';
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchFields(
    $q,
            $store = null,
            $withBoost = false,
            $compatibility = null
    )
    {

        $fields = [];
        foreach ($this->getProperties($store, $withBoost) as $fieldName => $property) {

            
            // If field is not searchable, ignore it
            $compatibility = $this->configHelper->getCompatibility($store);

            if ($compatibility == 6) {
                if (!isset($property['copy_to']) || ($property['type'] == 'integer' && !is_int($q))) {
                    continue;
                }
            } elseif ($compatibility < 6) {
                if (!isset($property['include_in_all']) || !$property['include_in_all'] || $property['type'] == 'integer' && !is_int($q)) {
                    continue;
                }
            }



            $boost = false;
            if ($withBoost && isset($property['boost'])) {
                $boost = (int) $property['boost'];
            }

            $fields[] = $fieldName . ($boost ? "^$boost" : '');

            if (isset($property['fields'])) {
                foreach ($property['fields'] as $key => $field) {
                    $fields[] = $fieldName . '.' . $key . ($boost ? "^$boost" : '');
                }
            }
        }
        
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($store = null)
    {
        return $this->indexerHelper->isIndexationEnabled($this->getCode(), $store);
    }

    /**
     * {@inheritdoc}
     */
    public function validateResult(array $data)
    {
        return true;
    }

}
