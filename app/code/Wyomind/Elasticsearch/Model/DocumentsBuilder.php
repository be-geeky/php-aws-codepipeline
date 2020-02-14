<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Index\TypeInterface;
use Magento\Framework\Search\Adapter\Mysql\DocumentFactory;
use Magento\Framework\Search\Document;

class DocumentsBuilder implements DocumentsBuilderInterface
{

    /**
     * Document Factory
     *
     * @var DocumentFactory
     */
    protected $documentFactory;
    protected $magentoVersion = 0;

    /**
     * @param DocumentFactory $documentFactory
     */
    public function __construct(
    DocumentFactory $documentFactory,
            \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->documentFactory = $documentFactory;
        $this->magentoVersion = $coreHelper->getMagentoVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function build(
    array $response,
        TypeInterface $type,
        $catalog = false
    )
    {
        $documents = [];

        $docs = [];
        foreach ($response['hits']['hits'] as $doc) {
            $data = $doc['_source'];
            if ($type->validateResult($data, $catalog)) {
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

        $ids = [];
        $scores = [];
        $docsNew = [];
        foreach ($docs as $id => $score) {
            $docsNew[] = ['id' => $id, 'score' => $score];
            $ids[$id] = $id;
            $scores[$id] = $score;
        }


        array_multisort($scores, SORT_DESC, $ids, SORT_ASC, $docsNew);

        $score = count($docsNew)+1;
        foreach ($docsNew as $info) {
            $documents[] = $this->createDocument($info['id'], $score--);
        }

        return $documents;
    }

    /**
     * @param int $id
     * @param float $score
     * @return Document
     */
    protected function createDocument(
    $id,
            $score
    )
    {
        if (version_compare($this->magentoVersion, "2.1.0") == -1) { // Mage version < 2.1.0
            return $this->documentFactory->create([
                        ['name' => 'entity_id', 'value' => (int) $id],
                        ['name' => 'score', 'value' => $score],
            ]);
        } else {
            return $this->documentFactory->create([
                        'entity_id' => (int) $id,
                        'score' => $score,
            ]);
        }
    }

}
