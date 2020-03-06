<?php

namespace TF\StoreHours\Test\Time;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use TF\StoreHours\Time\IntlTimeFormatter;

/**
 * Class IntlTimeFormatterTest
 */
class IntlTimeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    protected function setUp()
    {
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->timezone = $this->createMock(TimezoneInterface::class);
    }

    public function testFormatTimeUsChicago()
    {
        $this->resolver->method('getLocale')->willReturn('en_US');
        $this->timezone->method('getConfigTimezone')
            ->willReturn('America/Chicago');

        $timeFormatter = new IntlTimeFormatter(
            $this->timezone,
            $this->resolver
        );

        $beforeDstTs = (new \DateTime())
            ->setDate(2020, 3, 7)
            ->setTime(22, 11)
            ->getTimestamp();

        $afterDstTs = (new \DateTime())
            ->setDate(2020, 3, 8)
            ->setTime(22, 11)
            ->getTimestamp();

        $beforeDst = $timeFormatter->formatTime($beforeDstTs);
        $afterDst = $timeFormatter->formatTime($afterDstTs);

        // Before and after daylight saving time
        $this->assertEquals($beforeDst, '4:11 PM');
        $this->assertEquals($afterDst, '5:11 PM');
    }

    public function testFormatTimeFiShanghai()
    {
        $this->resolver->method('getLocale')->willReturn('en_FI');
        $this->timezone->method('getConfigTimezone')
            ->willReturn('Asia/Shanghai');

        $timeFormatter = new IntlTimeFormatter(
            $this->timezone,
            $this->resolver
        );

        $beforeDstTs = (new \DateTime())
            ->setDate(2020, 3, 7)
            ->setTime(12, 11)
            ->getTimestamp();

        $afterDstTs = (new \DateTime())
            ->setDate(2020, 3, 8)
            ->setTime(12, 11)
            ->getTimestamp();

        $beforeDst = $timeFormatter->formatTime($beforeDstTs);
        $afterDst = $timeFormatter->formatTime($afterDstTs);

        // Should be no difference in this timezone
        $this->assertEquals($beforeDst, '20.11');
        $this->assertEquals($afterDst, '20.11');
    }

    public function testParseTimeFailureUs()
    {
        $this->resolver->method('getLocale')->willReturn('en_US');
        $this->timezone->method('getConfigTimezone')
            ->willReturn('America/Chicago');

        $timeFormatter = new IntlTimeFormatter(
            $this->timezone,
            $this->resolver
        );

        $this->assertFalse($timeFormatter->parseTime('20.11'));
    }

    public function testParseTimeChicago()
    {
        $this->resolver->method('getLocale')->willReturn('en_US');
        $this->timezone->method('getConfigTimezone')
            ->willReturn('America/Chicago');

        $timeFormatter = new IntlTimeFormatter(
            $this->timezone,
            $this->resolver
        );

        // (12 + 8 + 6) * 60 * 60 + 11 * 60
        $this->assertEquals($timeFormatter->parseTime('8:11 PM'), 94260);
    }

    public function testParseTimeShanghai()
    {
        $this->resolver->method('getLocale')->willReturn('en_US');
        $this->timezone->method('getConfigTimezone')
            ->willReturn('Asia/Shanghai');

        $timeFormatter = new IntlTimeFormatter(
            $this->timezone,
            $this->resolver
        );

        // (1 - 8) * 60 * 60 + 36 * 60
        $this->assertEquals($timeFormatter->parseTime('1:36 AM'), -23040);
    }
}
