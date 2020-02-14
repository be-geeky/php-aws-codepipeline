<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Autocomplete\Index;

use Wyomind\Elasticsearch\Model\Index\TypeInterface;
use Magento\Framework\DataObject;

class Type extends DataObject implements TypeInterface
{

    /**
     * @var string
     */
    protected $code;

    /**
     * @param string $code
     * @param array $settings
     */
    public function __construct($code,
            array $settings = [])
    {
        $this->code = $code;
        parent::__construct($settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguageAnalyzer($store = null)
    {
        return $this->getData('language_analyzer');
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties($store = null,
            $withBoost = false)
    {
        return $this->getData('index_properties');
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchFields($q,
            $store = null,
            $withBoost = false,
            $compatibility = null)
    {
        $fields = [];


        $properties = $this->getProperties($store, $withBoost);
        if (is_array($properties)) {
            foreach ($properties as $fieldName => $property) {
                // If field is not searchable, ignore it
                if ($compatibility == 6) {
                    if (!isset($property['copy_to']) || $property['type'] == 'integer' && !is_int($q)) {
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

                if ($fieldName != "price") {
                    $fields[] = $fieldName . ($boost ? "^$boost" : '');

                    if (isset($property['fields'])) {
                        foreach ($property['fields'] as $key => $field) {
                            $fields[] = $fieldName . '.' . $key . ($boost ? "^$boost" : '');
                        }
                    }
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
        return $this->getData('enable_autocomplete');
    }

    /**
     * {@inheritdoc}
     */
    public function validateResult(array $data)
    {

        switch ($this->code) {
            case 'product':
                return isset($data[\Wyomind\Elasticsearch\Helper\Config::PRODUCT_PRICES]) && isset($data['visibility']) && $data['visibility'] >= 3; // visible in catalog search
            default:
                return true;
        }
    }

}
