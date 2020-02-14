<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

interface ClientInterface
{

    /**
     * Creates alias on specified index
     *
     * @param string $index
     * @param string $alias
     * @return mixed
     */
    public function createAlias($index,
            $alias);

    /**
     * Creates specified index with given parameters
     *
     * @param string $index
     * @param array $params
     * @return mixed
     */
    public function createIndex($index,
            array $params = []);

    /**
     * Deletes documents of given type from specified index
     *
     * @param array $ids
     * @param string $index
     * @param string $type
     * @return mixed
     */
    public function delete(array $ids,
            $index,
            $type);

    /**
     * Deletes specified index if exists
     *
     * @param string $index
     * @return mixed
     */
    public function deleteIndex($index);

    /**
     * Indicates whether given alias exists or not
     *
     * @param string $alias
     * @return bool
     */
    public function existsAlias($alias);

    /**
     * Indicates whether given index exists or not
     *
     * @param string $index
     * @return bool
     */
    public function existsIndex($index);

    /**
     * Retrieves multiple documents by ids
     *
     * @param array $indices
     * @param string $type
     * @param array $ids
     * @return array
     */
    public function getByIds($indices,
            $type,
            array $ids = []);

    /**
     * Builds alias from given index name
     *
     * @param string $name
     * @return string
     */
    public function getIndexAlias($name,
            $type = "product");

    /**
     * Returns index name
     *
     * @param string $name
     * @param bool $new
     * @return string
     */
    public function getIndexName($name,
            $new = false,
            $type = "product");

    /**
     * Retrieves indices that belong to specified alias
     *
     * @param string $alias
     * @return array
     */
    public function getIndicesWithAlias($alias);

    /**
     * Indexes documents of given type in specified index
     *
     * @param array $docs
     * @param string $index
     * @param string $type
     * @return mixed
     */
    public function index(array $docs,
            $index,
            $type);

    /**
     * Returns search engine availability
     *
     * @return bool
     */
    public function ping();

    /**
     * Request matching documents of given type in specified index with optional params
     *
     * @param array $indices
     * @param array $types
     * @param array $params
     * @return array
     */
    public function query($indices,
            $types,
            array $params = []);

    /**
     * Updates mapping of given type in specified index with params
     *
     * @param string $index
     * @param string $type
     * @param array $params
     * @return mixed
     */
    public function setMapping($index,
            $type,
            array $params);
}
