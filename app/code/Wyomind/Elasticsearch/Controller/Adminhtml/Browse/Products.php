<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Controller\Adminhtml\Browse;

/**
 * Index action (grid)
 */
class Products extends \Wyomind\Elasticsearch\Controller\Adminhtml\Browse
{

    /**
     * Does the menu is allowed
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_Elasticsearch::products');
    }

    /**
     * Execute action
     */
    public function execute()
    {


        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system");
        $resultPage->getConfig()->getTitle()->prepend(__('Elasticsearch > Browse Products'));

        return $resultPage;
    }
}
