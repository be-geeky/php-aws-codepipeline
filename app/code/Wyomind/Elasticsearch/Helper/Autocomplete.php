<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Wyomind\Elasticsearch\Autocomplete\Config\HandlerInterface as ConfigHandlerInterface;
use Wyomind\Elasticsearch\Helper\Interfaces\AutocompleteInterface;
use Wyomind\Elasticsearch\Model\Index\MappingBuilderInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Theme\Model\View\Design;

class Autocomplete extends Config implements AutocompleteInterface
{

    /**
     * @var MappingBuilderInterface
     */
    protected $mappingBuilder;

    /**
     * @var ConfigHandlerInterface
     */
    protected $configHandler;

    protected $theme = "";
    
    /**
     * @param Context $context
     * @param MappingBuilderInterface $mappingBuilder
     * @param ConfigHandlerInterface $configHandler
     */
    public function __construct(
    Context $context,
            MappingBuilderInterface $mappingBuilder,
            ConfigHandlerInterface $configHandler,
            Design $design
    )
    {

        $this->mappingBuilder = $mappingBuilder;
        $this->configHandler = $configHandler;
        $this->theme = $design->getDesignParams()['themeModel']->getCode();
        
        parent::__construct($context);
    }
    
    public function isInfortisUltimo() {
        return $this->theme == "Infortis/ultimo";
    }
    
    public function isAlothemesMilano() {
        return $this->theme == "Alothemes/milano";
    }
    
    /**
     * @param StoreInterface $store
     * @return array
     */
    public function getConfig(StoreInterface $store)
    {
        $config = [
            'client_config' => $this->getClientConfig($store),
            'config' => $this->getValue('elasticsearch', $store),
        ];

        $types = & $config['config']['types'];
        foreach ($this->mappingBuilder->getTypes() as $code => $type) {
            if (isset($types[$code])) {
                $types[$code]['index_properties'] = $type->getProperties($store, true);
            }
        }

        return $config;
    }

    public function getAutocompleteJsTemplate($store = null)
    {
        return $this->getValue('elasticsearch/autocomplete/advanced/js_template', $store);
    }

    public function getAutocompleteCssRules($store = null)
    {
        return $this->getValue('elasticsearch/autocomplete/advanced/css_rules', $store);
    }

    public function getCompatibility($store = null)
    {
        return $this->getValue('elasticsearch/autocomplete/advanced/compatibility', $store);
    }

    /**
     * @param string $entity
     * @param mixed $store
     * @return string
     */
    public function getTemplate($entity,
            $store = null)
    {
        if ($this->getValue(sprintf('elasticsearch/types/%s/enable_autocomplete', $entity), $store)) {
            $path = sprintf('elasticsearch/types/%s/autocomplete_template', $entity);
            return $this->getValue($path, $store);
        } else {
            return "";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveConfig(StoreInterface $store)
    {
        $config = new DataObject($this->getConfig($store));

        $this->_eventManager->dispatch('wyomind_elasticsearch_autocomplete_save_config_before', [
            'config' => $config,
        ]);

        $this->configHandler
                ->setScope($store->getCode())
                ->save($config->getData());

        $this->_eventManager->dispatch('wyomind_elasticsearch_autocomplete_save_config_after', []);
    }

}
