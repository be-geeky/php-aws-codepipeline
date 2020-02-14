<?php
namespace Intesols\Configupdate\Plugin\Magento\ConfigurableProduct\Block\Product\View\Type;

class Configurable
{

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {

        $jsonResult = json_decode($result, true);

        $jsonResult['skus'] = [];
        $jsonResult['barcodes'] = [];
        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $jsonResult['skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
			$jsonResult['barcodes'][$simpleProduct->getId()] = $simpleProduct->getBarcodeEan();
        }


        $result = json_encode($jsonResult);

        return $result;
    }
}