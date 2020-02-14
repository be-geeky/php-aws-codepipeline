<?php

namespace Alogic\Indexer\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory as IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\SimpleProductPrice as CoreSimpleProductPrice;

/**
 * Simple Product Type Price Indexer
 */
class SimpleProductPrice extends CoreSimpleProductPrice {

/**
 * @var BaseFinalPrice
 */
	private $baseFinalPrice;

	/**
	 * @var IndexTableStructureFactory
	 */
	private $indexTableStructureFactory;

	/**
	 * @var TableMaintainer
	 */
	private $tableMaintainer;

	/**
	 * @var string
	 */
	private $productType;

	/**
	 * @var BasePriceModifier
	 */
	private $basePriceModifier;

	/**
	 * @param BaseFinalPrice $baseFinalPrice
	 * @param IndexTableStructureFactory $indexTableStructureFactory
	 * @param TableMaintainer $tableMaintainer
	 * @param BasePriceModifier $basePriceModifier
	 * @param string $productType
	 */
	public function __construct(
		BaseFinalPrice $baseFinalPrice,
		IndexTableStructureFactory $indexTableStructureFactory,
		TableMaintainer $tableMaintainer,
		BasePriceModifier $basePriceModifier,
		$productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
	) {
		$this->baseFinalPrice = $baseFinalPrice;
		$this->indexTableStructureFactory = $indexTableStructureFactory;
		$this->tableMaintainer = $tableMaintainer;
		$this->productType = $productType;
		$this->basePriceModifier = $basePriceModifier;
		parent::__construct($baseFinalPrice, $indexTableStructureFactory, $tableMaintainer, $basePriceModifier, $productType);
	}
	/**
	 * {@inheritdoc}
	 */
	public function executeByDimensions(array $dimensions, \Traversable $entityIds) {
		$this->tableMaintainer->createMainTmpTable($dimensions);

		$temporaryPriceTable = $this->indexTableStructureFactory->create([
			'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
			'entityField' => 'entity_id',
			'customerGroupField' => 'customer_group_id',
			'websiteField' => 'website_id',
			'taxClassField' => 'tax_class_id',
			'originalPriceField' => 'price',
			'finalPriceField' => 'final_price',
			'minPriceField' => 'min_price',
			'maxPriceField' => 'max_price',
			'tierPriceField' => 'tier_price',
		]);

		$select = $this->baseFinalPrice->getQuery($dimensions, $this->productType, iterator_to_array($entityIds));
		$query = $select->insertFromSelect($temporaryPriceTable->getTableName(), [], true);
		$this->tableMaintainer->getConnection()->query($query);

		$this->basePriceModifier->modifyPrice($temporaryPriceTable, iterator_to_array($entityIds));
	}
}