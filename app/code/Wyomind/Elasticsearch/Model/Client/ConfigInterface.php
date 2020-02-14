<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Client;

interface ConfigInterface
{
    /**
     * @return int
     */
    public function getConnectTimeout();

    /**
     * @return array
     */
    public function getHosts();

    /**
     * @return string
     */
    public function getIndexPrefix();

    /**
     * @return bool
     */
    public function isVerifyHost();
}
