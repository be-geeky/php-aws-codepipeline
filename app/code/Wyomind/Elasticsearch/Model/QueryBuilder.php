<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Helper\Interfaces\QueryInterface as QueryHelperInterface;
use Wyomind\Elasticsearch\Model\Index\TypeInterface;

class QueryBuilder implements QueryBuilderInterface
{

    /**
     * @var QueryHelperInterface
     */
    protected $queryHelper;
    protected $coreHelper;
    protected $compatibility;

    /**
     * @param QueryHelperInterface $queryHelper
     */
    public function __construct(
    QueryHelperInterface $queryHelper,
        \Wyomind\Core\Helper\Data $coreHelper = null
    )
    {
        $this->compatibility = $queryHelper->getCompatibility();
        $this->queryHelper = $queryHelper;
        $this->coreHelper = $coreHelper;
    }

    public function removeToSmallQuery($q)
    {
        $splitted = explode(" ", $q);
        foreach ($splitted as $i => $split) {
            if (strlen(trim($split)) > 2) {
                $splitted[$i] = $split;
            } else {
                $splitted[$i] = "";
            }
        }
        return implode(" ", array_filter($splitted));
    }

    /**
     * {@inheritdoc}
     */
    public function build(
    $q,
        TypeInterface $type,
        $store = null,
        $boolQuery = null
    )
    {

        $q = $this->removeToSmallQuery($q);

        $queries = [];

        $params = [
            'from' => 0,
            'size' => 10000,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => &$queries
                    ],
                ],
            ],
        ];

        $queries[]['multi_match'] = [
            'query' => $q,
            'type' => 'cross_fields',
            'fields' => $type->getSearchFields($q, $store, true, $this->compatibility),
            'lenient' => true, // ignore bad format exception
            'operator' => $this->queryHelper->getQueryOperator($store),
        ];

        if ($this->queryHelper->isFuzzyQueryEnabled($store)) {
            if ($this->compatibility == 6) {
                $queries[]['match']['all'] = [
                    'query' => $q,
                    'operator' => 'AND',
                    'fuzziness' => $this->queryHelper->getFuzzyQueryMode($store),
                ];
            } elseif ($this->compatibility < 6) {
                $queries[]['match']['_all'] = [
                    'query' => $q,
                    'operator' => 'AND',
                    'fuzziness' => $this->queryHelper->getFuzzyQueryMode($store),
                ];
            }
        }


        return $params;
    }

    public function buildQuick(
    $q,
        TypeInterface $type,
        $boolQuery,
        $store = null
    )
    {


        $q = $this->removeToSmallQuery($q);

        $should = $boolQuery->getShould();
        $must = $boolQuery->getMust();

        if (count($should) + count($must) <= 2) {
            return $this->build($q, $type, $store, $boolQuery);
        }

        $queries = [];
        $filters = [];
        $params = [
            'from' => 0,
            'size' => 10000,
            'body' => [
                "sort" => ["_score" => ["order" => "desc"]],
                'query' => [
                    "constant_score" => ["filter" => ["bool" => ["should" => &$queries, "must" => &$filters]]]
                ]
            ]
        ];
        $queries[]['multi_match'] = [
            'query' => $q,
            'type' => 'cross_fields',
            'fields' => $type->getSearchFields($q, $store, true, $this->compatibility),
            'lenient' => true, // ignore bad format exception
            'operator' => $this->queryHelper->getQueryOperator($store),
        ];


        foreach ($should as $key => $info) {

            if ($info instanceof \Magento\Framework\Search\Request\Query\Filter) {
                $reference = $info->getReference();
                if ($reference instanceof \Magento\Framework\Search\Request\Filter\Term) {
                    $value = $reference->getValue();
                    if (isset($value['in'])) {
                        $value = $value['in'];
                    }
                    $field = $reference->getField();
                    if ($field == "category_ids") {
                        $field = "categories";
                    }
                    if (is_array($value)) {
                        $filters [] = ['terms' =>
                            [
                                $field . ($field != "visibility" ? "_ids" : "") => $value
                            ]
                        ];
                    } else {
                        $filters [] = ['term' =>
                            [
                                $field . ($field != "visibility" ? "_ids" : "") => $value
                            ]
                        ];
                    }
                } elseif ($reference instanceof \Magento\Framework\Search\Request\Filter\Range) {
                    $field = $reference->getField();
                    if ($field == "price") {
                        $field = "prices.final_price";
                    }
                    if ($field == "category_ids") {
                        $field = "categories";
                    }
                    $filters[] = [
                        'range' => [
                            $field => [
                                "from" => $reference->getFrom(),
                                "to" => $reference->getTo()
                            ]
                        ]
                    ];
                }
            }
        }

        foreach ($must as $key => $info) {
            if ($info instanceof \Magento\Framework\Search\Request\Query\Filter) {
                $reference = $info->getReference();
                if ($reference instanceof \Magento\Framework\Search\Request\Filter\Term) {
                    $value = $reference->getValue();
                    if (isset($value['in'])) {
                        $value = $value['in'];
                    }
                    if (isset($value['in'])) {
                        $value = $value['in'];
                    }
                    $field = $reference->getField();
                    if ($field == "category_ids") {
                        $field = "categories";
                    }
                    if (is_array($value)) {
                        $filters [] = ['terms' =>
                            [
                                $field . ($field != "visibility" ? "_ids" : "") => $value
                            ]
                        ];
                    } else {
                        $filters [] = ['term' =>
                            [
                                $field . ($field != "visibility" ? "_ids" : "") => $value
                            ]
                        ];
                    }
                } elseif ($reference instanceof \Magento\Framework\Search\Request\Filter\Range) {
                    $field = $reference->getField();
                    if ($field == "category_ids") {
                        $field = "categories";
                    }
                    if ($field == "price") {
                        $field = "prices.final_price";
                    }
                    $filters[] = [
                        'range' => [
                            $field => [
                                "from" => $reference->getFrom(),
                                "to" => $reference->getTo()
                            ]
                        ]
                    ];
                }
            }
        }

        if ($this->queryHelper->isFuzzyQueryEnabled($store)) {

            if ($this->compatibility == 6) {
                $queries[]['match']['all'] = [
                    'query' => $q,
                    'operator' => 'AND',
                    'fuzziness' => $this->queryHelper->getFuzzyQueryMode($store),
                ];
            } elseif ($this->compatibility < 6) {
                $queries[]['match']['_all'] = [
                    'query' => $q,
                    'operator' => 'AND',
                    'fuzziness' => $this->queryHelper->getFuzzyQueryMode($store),
                ];
            }
        }

        return $params;
    }

    /**
     * Advanced Search request
     * @param type $boolQuery
     * @param TypeInterface $type
     * @param type $store
     * @return array
     */
    public function buildAdv(
    $boolQuery,
        TypeInterface $type,
        $store = null
    )
    {

        $should = $boolQuery->getShould();
        $must = $boolQuery->getMust();

        $queries = [];

        $params = [
            'from' => 0,
            'size' => 10000,
            'body' => [
                'query' => []
            ]
        ];

        foreach ($should as $key => $info) {
            if ($info instanceof \Magento\Framework\Search\Request\Query\Match) {
                $matches = $info->getMatches();
                $match = $matches[0];
                $value = $info->getValue();
                if (isset($value['in'])) {
                    $value = $value['in'];
                }
                $cond = "match";
                if ($info->getName() == "sku") {
                    $value = mb_strtolower($value);
                    $cond = "must";
                }
                $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                    $cond => [
                        str_replace("_query", "", $info->getName()) => $value
                    ]
                ];
            } elseif ($info instanceof \Magento\Framework\Search\Request\Query\Filter) {
                $reference = $info->getReference();
                if ($reference instanceof \Magento\Framework\Search\Request\Filter\Wildcard || $reference instanceof \Magento\Framework\Search\Request\Filter\Term) {
                    if (is_array($reference->getValue())) {
                        foreach ($reference->getValue() as $value) {
                            $params['body']['query']['constant_score']['filter']['bool']['should'][] = [
                                'term' => [
                                    str_replace("_query", "", $reference->getField() . ($reference->getField() != "visibility" ? "_ids" : "")) => $value
                                ]
                            ];
                        }
                    } else {
                        $value = $reference->getValue();
                        if (isset($value['in'])) {
                            $value = $value['in'];
                        }
                        if ($reference->getField() == "sku") {
                            $value = mb_strtolower($value);
                        }
                        $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                            'term' => [
                                str_replace("_query", "", $reference->getField() . ($reference->getField() != "visibility" ? "_ids" : "")) => $value
                            ]
                        ];
                    }
                } else {
                    $field = $reference->getField();
                    if ($field == "price") {
                        $field = "prices.final_price";
                    }
                    $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                        'range' => [
                            $field => [
                                "from" => $reference->getFrom(),
                                "to" => $reference->getTo()
                            ]
                        ]
                    ];
                }
            }
        }
        foreach ($must as $key => $info) {
            if ($info instanceof \Magento\Framework\Search\Request\Query\Match) {
                $matches = $info->getMatches();
                $match = $matches[0];
                $value = $info->getValue();
                if (isset($value['in'])) {
                    $value = $value['in'];
                }
                $cond = "match";
                if ($info->getName() == "sku") {
                    $value = mb_strtolower($value);
                    $cond = "must";
                }
                $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                    $cond => [
                        str_replace("_query", "", $info->getName()) => $value
                    ]
                ];
            } elseif ($info instanceof \Magento\Framework\Search\Request\Query\Filter) {
                $reference = $info->getReference();
                if ($reference instanceof \Magento\Framework\Search\Request\Filter\Wildcard || $reference instanceof \Magento\Framework\Search\Request\Filter\Term) {
                    if (is_array($reference->getValue())) {
                        foreach ($reference->getValue() as $value) {
                            $params['body']['query']['constant_score']['filter']['bool']['should'][] = [
                                'term' => [
                                    str_replace("_query", "", $reference->getField() . ($reference->getField() != "visibility" ? "_ids" : "")) => $value
                                ]
                            ];
                        }
                    } else {
                        $value = $reference->getValue();
                        if (isset($value['in'])) {
                            $value = $value['in'];
                        }
                        if ($reference->getField() == "sku") {
                            $value = mb_strtolower($value);
                        }
                        $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                            'term' => [
                                str_replace("_query", "", $reference->getField() . ($reference->getField() != "visibility" ? "_ids" : "")) => $value
                            ]
                        ];
                    }
                } else {
                    $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                        'range' => [
                            $reference->getField() => [
                                "from" => $reference->getFrom(),
                                "to" => $reference->getTo()
                            ]
                        ]
                    ];
                }
            }
        }
        return $params;
    }

    /**
     * Category page request
     * @param type $boolQuery
     * @param TypeInterface $type
     * @param type $store
     * @return array
     */
    public function buildCat(
    $boolQuery,
        TypeInterface $type,
        $store = null
    )
    {

        $must = $boolQuery->getMust();
        $should = $boolQuery->getShould();

        $q = $must['category']->getReference()->getValue();

        $queries = [];

        $params = [
            'from' => 0,
            'size' => 10000,
            'body' => [
                'query' => []
            ]
        ];

        if (!is_array($q)) {
            $params['body']['query']['constant_score']['filter']['bool']['should'][] = [
                'term' => [
                    \Wyomind\Elasticsearch\Helper\Config::PRODUCT_CATEGORIES_ID => $q
                ]
            ];
            $params['body']['query']['constant_score']['filter']['bool']['should'][] = [
                'term' => [
                    \Wyomind\Elasticsearch\Helper\Config::PRODUCT_CATEGORIES_PARENT_ID => $q
                ]
            ];
        } else {
            $params['body']['query']['constant_score']['filter']['bool']['should'][] = [
                'terms' => [
                    \Wyomind\Elasticsearch\Helper\Config::PRODUCT_CATEGORIES_ID => $q
                ]
            ];
            $params['body']['query']['constant_score']['filter']['bool']['should'][] = [
                'terms' => [
                    \Wyomind\Elasticsearch\Helper\Config::PRODUCT_CATEGORIES_PARENT_ID => $q
                ]
            ];
        }


        foreach ($should as $key => $info) {
            $reference = $info->getReference();
            $field = $reference->getField();
            if ($reference instanceof \Magento\Framework\Search\Request\Filter\Range) {
                if ($field == "price") {
                    $field = "prices.final_price";
                }
                $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                    'range' => [
                        $field => [
                            "from" => $reference->getFrom(),
                            "to" => $reference->getTo()
                        ]
                    ]
                ];
            } else {
                if ($this->coreHelper != null && $this->coreHelper->moduleIsEnabled("Amasty_Shopby")) { // fix for Amasty_Shopby !
                    $tmp = $reference->getValue();
                    $value = $tmp[0];
                } else {
                    $value = $reference->getValue();
                }
                if (isset($value['in'])) {
                    $value = $value['in'];
                }
                if (is_array($value)) {
                    $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                        'terms' => [
                            $field . ($field != "visibility" ? "_ids" : "") => $value
                        ]
                    ];
                } else {
                    $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                        'term' => [
                            $field . ($field != "visibility" ? "_ids" : "") => $value
                        ]
                    ];
                }
            }
        }

        foreach ($must as $key => $info) {
            if ($info->getReference()->getField() != "category_ids") {
                $field = $info->getReference()->getField();
                if ($field == "price") {
                    $field = "prices.final_price";
                }
                if (method_exists($info->getReference(), "getFrom")) {
                    $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                        'range' => [
                            $field => [
                                "from" => $info->getReference()->getFrom(),
                                "to" => $info->getReference()->getTo()
                            ]
                        ]
                    ];
                } else {

                    $reference = $info->getReference();
                    $field = $reference->getField();
                    if ($this->coreHelper != null && $this->coreHelper->moduleIsEnabled("Amasty_Shopby")) { // fix for Amasty_Shopby !
                        $tmp = $reference->getValue();
                        $value = $tmp[0];
                    } else {
                        $value = $reference->getValue();
                    }
                    if (isset($value['in'])) {
                        $value = $value['in'];
                    }
                    if (is_array($value)) {
                        $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                            'terms' => [
                                $field . ($field != "visibility" ? "_ids" : "") => $value
                            ]
                        ];
                    } else {
                        $params['body']['query']['constant_score']['filter']['bool']['must'][] = [
                            'term' => [
                                $field . ($field != "visibility" ? "_ids" : "") => $value
                            ]
                        ];
                    }
                }
            }
        }

        return $params;
    }

}
