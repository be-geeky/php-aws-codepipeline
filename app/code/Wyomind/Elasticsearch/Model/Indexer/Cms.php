<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Indexer;

use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;

class Cms extends AbstractIndexer
{

    /**
     * @return PageCollection
     */
    protected function createPageCollection()
    {
        return $this->objectManager->create(PageCollection::class);
    }

    /**
     * @param int $storeId
     * @param array $ids
     * @return \Generator
     */
    public function export(
    $storeId,
            $ids = []
    )
    {

        $this->handleLog("");
        $this->handleLog("<comment>Indexing cms pages for store id: " . $storeId."</comment>");

        $this->eventManager->dispatch('wyomind_elasticsearch_cms_export_before', [ 'store_id' => $storeId, 'ids' => $ids]);

        $pages = [];

        $attributesConfig = $this->indexerHelper->getEntitySearchableAttributes('cms', $storeId);
        $collection = $this->createPageCollection()
                ->addStoreFilter($storeId);
        
        if (count(array_filter($attributesConfig)) != 0) {
            $collection->addFieldToSelect($attributesConfig);
        }

        if ($excluded = $this->indexerHelper->getExcludedPageIds($storeId)) {
            $collection->addFieldToFilter('page_id', ['nin' => $excluded]);
        }
        $collection->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);


        $this->handleLog("<info>" . count($collection) . " cms pages found</info>");
        /** @var \Magento\Cms\Model\Page $page */
        foreach ($collection as $page) {
            $page->setContent(html_entity_decode($page->getContent()));
            $pages[$page->getId()] = array_merge(
                    [\Wyomind\Elasticsearch\Helper\Config::CMS_ID => (int)$page->getId()], $page->toArray($attributesConfig)
            );
            if (isset($pages[$page->getId()]['title'])) {
                $pages[$page->getId()]['title_suggester'] = $pages[$page->getId()]['title'];
            }
        }
        $this->handleLog("<info>" . count($collection) . " cms pages indexed</info>");
        $this->handleLog("");

        yield $pages;

        $this->eventManager->dispatch('wyomind_elasticsearch_cms_export_after', [ 'store_id' => $storeId, 'ids' => $ids]);
    }

}
