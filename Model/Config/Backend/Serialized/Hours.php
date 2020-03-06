<?php

namespace TF\StoreHours\Model\Config\Backend\Serialized;

use Magento\Framework\Serialize\Serializer\Json;

class Hours extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @var \TF\StoreHours\Time\IntlTimeFormatter
     */
    protected $_timeFormatter;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \TF\StoreHours\Time\IntlTimeFormatter $timeFormatter
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \TF\StoreHours\Time\IntlTimeFormatter $timeFormatter
    ) {
        $this->_localeLists = $localeLists;
        $this->_timeFormatter = $timeFormatter;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data, $serializer);
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        $weekdays = $this->_localeLists->getOptionWeekdays(true);
        $value = $this->getValue();

        // Set defaults
        if ($this->getValue() === false) {
            $value = [];

            foreach ($weekdays as $weekday) {
                $code = $weekday['value'];
                $value[$code] = [
                    'day' => $code,
                    'opening_time' => '',
                    'closing_time' => ''
                ];
            }

        }

        // Fill in weekday labels
        foreach ($weekdays as $weekday) {
            $value[$weekday['value']]['day_label'] = $weekday['label'];
        }

        $this->setValue($value);
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        foreach ($value as $day => $data) {
            if (is_array($data)) {
                // Day codes do not get posted, but can be filled in from the row ID
                $value[$day]['day'] = $day;

                // Convert opening and closing times to timestamp
                $value[$day]['opening_time'] = $this->parseTime($value[$day]['opening_time']);
                $value[$day]['closing_time'] = $this->parseTime($value[$day]['closing_time']);
            }
        }

        $this->setValue($value);
        return parent::beforeSave();
    }

    private function parseTime($timeString)
    {
        // Leave empty values
        if (!$timeString) {
            return '';
        }

        $timestamp = $this->_timeFormatter->parseTime($timeString);

        if ($timestamp === false) {
            throw new \Exception("\"$timeString\" is not a valid time.");
        }

        return $timestamp;
    }
}
