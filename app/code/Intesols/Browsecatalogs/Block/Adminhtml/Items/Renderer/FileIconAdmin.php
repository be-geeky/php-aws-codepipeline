<?php
namespace Intesols\Browsecatalogs\Block\Adminhtml\Items\Renderer;
 
use Magento\Framework\DataObject;

/**
 * Class FileIconAdmin
 * @package Intesols\Browsecatalogs\Block\Adminhtml\Items\Renderer
 */
class FileIconAdmin extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Prince\Productattach\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Prince\Productattach\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuider;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

	protected $request;
	
    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Intesols\Browsecatalogs\Helper\Data $dataHelper
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Intesols\Browsecatalogs\Helper\Data $dataHelper,
        \Magento\Backend\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Registry $registry,
		\Magento\Framework\App\Request\Http $request
    ) {
        $this->dataHelper = $dataHelper;
        $this->assetRepo = $assetRepo;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $registry;
		$this->request = $request;
    }
 
    /**
     * get customer group name
     * @param  DataObject $row
     * @return string
     */
    public function getElementHtml()
    {
        $fileIcon = '<h3>No File Uploded</h3>';
        $file = $this->getValue();
			
        if ($file) {
            $fileExt = pathinfo($file, PATHINFO_EXTENSION);
            if ($fileExt) {
                $iconImage = $this->assetRepo->getUrl(
                    'Intesols_Browsecatalogs::images/'.$fileExt.'.png'
                );
                $url = $this->dataHelper->getBaseUrl().'/'.$file;
                $fileIcon = "<a href=".$url." target='_blank'>
                    <img src='".$iconImage."' />
                    <div>OPEN FILE</div></a>";
            } else {
                 $iconImage = $this->assetRepo->getUrl('Intesols_Browsecatalogs::images/unknown.png');
                 $fileIcon = "<img src='".$iconImage."' />";
            }
            //$attachId = $this->coreRegistry->registry('browsecatalogs_id');
			$id = $this->request->getParam('id');
            $fileIcon .= "<a href='".$this->urlBuilder->getUrl(
                'intesols_browsecatalogs/items/deletefile', $param = ['id' => $id])."'>
                <div style='color:red;'>DELETE FILE</div></a>";
        } 
        return $fileIcon;
    }
}