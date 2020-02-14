<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\System\Config\Source\Attribute;

use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Option\ArrayInterface;

class Cms implements ArrayInterface
{

    /**
     * @var array
     */
    protected $allowedTypes = [
        'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'
    ];

    /**
     * @var PageResource
     */
    protected $pageResource;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @param PageResource $pageResource
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        PageResource $pageResource,
        EventManagerInterface $eventManager
    ) {
    
        $this->pageResource = $pageResource;
        $this->eventManager = $eventManager;
    }

    /**
     * Return list of searchable attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $tableInfo = $this->pageResource
                ->getConnection()
                ->describeTable($this->pageResource->getMainTable());

        foreach ($tableInfo as $field => $info) {
            if (in_array($info['DATA_TYPE'], $this->allowedTypes) &&
                    $field != 'layout_update_xml' &&
                    substr($field, 0, 7) !== 'custom_') {
                $options[$field] = [
                    'value' => $field,
                    'label' => ucwords(strtr($field, '_-', '  ')),
                ];
            }
        }

        $this->eventManager->dispatch('wyomind_elasticsearch_cms_attributes', [
            'attributes' => $options
        ]);

        ksort($options);

        return $options;
    }
}
