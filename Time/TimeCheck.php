<?php

namespace TF\StoreHours\Time;

class TimeCheck
{

    /**
     * @var IntlTimeFormatter
     */
    private $timeFormatter;

    /**
     * @var array
     */
    private $hours = [];

    /**
     * @var int
     */
    private $currentDateTime = null;

    /**
     * @param IntlTimeFormatter $timeFormatter
     */
    public function __construct(
        IntlTimeFormatter $timeFormatter
    ) {
        $this->timeFormatter = $timeFormatter;
    }

    /**
     * @param array $hours
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    /**
     * @param int $timestamp
     */
    public function setCurrentDateTime($timestamp)
    {
        $this->currentDateTime = $timestamp;
    }

    public function isClosed()
    {
        if ($this->currentDateTime === null) {
            return false;
        }

        // Get current day as 3 letter code.
        $currentDay = (new \DateTime('@'.$this->currentDateTime))
            ->setTimezone($this->timeFormatter->getDateTimeZone())
            ->format('D');

        /*
         * Formatting and reparsing the current time should remove the date
         * component from the timestamp.
         */
        $currentTimeFormatted = $this->timeFormatter
            ->formatTime($this->currentDateTime);
        $currentTime = $this->timeFormatter->parseTime($currentTimeFormatted);

        $currentHours = $this->hours[$currentDay] ?? null;

        if (!$currentHours) {
            return false;
        }

        $openingTime = $currentHours['opening_time'] ?? null;
        $closingTime = $currentHours['closing_time'] ?? null;
        $closed = false;

        if (($openingTime || $openingTime === 0) && $currentTime < $openingTime) {
            $closed = true;
        }

        if (($closingTime || $closingTime === 0) && $currentTime >= $closingTime) {
            $closed = true;
        }

        return $closed;
    }
}
