<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Formatter;

use Wyomind\Elasticsearch\Helper\Config;
use Wyomind\Elasticsearch\Model\FormatterInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Design\Theme\FlyweightFactory;

class Image implements FormatterInterface
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var FlyweightFactory
     */
    protected $themeFactory;

    /**
     * @param Config $config
     * @param ImageHelper $imageHelper
     * @param AssetRepository $assetRepo
     * @param FlyweightFactory $themeFactory
     */
    public function __construct(
    Config $config,
        ImageHelper $imageHelper,
        AssetRepository $assetRepo,
        FlyweightFactory $themeFactory
    )
    {

        $this->config = $config;
        $this->imageHelper = $imageHelper;
        $this->assetRepo = $assetRepo;
        $this->themeFactory = $themeFactory;
    }

    /**
     * @param mixed $value
     * @param mixed $store
     * @return mixed
     */
    public function format(
    $value,
        $store = null
    )
    {

        if ($value == 'no_selection') {
            $value = null;
        } else {
            try {
                $imgSize = $this->config->getProductImageSize($store);
                $image = $this->imageHelper->init(null, 'thumbnail', [
                    'type' => 'thumbnail',
                    'width' => $imgSize,
                    'height' => $imgSize,
                ]);
                $image->setImageFile($value);
                $value = $image->getUrl();
                if ($store->isFrontUrlSecure()) {
                    $value = str_replace("http://", "https://", $value);
                }
                if (!$this->config->getEnablePubFolder()) {
                    $value = str_replace("pub/", "", $value);
                }
            } catch (\Exception $e) {
                $themeId = $this->config->getTheme($store);
                $value = $this->assetRepo
                    ->createAsset('Magento_Catalog::images/product/placeholder/image.jpg', [
                        'area' => 'frontend',
                        'theme' => $this->themeFactory->create($themeId)->getThemePath(),
                    ])
                    ->getUrl();
            }
        }

        return $value;
    }

}
