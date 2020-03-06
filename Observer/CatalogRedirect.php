<?php

namespace TF\StoreHours\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use TF\StoreHours\Time\TimeCheck;

class CatalogRedirect implements ObserverInterface
{
    /**
     * @var RedirectInterface
     */
    private $redirect;

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
     * @param RedirectInterface $redirect
     * @param ScopeConfigInterface $scopeConfig
     * @param TimeCheck $timeCheck
     * @param Json $serializer
     */
    public function __construct(
        RedirectInterface $redirect,
        ScopeConfigInterface $scopeConfig,
        TimeCheck $timeCheck,
        Json $serializer
    ) {
        $this->redirect = $redirect;
        $this->scopeConfig = $scopeConfig;
        $this->timeCheck = $timeCheck;
        $this->serializer = $serializer;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $value = $this->scopeConfig->getValue(
            'general/store_hours/hours',
            ScopeInterface::SCOPE_STORE
        );

        $hours = $this->serializer->unserialize($value);

        $this->timeCheck->setHours($hours);
        $this->timeCheck->setCurrentDateTime(time());

        if ($this->timeCheck->isClosed()) {
            $response = $observer->getEvent()->getControllerAction()->getResponse();
            $this->redirect->redirect($response, 'store-closed');
        }
    }
}
