<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
define('DS', DIRECTORY_SEPARATOR);
define('BP', __DIR__);

require BP . DS . 'vendor' . DS . 'autoload.php';

use Wyomind\Elasticsearch\Autocomplete\Config;
use Wyomind\Elasticsearch\Autocomplete\Search;
use Wyomind\Elasticsearch\Autocomplete\Index\Type;
use Wyomind\Elasticsearch\Model\Client;
use Wyomind\Elasticsearch\Model\QueryBuilder;
use Elasticsearch\ClientBuilder;
use Psr\Log\NullLogger;

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$result = [];
$q = isset($_GET['q']) ? $_GET['q'] : '';
$found = false;

$enableDebugMode = 0;

if ('' !== $q) {
    try {
        $store = isset($_GET['store']) ? $_GET['store'] : '';
        
        try {
            $configHandler = new Config\JsonHandler($store);
            $config = new Config($configHandler->load());   
            
        } catch (\Exception $e) {
            $enableDebugMode = 1;
            throw $e;
        }

        if (!$config->getData()) {
            $enableDebugMode = 1;
            throw new \Exception('Could not find config for autocomplete');
        }

        $enableDebugMode = $config->getEnableDebugMode();

        $client = new Client(new ClientBuilder, $config, new NullLogger());
        $search = new Search($client, $config->getCompatibility());

        $typesConfig = $config->getTypes();
        foreach ($typesConfig as $code => $settings) {
            if ($code == "doyoumean") {
                continue;
            }
            try {
                if ($settings['enable_autocomplete'] == 1) {
                    $type = new Type($code, $settings);
                    $params = (new QueryBuilder($config))->build($q, $type);
                    $index = $client->getIndexAlias($store, $code);
                    list($docs, $suggests) = $search->query($index, $type, $params);
                    $limit = $settings['autocomplete_limit'];
                    $result[$code] = [
                        'count' => count($docs),
                        'docs' => array_slice($docs, 0, $limit),
                        'enabled' => $settings['enable_autocomplete'] && ($code == "product" || ($code != "product" && $settings['enable']))
                    ];
                    if ($code == "product" && $typesConfig['doyoumean']['enable_autocomplete']) {
                        $result["suggests"] = [
                            'count' => count($suggests),
                            'docs' => $suggests,
                            'enabled' => $typesConfig['doyoumean']['enable_autocomplete']
                        ];
                    } else if (!$typesConfig['doyoumean']['enable_autocomplete']) {
                        $result["suggests"] = [
                            'count' => 0,
                            'docs' => [],
                            'enabled' => 0
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore results of current type if an exception is thrown when searching
                $result[$code] = [];
                if ($enableDebugMode) {
                    $result["error"][] = $e->getMessage();
                }
            }
        }

        $found = true;
    } catch (\Exception $e) {
        if ($enableDebugMode) {
            $result["error"][] = $e->getMessage();
        }
        $result["suggests"] = [ 'count' => 0];
        $result["product"] = [ 'count' => 0];
        $result["category"] = [ 'count' => 0];
        $result["cms"] = [ 'count' => 0];
        header('Fast-Autocomplete-error: ' . $e->getMessage());
    }
}

header('Fast-Autocomplete: ' . ($found ? 'HIT' : 'MISS'));

echo json_encode($result);
