<?php

namespace TF\StoreHours\Time;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class IntlTimeFormatter
{

    /**
     * @var \IntlDateFormatter
     */
    private $formatter;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver
    ) {
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
    }

    private function getFormatter() {
        if (!$this->formatter) {
            $configTimezone = $this->timezone->getConfigTimezone();
            $locale = $this->localeResolver->getLocale();

            $this->formatter = new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT,
                $configTimezone
            );
        }

        return $this->formatter;
    }

    public function getDateTimeZone()
    {
        return $this->getFormatter()->getTimeZone()->toDateTimeZone();
    }

    public function parseTime($timeString)
    {
        return $this->getFormatter()->parse($timeString);
    }

    public function formatTime($value)
    {
        return $this->getFormatter()->format($value);
    }
}
