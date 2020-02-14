<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Ui\Component;

/**
 * Description of Actions
 *
 * @author Paul
 */
class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var @param \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    private $_type = 'product';

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_type = $data['type'];
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $indice = $this->context->getFilterParam('indice');

            foreach ($dataSource['data']['items'] as &$item) {
                // display raw data
                $item[$this->getData('name')]['raw'] = [
                    'href' => "javascript:Elasticsearch.raw('" . $this->urlBuilder->getUrl('elasticsearch/browse/raw', ["id" => $item['id'], "indice" => $indice, "type" => $this->_type]) . "');",
                    'label' => __('Raw data'),
                    'hidden' => false,
                ];

                if ($this->_type == "cms") {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'cms/page/edit',
                            ['page_id' => $item['id']]
                        ),
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];
                } elseif ($this->_type == "product") {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $item['id']]
                        ),
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];
                } elseif ($this->_type == "category") {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'catalog/category/edit',
                            ['id' => $item['id']]
                        ),
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
