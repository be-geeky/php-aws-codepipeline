<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Tax\Model\TaxCalculation;
use Magento\Tax\Model\ClassModel as TaxClass;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection as TaxClassCollection;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory as TaxClassCollectionFactory;

class Tax extends AbstractHelper
{

    /**
     * @var TaxClassCollectionFactory
     */
    protected $taxClassCollectionFactory;

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @param Context $context
     * @param TaxClassCollectionFactory $taxClassCollectionFactory
     * @param TaxCalculation $taxCalculation
     * @param TaxConfig $taxConfig
     */
    public function __construct(
        Context $context,
        TaxClassCollectionFactory $taxClassCollectionFactory,
        TaxCalculation $taxCalculation,
        TaxConfig $taxConfig
    ) {
    
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        parent::__construct($context);
    }

    /**
     * @return TaxClassCollection
     */
    protected function getProductTaxClasses()
    {
        return $this->taxClassCollectionFactory->create()
            ->setClassTypeFilter(TaxClass::TAX_CLASS_TYPE_PRODUCT);
    }

    /**
     * @param mixed $store
     * @return array
     */
    public function getRates($store = null)
    {
        $rates = [];
        foreach ($this->getProductTaxClasses() as $taxClass) {
            /** @var TaxClass $taxClass */
            $rates[$taxClass->getId()] = $this->taxCalculation->getCalculatedRate(
                $taxClass->getId(),
                null,
                $store
            );
        }

        return $rates;
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function needPriceConversion($store = null)
    {
        $priceIncludesTax = $this->priceIncludesTax($store);
        $priceDisplayType = $this->taxConfig->getPriceDisplayType($store);

        if ($priceIncludesTax) {
            return $priceDisplayType == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
        }

        return $priceDisplayType == TaxConfig::DISPLAY_TYPE_INCLUDING_TAX
            || $priceDisplayType == TaxConfig::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function priceIncludesTax($store = null)
    {
        return $this->taxConfig->priceIncludesTax($store);
    }
}
