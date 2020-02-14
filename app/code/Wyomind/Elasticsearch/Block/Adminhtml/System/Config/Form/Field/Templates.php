<?php

namespace Wyomind\Elasticsearch\Block\Adminhtml\System\Config\Form\Field;

class Templates extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_backendHelper = null;
    protected $_storeManager = null;
    protected $_ioFile = null;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Magento\Framework\Filesystem\Io\File $ioFile,
            array $data = []
    )
    {

        parent::__construct($context, $data);
        $this->_backendHelper = $backendHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_ioFile = $ioFile;
    }

    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = "";
        
        $ds = DIRECTORY_SEPARATOR;
        $path = str_replace("Block" . $ds . "Adminhtml" . $ds . "System" . $ds . "Config" . $ds . "Form" . $ds . "Field" . $ds . "Templates.php", "", __FILE__);
        $path .= "view" . $ds . "adminhtml" . $ds . "templates" . $ds . "autocomplete";
        
        $this->_ioFile->cd($path);
        $list = $this->_ioFile->ls(\Magento\Framework\Filesystem\Io\File::GREP_DIRS);

        $html .= '<td class="value">';

        foreach ($list as $template) {
            $html .= "&nbsp;<button "
                    . "load_url=" . $this->getUrl('elasticsearch/template/load') . " "
                    . "onClick='return false;' "
                    . "style='inline-block' "
                    . "path='".base64_encode($template['id'])."' "
                    . "class='action-default scalable save primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only load-template'"
                    . "><span><span>" . __(str_replace("_", " ", $template['text'])) . "</span></span></button>";
        }

        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }

        $html .= '</td>';
        return $html;
    }

}
