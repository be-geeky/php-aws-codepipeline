<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Request\Dimension;
use Magento\Framework\Search\RequestInterface;

interface AdapterInterface extends \Magento\Framework\Search\AdapterInterface
{

    /**
     * @param Dimension $dimension
     * @param \Traversable $documents
     */
    public function addDocs(Dimension $dimension, \Traversable $documents);

    /**
     * @param Dimension $dimension
     * @param array $ids
     */
    public function deleteDocs(Dimension $dimension, array $ids);

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function ping($storeId = null);

    /**
     * @param RequestInterface $request
     * @param string $type
     * @return array
     */
    public function request(RequestInterface $request, $type = 'product');
}
