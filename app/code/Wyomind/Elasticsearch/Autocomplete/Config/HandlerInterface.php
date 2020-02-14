<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Autocomplete\Config;

interface HandlerInterface
{
    /**
     * @return array
     */
    public function load();

    /**
     * @param array $config
     * @return mixed
     */
    public function save(array $config);

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope);
}
