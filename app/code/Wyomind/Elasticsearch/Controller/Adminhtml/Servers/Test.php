<?php

namespace Wyomind\Elasticsearch\Controller\Adminhtml\Servers;

class Test extends \Magento\Backend\App\Action
{

    protected $_config = null;
    protected $_jsonHelper = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Wyomind\Elasticsearch\Helper\Config $config,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
    
        parent::__construct($context);
        $this->_config = $config;
        $this->_jsonHelper = $jsonHelper;
    }

    protected function _isAllowed()
    {
        return true;
    }

    public function execute()
    {
        
        $warnings = [];
        $hosts = explode(',', $this->getRequest()->getParam('servers'));
        foreach ($hosts as $host) {
            $test = \Elasticsearch\ClientBuilder::create()
                    ->setHosts([$host])
                    ->build();
            try {
                $warnings[] = ['host'=>$host,'data'=>$test->info([ "client" => ["verify" => false, "connect_timeout" => 5]])];
            } catch (\Exception $e) {
                $warnings[] = ['host'=>$host,'error'=>$e->getMessage()];
            }
        }
        
        return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($warnings));
    }
}
