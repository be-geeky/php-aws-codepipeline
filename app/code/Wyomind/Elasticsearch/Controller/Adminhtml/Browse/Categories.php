<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Controller\Adminhtml\Browse;

/**
 * Index action (grid)
 */
class Categories extends \Wyomind\Elasticsearch\Controller\Adminhtml\Browse
{

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_Elasticsearch::categories');
    }

    /**
     * Execute action
     */
    public function execute()
    {


        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system");
        $resultPage->getConfig()->getTitle()->prepend(__('Elasticsearch > Browse Categories'));

        return $resultPage;
    }
}
