<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Controller\Adminhtml;

abstract class History extends \Magento\Backend\App\Action
{

    public $resultRedirectFactory = null;
    public $history = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Wyomind\Watchlog\Cron\History $history
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->history = $history;
        parent::__construct($context);
    }

    /**
     * Does the menu is allowed
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_Watchlog::attempts');
    }

    abstract public function execute();
}
