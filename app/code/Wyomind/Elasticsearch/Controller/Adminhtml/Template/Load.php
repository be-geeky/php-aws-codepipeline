<?php

namespace Wyomind\Elasticsearch\Controller\Adminhtml\Template;

class Load extends \Magento\Backend\App\Action
{

    protected $_config = null;
    protected $_jsonHelper = null;
    protected $_ioFile = null;

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Wyomind\Elasticsearch\Helper\Config $config,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Filesystem\Io\File $ioFile
    )
    {

        parent::__construct($context);
        $this->_jsonHelper = $jsonHelper;
        $this->_ioFile = $ioFile;
    }

    protected function _isAllowed()
    {
        return true;
    }

    public function execute()
    {

        $path = base64_decode($this->getRequest()->getParam('path'));
        $templateFile = $path . DIRECTORY_SEPARATOR . basename($path) . "._js";
        $cssFile = $path . DIRECTORY_SEPARATOR . basename($path) . ".css";

        $this->_ioFile->cd($path);

        $templateContent = "";
        if ($this->_ioFile->fileExists($templateFile)) {
            $templateContent = $this->_ioFile->read($templateFile);
        }

        $cssContent = "";
        if ($this->_ioFile->fileExists($cssFile)) {
            $cssContent = $this->_ioFile->read($cssFile);
        }


        return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode(["template" => $templateContent, "css" => $cssContent]));
    }

}
