<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Wyomind\Elasticsearch\Helper\Interfaces\IndexerInterface;
use Wyomind\Elasticsearch\Model\FormatterFactory;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table as SourceTable;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Validator\UniversalFactory;

class Indexer extends Config implements IndexerInterface
{

    /**
     * @var array
     */
    protected $includedAttributes = ['visibility', 'image', 'tax_class_id'];

    /**
     * @var UniversalFactory
     */
    protected $universalFactory;

    /**
     * @var FormatterFactory
     */
    protected $formatterFactory;

    /**
     * @param Context $context
     * @param UniversalFactory $universalFactory
     * @param FormatterFactory $formatterFactory
     */
    public function __construct(
        Context $context,
        UniversalFactory $universalFactory,
        FormatterFactory $formatterFactory
    ) {
    
        parent::__construct($context);
        $this->universalFactory = $universalFactory;
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function formatAttributeValue(EntityAttribute $attribute, $value, $store = null)
    {
        $formatter = $this->formatterFactory->create($attribute);

        return $formatter->format($value, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeIndexable(EntityAttribute $attribute)
    {
        return ($attribute->getData('is_searchable') || $attribute->getData('is_visible_in_advanced_search') || in_array($attribute->getAttributeCode(), $this->includedAttributes));
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeUsingOptions(EntityAttribute $attribute)
    {
        $model = !$attribute->getSourceModel() ? : $this->universalFactory->create($attribute->getSourceModel());
        $backend = $attribute->getBackendType();

        return $attribute->usesSource() && ($backend == 'int' && $model instanceof \Magento\Eav\Model\Entity\Attribute\Source\Table) || ($backend == 'varchar' && $attribute->getFrontendInput() == 'multiselect');
    }
}
