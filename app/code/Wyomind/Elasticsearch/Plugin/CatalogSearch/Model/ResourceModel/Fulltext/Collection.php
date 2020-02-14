<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\ElasticSearch\Plugin\CatalogSearch\Model\ResourceModel\Fulltext;

class Collection
{

    private $_session = null;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session
    )
    {
        $this->_session = $session;
    }

    public function aroundGetSelect($subject,
                                    $proceed)
    {
        $select = $proceed();
        if ($this->_session->getElasticSearchAlreadyProcessed()) {
            return $select;
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $om->get("Magento\Framework\App\Request\Http");

        $dir = $request->getParam("product_list_dir");
        $field = $request->getParam("product_list_order");
        if (!$field) {
            $field = "relevance";
            switch ($request->getFullActionName()) {
                case "catalogsearch_result_index":
                    $field = "relevance";
                    break;
                default:
                    $field = "position";
            }
        }
        if (!$dir) {
            $dir = "asc";
        }
        if ($field == "relevance") {
            $subject->setOrder($field, $dir);
        }

        if ($field == "name") {
            if (!$subject->isEnabledFlat()) {
                $subject->addAttributeToSelect("name");
            }
            $select->order(
                $field . ' ' . $dir
            );
        }

        $this->_session->setElasticSearchAlreadyProcessed(true);
        return $select;
    }

}
