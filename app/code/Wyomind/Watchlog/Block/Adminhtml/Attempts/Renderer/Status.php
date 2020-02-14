<?php

namespace Wyomind\Watchlog\Block\Adminhtml\Attempts\Renderer;

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $type = "notice";
        $inner = "SUCCESS";
        switch ($row->getStatus()) {
            case \Wyomind\Watchlog\Helper\Data::FAILURE;
                $type = 'major';
                $inner = "FAILURE";
                break;
        }
        return "<span class='grid-severity-" . $type . "'>" . $inner . "</span>";
    }
}
