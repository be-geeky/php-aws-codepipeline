<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Observer\Config;

use Wyomind\Elasticsearch\Helper\Interfaces\AutocompleteInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AutocompleteInterface
     */
    protected $autocomplete;

    /**
     * @param StoreManagerInterface $storeManager
     * @param AutocompleteInterface $autocomplete
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AutocompleteInterface $autocomplete
    ) {
        $this->storeManager = $storeManager;
        $this->autocomplete = $autocomplete;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->autocomplete->saveConfig($store);
        }
    }
}
