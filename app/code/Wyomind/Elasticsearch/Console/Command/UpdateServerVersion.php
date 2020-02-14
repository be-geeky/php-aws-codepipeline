<?php

/* *
 * Copyright Â© 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\StoreManagerInterface\Proxy as StoreManagerInterface;

/**
 * $ bin/magento help wyomind:elasticsearch:update:server:version
 * Usage:
 * wyomind:elasticsearch:updateconfig
 *
 * Options:
 * --help (-h)           Display this help message
 * --quiet (-q)          Do not output any message
 * --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * --version (-V)        Display this application version
 * --ansi                Force ANSI output
 * --no-ansi             Disable ANSI output
 * --no-interaction (-n) Do not ask any interactive question
 */
class UpdateServerVersion extends Command
{

    protected $_state = null;
    protected $_storeManager = null;
    protected $_coreHelperFactory = null;

    public function __construct(
    StoreManagerInterface $storeManager,
            \Magento\Framework\App\State $state,
            \Wyomind\Core\Helper\DataFactory $coreHelperFactory
    )
    {
        $this->_storeManager = $storeManager;
        $this->_state = $state;
        $this->_coreHelperFactory = $coreHelperFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:elasticsearch:update:server:version')
                ->setDescription(__('Update the compatibility mode according to the ES server version'))
                ->setDefinition([]);
        parent::configure();
    }

    protected function execute(
    InputInterface $input,
            OutputInterface $output
    )
    {

        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            try {
                $this->_state->setAreaCode('adminhtml');
            } catch (\Exception $e) {
                
            }
            $output->writeln("");

            // global scope
            
            $output->writeln("Default Scope");
            
            $coreHelper = $this->_coreHelperFactory->create();
            
            $hosts = explode(',', $coreHelper->getDefaultConfig("catalog/search/elasticsearch/servers"));
            foreach ($hosts as $host) {
                $test = \Elasticsearch\ClientBuilder::create()->setHosts([$host])->build();
                try {
                    $info = $test->info([ "client" => ["verify" => false, "connect_timeout" => 5]]);
                    $version = explode(".", $info['version']['number']);
                    $version = array_shift($version);
                    if (in_array($version, [2, 5, 6])) {
                        $coreHelper->setDefaultConfig("catalog/search/elasticsearch/version", $info['version']['number']);
                        $coreHelper->setDefaultConfig("catalog/search/elasticsearch/compatibility", $version);
                        $output->writeln("<comment>" . __("Elasticsearch server version found: ") . $info['version']['number'] . "</comment>");
                    } else {
                        $coreHelper->setDefaultConfig("catalog/search/elasticsearch/version", $info['version']['number']);
                        $coreHelper->setDefaultConfig("catalog/search/elasticsearch/compatibility", 6);
                        $output->writeln("<error>" . __("Elasticsearch server version found not compatible: ") . $info['version']['number'] . "</error>");
                    }
                } catch (\Exception $e) {
                    $coreHelper->setDefaultConfig("catalog/search/elasticsearch/version", "");
                    $coreHelper->setDefaultConfig("catalog/search/elasticsearch/compatibility", 6);
                    $output->writeln("<error>" . __("Cannot find the Elasticsearch server version: ") . $e->getMessage() . "</error>");
                }
            }

            foreach ($this->_storeManager->getStores() as $store) {
                $output->writeln(sprintf("Store %s (%s)",$store['name'], $store['code']));
                
                $hosts = explode(',', $coreHelper->getStoreConfig("catalog/search/elasticsearch/servers", $store->getStoreId()));
                foreach ($hosts as $host) {
                    $test = \Elasticsearch\ClientBuilder::create()->setHosts([$host])->build();
                    try {
                        $info = $test->info([ "client" => ["verify" => false, "connect_timeout" => 5]]);
                        $version = explode(".", $info['version']['number']);
                        $version = array_shift($version);
                        if (in_array($version, [2, 5, 6])) {
                            $coreHelper->setStoreConfig("catalog/search/elasticsearch/version", $info['version']['number'], $store->getStoreId());
                            $coreHelper->setStoreConfig("catalog/search/elasticsearch/compatibility", $version, $store->getStoreId());
                            $output->writeln("<comment>" . __("Elasticsearch server version found: ") . $info['version']['number'] . "</comment>");
                        } else {
                            $coreHelper->setStoreConfig("catalog/search/elasticsearch/version", $info['version']['number'], $store->getStoreId());
                            $coreHelper->setStoreConfig("catalog/search/elasticsearch/compatibility", 6, $store->getStoreId());
                            $output->writeln("<error>" . __("Elasticsearch server version found not compatible: ") . $info['version']['number'] . "</error>");
                        }
                    } catch (\Exception $e) {
                        $coreHelper->setStoreConfig("catalog/search/elasticsearch/version", "", $store->getStoreId());
                        $coreHelper->setStoreConfig("catalog/search/elasticsearch/compatibility", 6, $store->getStoreId());
                        $output->writeln("<error>" . __("Cannot find the Elasticsearch server version: ") . $e->getMessage() . "</error>");
                    }
                }
                $output->writeln("");
            }
            
            
            $output->writeln("<info>".__("Please run:")."</info>"); 
            $output->writeln("");
            $output->writeln("   bin/magento wyomind:elasticsearch:update:config");
            $output->writeln("");
            $output->writeln("<info>".__("to update the autocomplete configuration file")."</info>");
            $output->writeln("");
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }


        return $returnValue;
    }

}
