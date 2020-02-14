<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Wyomind\Elasticsearch\Model\AdapterInterface;
use Wyomind\Elasticsearch\Model\Request\Dimension;

class IndexerHandler implements IndexerInterface {

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var AdapterInterface
	 */
	protected $adapter;

	/**
	 * @var Batch
	 */
	protected $batch;

	/**
	 * @var int
	 */
	protected $batchSize;

	protected $eventManager;

	/**
	 * @param array $data
	 * @param AdapterInterface $adapter
	 * @param Batch $batch
	 * @param type $batchSize
	 */
	public function __construct(
		array $data,
		AdapterInterface $adapter,
		Batch $batch,
		\Magento\Framework\Event\ManagerInterface $eventManager,
		$batchSize = 500
	) {

		$this->data = $data;
		$this->adapter = $adapter;
		$this->batch = $batch;
		$this->batchSize = $batchSize;
		$this->eventManager = $eventManager;
	}

	public function __toString() {
		return "Elasticsearch IndexerHandler";
	}

	/**
	 * Remove all data from index of a specific type
	 *
	 * @param Dimension[] $dimensions
	 * @return $this
	 */
	public function cleanIndex($dimensions) {
		$this->eventManager->dispatch('wyomind_elasticsearch_indexer_clean_before', ["dimensions" => $dimensions]);
		foreach ($dimensions as $dimension) {
			$this->adapter->deleteDocs($dimension, []);
		}
		$this->eventManager->dispatch('wyomind_elasticsearch_indexer_clean_after', []);
		return $this;
	}

	/**
	 * Remove entities data from index
	 *
	 * @param Dimension[] $dimensions
	 * @param \Traversable $documents
	 * @return $this
	 */
	public function deleteIndex($dimensions,
		\Traversable $documents) {
		$this->eventManager->dispatch('wyomind_elasticsearch_indexer_delete_before', ["dimensions" => $dimensions]);
		foreach ($dimensions as $dimension) {
			foreach ($this->batch->getItems($documents, $this->batchSize) as $docIds) {
				$this->adapter->deleteDocs($dimension, $docIds);
			}
		}
		$this->eventManager->dispatch('wyomind_elasticsearch_indexer_delete_after', []);

		return $this;
	}

	/**
	 * Define if engine is available
	 *
	 * @return bool
	 */
	public function isAvailable($dimensions = []) {
		return $this->adapter->ping();
	}

	/**
	 * Add entities data to index
	 *
	 * @param Dimension[] $dimensions
	 * @param \Traversable $documents
	 * @return $this
	 */
	public function saveIndex(
		$dimensions,
		\Traversable $documents
	) {

		$this->eventManager->dispatch('wyomind_elasticsearch_indexer_save_before', ["dimensions" => $dimensions]);
		foreach ($dimensions as $dimension) {
			$this->adapter->addDocs($dimension, $documents);
		}
		$this->eventManager->dispatch('wyomind_elasticsearch_indexer_save_after', []);

		return $this;
	}

}
