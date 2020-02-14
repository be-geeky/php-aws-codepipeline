<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Controller\Adminhtml\History;

class Purge extends \Wyomind\Watchlog\Controller\Adminhtml\History
{

    public function execute()
    {
        $this->history->purge();
        return $this->resultRedirectFactory->create()->setPath('watchlog/attempts/'.$this->getRequest()->getParam('previous'));
    }
}
