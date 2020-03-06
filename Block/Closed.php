<?php

namespace TF\StoreHours\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use TF\StoreHours\Time\TimeCheck;

class Closed extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TimeCheck
     */
    private $timeCheck;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param ScopeConfigInterface $scopeConfig
     * @param TimeCheck $timeCheck
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        ScopeConfigInterface $scopeConfig,
        TimeCheck $timeCheck,
        Json $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->timeCheck = $timeCheck;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * @return boolean
     */
    public function isClosed()
    {
        $value = $this->scopeConfig->getValue(
            'general/store_hours/hours',
            ScopeInterface::SCOPE_STORE
        );

        $hours = $this->serializer->unserialize($value);

        $this->timeCheck->setHours($hours);
        $this->timeCheck->setCurrentDateTime(time());

        return $this->timeCheck->isClosed();
    }
}
