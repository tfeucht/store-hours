<?php

namespace TF\StoreHours\Block;

class Text extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getIsRenderToJsTemplate() === true) {
           return '<%- day_label %>';
        } else {
            // not implemented
        }
    }
}
