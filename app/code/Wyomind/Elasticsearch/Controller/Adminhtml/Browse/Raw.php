<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Controller\Adminhtml\Browse;

/**
 * Index action (grid)
 */
class Raw extends \Wyomind\Elasticsearch\Controller\Adminhtml\Browse
{

    /**
     * Execute action
     */
    public function execute()
    {
        return $this->getResponse()->representJson($this->getJsonData());
    }

    public function getJsonData()
    {
        $indice = $this->getRequest()->getParam('indice');
        if ($indice == "") {
            $store = $this->dataHelper->getFirstStoreviewCode();
        } else {
            $store = $this->dataHelper->getStoreCode($indice);
        }

        $configHandler = new \Wyomind\Elasticsearch\Autocomplete\Config\JsonHandler($store);
        $config = new \Wyomind\Elasticsearch\Autocomplete\Config($configHandler->load());

        if (!$config->getData()) {
            throw new \Exception('Could not find config for autocomplete');
        }

        $client = new \Wyomind\Elasticsearch\Model\Client(new \Elasticsearch\ClientBuilder, $config, new \Psr\Log\NullLogger());
        $index = $client->getIndexAlias($store, $this->getRequest()->getParam('type'));
        $code = $this->getRequest()->getParam('type');
        $type = new \Wyomind\Elasticsearch\Autocomplete\Index\Type($code, []);

        $data = $client->getByIds($index, $type->getCode(), [$this->getRequest()->getParam('id')]);

        return json_encode($data['docs'][0], JSON_PRETTY_PRINT);
    }
}
