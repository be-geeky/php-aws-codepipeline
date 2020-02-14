<?php
namespace Intesols\Distributor\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	protected $scopeConfig;

	public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Psr\Log\LoggerInterface $logger, \Magento\Store\Model\StoreManagerInterface $storeManager) {
		$this -> scopeConfig = $scopeConfig;
		$this -> storeManager = $storeManager;
		$this -> logger = $logger;
	}	
	public function getStatus() {
		return $this -> scopeConfig -> getValue('intesolsRT/licenseandstatus/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

	}
	public function getCountryCode() {
		return $this -> scopeConfig -> getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

	}	
}