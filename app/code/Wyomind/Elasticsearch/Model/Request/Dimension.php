<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Request;

use Magento\Store\Model\Store;

class Dimension extends \Magento\Framework\Search\Request\Dimension
{

    /**
     * @var bool
     */
    protected $full = false;

    /**
     * @var string
     */
    protected $type = 'product';

    /**
     * Indicates whether indexation is full or not
     *
     * @return bool
     */
    public function isFull()
    {
        return $this->full;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getName() == 'scope' ? $this->getValue() : Store::DEFAULT_STORE_ID;
    }

    /**
     * Returns type of current dimension
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Flags current dimension as full indexation
     *
     * @param bool $bool
     * @return $this
     */
    public function setFull($bool = true)
    {
        $this->full = (bool) $bool;

        return $this;
    }

    /**
     * Defines type of current dimension
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
