<?php

namespace TF\StoreHours\Test\Time;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use TF\StoreHours\Time\IntlTimeFormatter;
use TF\StoreHours\Time\TimeCheck;

/**
 * Class TimeCheckTest
 */
class TimeCheckTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var IntlTimeFormatter
     */
    protected $timeFormatter;

    protected function setUp()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $timezone = $this->createMock(TimezoneInterface::class);

        $resolver->method('getLocale')->willReturn('en_US');
        $timezone->method('getConfigTimezone')
            ->willReturn('America/Chicago');

        $this->timeFormatter = new IntlTimeFormatter(
            $timezone,
            $resolver
        );
    }

    /**
     * Because the time formatter parses the current time without date
     * information, the timestamp is always calculated without adjusting for
     * DST. This means the stored hour timestamps should not need to be adjusted
     * for DST.
     */
    public function testDst()
    {
        $beforeDst = (new \DateTime())
            ->setTimezone(new \DateTimeZone('America/Chicago'))
            ->setDate(2020, 3, 1)
            ->setTime(20, 0)
            ->getTimestamp();

        $afterDst = (new \DateTime())
            ->setTimezone(new \DateTimeZone('America/Chicago'))
            ->setDate(2020, 3, 8)
            ->setTime(20, 0)
            ->getTimestamp();

        // This would be 9:00 pm - 9:00 pm during DST
        $hours = [
            'Sun' => [
                'opening_time' => $this->timeFormatter->parseTime('8:00 a'),
                'closing_time' => $this->timeFormatter->parseTime('8:00 p')
            ]
        ];

        $timeCheck = new TimeCheck($this->timeFormatter);
        $timeCheck->setHours($hours);

        $timeCheck->setCurrentDateTime($beforeDst);
        $this->assertTrue($timeCheck->isClosed());

        $timeCheck->setCurrentDateTime($afterDst);
        $this->assertTrue($timeCheck->isClosed());
    }

    public function testExtremes()
    {
        $beforeOpening = (new \DateTime())
            ->setTimezone(new \DateTimeZone('America/Chicago'))
            ->setDate(2020, 3, 2)
            ->setTime(0, 0)
            ->getTimestamp();

        $atOpening = (new \DateTime())
            ->setTimezone(new \DateTimeZone('America/Chicago'))
            ->setDate(2020, 3, 2)
            ->setTime(0, 1)
            ->getTimestamp();

        $beforeClosing = (new \DateTime())
            ->setTimezone(new \DateTimeZone('America/Chicago'))
            ->setDate(2020, 3, 2)
            ->setTime(23, 58)
            ->getTimestamp();

        $atClosing = (new \DateTime())
            ->setTimezone(new \DateTimeZone('America/Chicago'))
            ->setDate(2020, 3, 2)
            ->setTime(23, 59)
            ->getTimestamp();

        $hours = [
            'Mon' => [
                'opening_time' => $this->timeFormatter->parseTime('12:01 a'),
                'closing_time' => $this->timeFormatter->parseTime('11:59 p')
            ]
        ];

        $timeCheck = new TimeCheck($this->timeFormatter);
        $timeCheck->setHours($hours);

        $timeCheck->setCurrentDateTime($beforeOpening);
        $this->assertTrue($timeCheck->isClosed());

        $timeCheck->setCurrentDateTime($atClosing);
        $this->assertTrue($timeCheck->isClosed());

        $timeCheck->setCurrentDateTime($atOpening);
        $this->assertFalse($timeCheck->isClosed());

        $timeCheck->setCurrentDateTime($beforeClosing);
        $this->assertFalse($timeCheck->isClosed());
    }
}
