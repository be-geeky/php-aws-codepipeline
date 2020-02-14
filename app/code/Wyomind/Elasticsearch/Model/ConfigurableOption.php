<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

class ConfigurableOption extends \Magento\Framework\Model\AbstractModel
{


    public function _construct()
    {
        $this->_init('Wyomind\Elasticsearch\Model\ResourceModel\ConfigurableOption');
    }
}
