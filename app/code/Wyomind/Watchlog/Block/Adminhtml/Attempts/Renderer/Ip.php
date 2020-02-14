<?php

namespace Wyomind\Watchlog\Block\Adminhtml\Attempts\Renderer;

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

class Ip extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        return "<a target='_blank' href='http://www.abuseipdb.com/check/".$row->getIp()."' title='".__('Check this ip')."'>".$row->getIp()."</a>";
    }
}
