<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Block\Adminhtml\Attempts;

/**
 * Data feed grid container
 */
class Advanced extends \Magento\Backend\Block\Widget\Grid\Container
{
    
    public $watchlogHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Wyomind\Watchlog\Helper\Data $watchlogHelper,
        array $data = []
    ) {
        $this->watchlogHelper = $watchlogHelper;
        $this->watchlogHelper->checkWarning();
        parent::__construct($context, $data);
    }
    
    /**
     * Block constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_attempts_advanced';
        $this->_blockGroup = 'Wyomind_Watchlog';
        $this->_headerText = __('Watchlog > Login Attempts');
        $this->setTemplate('advanced.phtml');
        parent::_construct();
        $this->buttonList->remove('add');
    }
    
    public function isPeriodicalReportEnabled()
    {
        return $this->watchlogHelper->getDefaultConfig("watchlog/periodical_report/enable_reporting");
    }
}
