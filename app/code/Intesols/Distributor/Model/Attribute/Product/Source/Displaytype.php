<?php
/**
* 
* Do not edit or add to this file if you wish to upgrade the module to newer
* versions in the future. If you wish to customize the module for your 
* needs please contact us to https://www.milople.com/contact-us.html
* 
* @category    Ecommerce
* @package     Milople_Sizechartpopup
* @copyright   Copyright (c) 2016 Milople Technologies Pvt. Ltd. All Rights Reserved.
* @url         https://www.milople.com/magento2/size-chart-popup.html
*
**/
namespace Milople\Sizechartpopup\Model\Attribute\Product\Source;
class Displaytype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {
	/**
	 *  This function Work as a Source Model for "Show Size Chart Through" field in size chart tab product and category configuration.
	 **/
	public function getAllOptions() {
		if (!$this -> _options) {
			$this -> _options = [['value' => 1, 'label' => __('Image')], ['value' => 2, 'label' => __('Block')], ];
		}
		return $this -> _options;
	}

}
