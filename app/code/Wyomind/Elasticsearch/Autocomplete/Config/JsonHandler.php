<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Autocomplete\Config;

class JsonHandler implements HandlerInterface
{

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $scope
     * @param string $path
     */
    public function __construct($scope = '', $path = '')
    {
        if ($path == '') {
            $path = BP . '/var/elasticsearch/';
        }
        $this->setScope($scope);
        $this->setPath(str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        if (!file_exists($this->path)) {
            @mkdir($this->path, 0777, true);
        }

        return $this->path . $this->scope . '.json';
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $file = $this->getFileName();

        if (!file_exists($file) || !is_file($file) || !filesize($file)) {
            throw new \Exception(sprintf('Could not find config file for scope "%s"', $this->scope));
        }

        return @json_decode(file_get_contents($file), true);
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $config)
    {
        return @file_put_contents($this->getFileName(), json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }
}
