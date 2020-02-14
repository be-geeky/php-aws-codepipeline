<?php

namespace Alogic\Customadmin\Block\Adminhtml;

class Product extends \Magento\Catalog\Block\Adminhtml\Product {
	protected function _getAddProductButtonOptions() {
		/* var Array $arrAlowedTypes */
		$arrAlowedTypesIds = array('simple', 'configurable');
		$splitButtonOptions = [];
		$types = $this->_typeFactory->create()->getTypes();
		uasort(
			$types,
			function ($elementOne, $elementTwo) {
				return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
			}
		);

		foreach ($types as $typeId => $type) {
			if (in_array($typeId, $arrAlowedTypesIds)) {
				$splitButtonOptions[$typeId] = [
					'label' => __($type['label']),
					'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
					'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
				];
			}
		}

		return $splitButtonOptions;
	}
}