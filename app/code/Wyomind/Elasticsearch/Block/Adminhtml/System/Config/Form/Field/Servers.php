<?php

namespace Wyomind\Elasticsearch\Block\Adminhtml\System\Config\Form\Field;

class Servers extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_backendHelper = null;
    protected $_storeManager = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->_backendHelper = $backendHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value">';
            $html .= $this->_getElementHtml($element);
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= "<button "
                . "id='es_test_servers' "
                . "callback_url=" . $this->getUrl('elasticsearch/servers/test') . " "
                . "onClick='return false;' "
                . "style='inline-block' "
                . "class='action-default scalable save primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'"
                . "><span><span>" . __("Check servers") . "</span></span></button>";
        $html .= '</td>';
        return $html;
    }
}
