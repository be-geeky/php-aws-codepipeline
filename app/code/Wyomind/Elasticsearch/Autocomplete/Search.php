<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Autocomplete;

use Wyomind\Elasticsearch\Model\ClientInterface;
use Wyomind\Elasticsearch\Model\Index\TypeInterface;

class Search
{

    /**
     * @var ClientInterface
     */
    protected $client;
    protected $compatibility = 6;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client,
                                $compatibility)
    {
        $this->compatibility = $compatibility;
        $this->client = $client;
    }

    /**
     * @param string $index
     * @param TypeInterface $type
     * @param array $params
     * @return array
     */
    public function query(
        $index,
        TypeInterface $type,
        array $params = [],
        $highlight = true,
        $suggestNumber = true
    )
    {


        $searchTerm = $params['body']['query']['bool']['should'][0]['multi_match']['query'];

        $suggestsResults = [];
        if ($type->getCode() == "product") {

            $suggestParams = [
                "index" => $index,
                "body" => [
                    "suggest" => [
                        "name" => [
                            "text" => $searchTerm,
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

            $response = $this->client->query($index, $type->getCode(), $suggestParams);

            if (isset($response['suggest']) && isset($response['suggest']['name'])) {
                foreach ($response['suggest']['name'] as $suggests) {
                    foreach ($suggests['options'] as $option) {
                        if ($option['text'] != $searchTerm) {

                            $countParams = $params;
                            $countParams['body']['query']['bool']['should'][0]['multi_match']['query'] = $option['text'];
                            if ($this->compatibility == 6) {
                                $countParams['body']['query']['bool']['should'][1]['match']['all']['query'] = $option['text'];
                            } else {
                                $countParams['body']['query']['bool']['should'][1]['match']['_all']['query'] = $option['text'];
                            }
                            if ($suggestNumber) {
                                $suggestsResults[] = ["text" => $option['text'], "count" => count($this->query($index, $type, $countParams, false, false)[0])];
                            } else {
                                $suggestsResults[] = ["text" => $option['text'], "count" => 0];
                            }
                            //break 2;
                        }
                    }
                }
            }
        }


        $response = $this->client->query($index, $type->getCode(), $params);
        $docs = [];
        foreach ($response['hits']['hits'] as $doc) {
            $data = $doc['_source'];
            $ids[] = $doc['_id'];
            if ($type->validateResult($data)) {
                $docs[$doc['_id']] = $doc['_score'];
            }

            if (isset($data[\Wyomind\Elasticsearch\Helper\Config::PRODUCT_PARENT_IDS])) {
                foreach ($data[\Wyomind\Elasticsearch\Helper\Config::PRODUCT_PARENT_IDS] as $parentId) {
                    if (isset($docs[$parentId])) {
                        $doc['_score'] = max($doc['_score'], $docs[$parentId]);
                    }
                    $docs[$parentId] = $doc['_score'];
                }
            }
        }

        $result = [];

        if (!empty($docs)) {

            $ids = [];
            $scores = [];
            $docsNew = [];

            foreach ($docs as $id => $score) {
                $docsNew[] = ['id' => $id, 'score' => $score];
                $ids[$id] = $id;
                $scores[$id] = $score;
            }


            array_multisort($scores, SORT_DESC, $ids, SORT_ASC, $docsNew);
            $docs = [];
            foreach ($docsNew as $doc) {
                $docs[] = $doc['id'];
            }

            $response = $this->client->getByIds($index, $type->getCode(), $docs);

            foreach ($response['docs'] as $data) {
                if (isset($data['_source']) && $type->validateResult($data['_source'])) {

                    if (isset($data['_source']['categories'])) {
                        usort($data['_source']['categories'], function ($catA, $catB) {
                            return count(explode(" > ", $catA)) < count(explode(" > ", $catB));
                        });
                    }
                    if (!empty($data['_source']['categories'])) {
                        $split = explode(" > ", $data['_source']['categories'][0]);
                        $data['_source']['category'] = array_pop($split);
                    }


                    if ($highlight) {
                        $toReplace = explode(" ", $searchTerm);
                        $toReplace2 = [];
                        $replacement = [];

                        $replacementEscapes = [
                            '/' => '\/',
                            '(' => '\(',
                            ')' => '\)',
                            '.' => '\.',
                            ']' => '\]',
                            '[' => '\[',
                        ];

                        foreach ($toReplace as $rp) {
                            if (trim($rp) != "") {
                                $replacement[] = "<b>\\0</b>";
                                $toReplace2[] = "/" . str_replace(array_keys($replacementEscapes), array_values($replacementEscapes), addslashes($rp)) . "/i";
                            }
                        }
                        if ($type->getCode() == "product") {
                            $data['_source']['name'] = preg_replace($toReplace2, $replacement, $data['_source']['name']);
                        }
                        if ($type->getCode() == "category") {
                            $data['_source']['path'] = preg_replace($toReplace2, $replacement, $data['_source']['path']);
                        }
                        if ($type->getCode() == "cms") {
                            $data['_source']['title'] = preg_replace($toReplace2, $replacement, $data['_source']['title']);
                        }
                    }
                    $result[] = $data['_source'];
                }
            }
        }

        return [$result, $suggestsResults];
    }

}
