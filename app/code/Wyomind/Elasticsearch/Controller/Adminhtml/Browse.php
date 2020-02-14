<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Controller\Adminhtml;

abstract class Browse extends \Magento\Backend\App\Action
{

    public $coreHelper = null;
    public $dataHelper = null;
    public $resultPageFactory = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Model\Context $contextModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\SimpleGoogleShopping\Helper\Data $sgsHelper
     * @param \Wyomind\SimpleGoogleShopping\Model\Feeds $sgsModel
     * @param \Magento\Framework\Model\Context $context_
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Wyomind\SimpleGoogleShopping\Helper\Parser $parserHelper
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\Elasticsearch\Helper\Data $dataHelper
    ) {
        $this->coreHelper = $coreHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_Elasticsearch::browse');
    }

    /**
     * execute action
     */
    abstract public function execute();
}
