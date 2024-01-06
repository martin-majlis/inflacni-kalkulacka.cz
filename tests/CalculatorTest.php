<?php

namespace InflationCalculator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use InflationCalculator\Calculator;

#[CoversClass(Calculator::class)]
final class CalculatorTest extends TestCase
{
    public function testSameValuesAsPenizecz(): void
    {
        // https://www.penize.cz/kalkulacky/znehodnoceni-koruny-inflace#inflace
        $calculator = new Calculator();

        $this->assertEquals(
            100000,
            $calculator->conversionTable(100000, 2015)[2015][VALUE_PURCHASE]
        );

        $this->assertEquals(
            100700,
            round($calculator->conversionTable(100000, 2015)[2016][VALUE_PURCHASE])
        );

        $this->assertEquals(
            99305,
            round($calculator->conversionTable(100000, 2016)[2015][VALUE_PURCHASE])
        );
        $this->assertEquals(
            100000,
            $calculator->conversionTable(100000, 2016)[2016][VALUE_PURCHASE]
        );
    }
}
