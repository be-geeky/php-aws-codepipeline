<?php

/* *
 * Copyright Â© 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wyomind\Elasticsearch\Helper\AutocompleteFactory;
use Wyomind\Elasticsearch\Helper\DataFactory;
use Wyomind\Elasticsearch\Helper\ConfigFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Magento\Store\Model\StoreManagerInterface\Proxy as StoreManagerInterface;

/**
 * $ bin/magento help wyomind:elasticsearch:update:config
 * Usage:
 * wyomind:elasticsearch:update:config
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
class Search extends Command
{

    protected $_state = null;
    protected $_storeManager = null;
    protected $_autocompleteHelperFactory = null;
    protected $_dataHelperFactory = null;
    protected $_configHelperFactory = null;

    public function __construct(
    StoreManagerInterface $storeManager,
            AutocompleteFactory $autocompleteHelperrFactory,
            DataFactory $dataHelperFactory,
            ConfigFactory $configHelperFactory,
            \Magento\Framework\App\State $state
    )
    {
        $this->_autocompleteHelperFactory = $autocompleteHelperrFactory;
        $this->_dataHelperFactory = $dataHelperFactory;
        $this->_configHelperFactory = $configHelperFactory;
        $this->_storeManager = $storeManager;
        $this->_state = $state;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:elasticsearch:search')
                ->setDescription(__('Perform the autocomplete search'))
                ->setDefinition([
                    new Inputoption(
                            "storeview", "s", InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, __('The storeview from which to search documents (default: the first storeview in the Magento system)')
                    ),
                    new InputOption(
                            "type", "t", InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, __('The type of documents to search (default: doyoumean, product, category, cms)')
                    ),
                    new InputArgument(
                            "search_term", InputArgument::REQUIRED | InputArgument::IS_ARRAY, __('The search terms to search')
                    ),
        ]);
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
                $this->_state->setAreaCode('front');
            } catch (\Exception $e) {
                
            }
            $storeviews = $input->getOption("storeview");
            if (empty($storeviews)) {
                $storeviews = [""];
            }
            $typeCodes = $input->getOption("type");
            $q = implode(" ", $input->getArgument("search_term"));

            if (empty($typeCodes)) {
                $typeCodes = ["doyoumean", "product", "category", "cms"];
            }
            foreach ($typeCodes as $key => $code) {
                $splitted = explode(",", $code);
                if (count($splitted) > 1) {
                    $typeCodes[$key] = $splitted[0];
                    $count = count($splitted);
                    for ($i = 1; $i < $count; $i++) {
                        $typeCodes[] = $splitted[$i];
                    }
                }
            }
            $typeCodes = array_unique($typeCodes);


            foreach ($storeviews as $storeview) {
                foreach ($typeCodes as $typeCode) {

                    $suggest = $typeCode == "doyoumean";
                    if ($suggest) {
                        $typeCode = "product";
                    }

                    $index = "";
                    $storeCollection = $this->_dataHelperFactory->create()->getAllStoreviews();
                    $config = $this->_configHelperFactory->create();
                    $storeExists = false;

                    foreach ($storeCollection as $store) {
                        if ($storeview == "" || $storeview == $store->getCode() || $storeview == $store->getId()) {
                            $storeExists = true;
                            $configHandler = new \Wyomind\Elasticsearch\Autocomplete\Config\JsonHandler($store->getCode());
                            $config = new \Wyomind\Elasticsearch\Autocomplete\Config($configHandler->load());
                            $client = new \Wyomind\Elasticsearch\Model\Client(new \Elasticsearch\ClientBuilder, $config, new \Psr\Log\NullLogger());
                            if ($config->getData() && $client->existsIndex($config->getIndexPrefix($store) . $store->getCode() . "_" . $typeCode)) {
                                $storeview = $store->getCode();
                                $index = $config->getIndexPrefix($store) . $store->getCode() . "_" . $typeCode;
                                break;
                            }
                        }
                    }
                    $output->writeln("");
                    $output->writeln("<comment>Storeview:     </comment>" . $storeview);
                    $output->writeln("<comment>Document type: </comment>" . ($suggest ? "suggest" : $typeCode));
                    $output->writeln("<comment>Index:         </comment>" . $index);
                    $output->writeln("");

                    if ($index == "") {
                        $output->writeln("");
                        $output->writeln("<error>" . sprintf("%60s", "") . "</error>");
                        if (!$storeExists) {
                            $output->writeln("<error>" . sprintf("%5sCannot find an index for the storeview: %-15s", "", $storeview) . "</error>");
                            $output->writeln("<error>" . sprintf("%5sAllowed values are:%36s", "", "") . "</error>");
                            foreach ($storeCollection as $store) {
                                $output->writeln("<error>" . sprintf("%5s - %-52s", "", $store->getCode() . " / " . $store->getId()) . "</error>");
                            }
                        } else {
                            $output->writeln("<error>" . sprintf("%5sCannot find an index for the type: %-20s", "", $typeCode) . "</error>");
                            $output->writeln("<error>" . sprintf("%5sAllowed values are:%36s", "", "") . "</error>");
                            $output->writeln("<error>" . sprintf("%5s - doyoumean%-45s", "", "") . "</error>");
                            $output->writeln("<error>" . sprintf("%5s - product%-45s", "", "") . "</error>");
                            $output->writeln("<error>" . sprintf("%5s - category%-44s", "", "") . "</error>");
                            $output->writeln("<error>" . sprintf("%5s - cms%-49s", "", "") . "</error>");
                        }
                        $output->writeln("<error>" . sprintf("%60s", "") . "</error>");
                        $output->writeln("");
                    } else {

                        $configHandler = new \Wyomind\Elasticsearch\Autocomplete\Config\JsonHandler($storeview);
                        $config = new \Wyomind\Elasticsearch\Autocomplete\Config($configHandler->load());
                        $settings = $config->getTypes()[$typeCode];
                        $index = $client->getIndexAlias($storeview, $typeCode);
                        $type = new \Wyomind\Elasticsearch\Autocomplete\Index\Type($typeCode, $settings);
                        $params = (new \Wyomind\Elasticsearch\Model\QueryBuilder($config))->build($q, $type);
                        $client = new \Wyomind\Elasticsearch\Model\Client(new \Elasticsearch\ClientBuilder, $config, new \Psr\Log\NullLogger());
                        $search = new \Wyomind\Elasticsearch\Autocomplete\Search($client, $config->getCompatibility());
                        list($docs, $suggests) = $search->query($index, $type, $params, false);

                        if ($suggest) {
                            $docs = [];
                            foreach ($suggests as $sugg) {
                                $docs[] = ["term" => $sugg];
                            }
                            $typeCode = "suggest";
                        }

                        $output->writeln("");
                        $output->writeln("<info>" . sprintf("%d documents found", count($docs)) . "</info>");

                        $columns = [
                            "product" => ['id', 'sku', 'type_id', 'name', 'status', 'tax_class_id', 'visibility', 'price', 'url'],
                            "category" => ['id', 'name', 'path'],
                            "cms" => ['id', 'identifier', 'title', 'content_heading'],
                            "doyoumean" => ['term'],
                        ];

                        $table = $this->getHelperSet()->get('table');
                        $table->setHeaders($columns[$typeCode]);
                        $table->setRows([]);
                        foreach ($docs as $doc) {

                            $data = [];
                            foreach ($columns[$typeCode] as $column) {
                                if (isset($doc[$column])) {
                                    $data[] = $doc[$column];
                                } else {
                                    $data[] = "";
                                }
                            }
                            $table->addRow($data);
                        }

                        $table->render($output);
                    }
                }
            }
            $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }


        return $returnValue;
    }

}
