<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\ResourceModel\ConfigurableOption;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Wyomind\Elasticsearch\Model\ConfigurableOption', 'Wyomind\Elasticsearch\Model\ResourceModel\ConfigurableOption');
    }
    
    
    public function getConfigurableOptions()
    {

        /*
          SELECT cpsa.product_id, ea.attribute_code, group_concat(cpia.value) as `values`
          FROM `catalog_product_super_attribute` `cpsa`
          LEFT JOIN catalog_product_index_eav AS cpia
          ON     cpsa.attribute_id = cpia.attribute_id
          AND `cpsa`.product_id = cpia.entity_id
          LEFT JOIN eav_attribute AS ea ON ea.attribute_id = cpsa.attribute_id
          GROUP BY cpsa.product_id, cpia.attribute_id
         */

        $connection = $this->_resource;
        $catalogProductIndexEav = $connection->getTable('catalog_product_index_eav');
        $eavAttribute = $connection->getTable('eav_attribute');

        $this->getSelect()->reset("columns");
        $this->getSelect()->columns(['main_table.product_id', 'ea.attribute_code']);
        
        $this->getSelect()->joinLeft(["cpia" => $catalogProductIndexEav], "main_table.attribute_id = cpia.attribute_id AND main_table.product_id = cpia.entity_id", []);
        $this->getSelect()->joinLeft(["ea" => $eavAttribute], "ea.attribute_id = main_table.attribute_id", []);
        
        $this->getSelect()->group(['main_table.product_id', 'cpia.attribute_id']);
        
        return $this;
    }
}
