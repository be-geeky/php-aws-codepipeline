<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Ui\Component\Products;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'image';

    const ALT_FIELD = 'name';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['image'])) {
                    $item[$fieldName . '_src'] = $item['image'];
                    $item[$fieldName . '_alt'] = $item['image'];
                    $item[$fieldName . '_link'] = $item['image'];
                    $item[$fieldName . '_orig_src'] = $item['image'];
                } else {
                    $item[$fieldName . '_src'] = "";
                    $item[$fieldName . '_alt'] = "";
                    $item[$fieldName . '_link'] = "";
                    $item[$fieldName . '_orig_src'] = "";
                }
            }
        }

        return $dataSource;
    }
}
