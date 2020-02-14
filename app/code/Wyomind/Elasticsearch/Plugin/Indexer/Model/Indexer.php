<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Plugin\Indexer\Model;

class Indexer
{

    protected $elasticsearch = false;
    protected $engine = "";
    protected $output = null;

    public function __construct(
    \Wyomind\Elasticsearch\Helper\Config $config,
            \Symfony\Component\Console\Output\ConsoleOutput $output
    )
    {
        $this->output = $output;
        $this->elasticsearch = $config->getEngine() == "elasticsearch";
        $this->engine = $config->getEngine();
    }

    public function afterGetActionClass(\Magento\Indexer\Model\Indexer $subject,
            $actionClass)
    {
        if (php_sapi_name() === "cli") {
            $this->output->writeln("<comment>Using ".$this->engine." search engine</comment>");
        }
        if ($subject->getId() == "catalogsearch_fulltext" && !$this->elasticsearch) {
            $actionClass = "\\Magento\\CatalogSearch\\Model\\Indexer\\Fulltext";
        } elseif ($subject->getId() == "catalogsearch_fulltext" && $this->elasticsearch) {
        }
        return $actionClass;
    }

}
