<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Index;

interface TypeInterface
{

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param mixed $store
     * @return string
     */
    public function getLanguageAnalyzer($store = null);

    /**
     * @param mixed $store
     * @param bool $withBoost
     * @return array
     */
    public function getProperties(
    $store = null,
            $withBoost = false
    );

    /**
     * @param string $q
     * @param mixed $store
     * @param bool $withBoost
     * @return array
     */
    public function getSearchFields(
    $q,
            $store = null,
            $withBoost = false,
            $compatibility = null
    );

    /**
     * @param mixed $store
     * @return bool
     */
    public function isEnabled($store = null);

    /**
     * @param array $data
     * @return bool
     */
    public function validateResult(array $data);
}
