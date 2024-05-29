<?php

namespace InflationCalculator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use InflationCalculator\Parser;

#[CoversClass(Parser::class)]
final class ParserTest extends TestCase
{
    private Parser $parser;
    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testfromCzechNumberToValue(): void
    {
        $this->assertEquals(
            12345.0,
            $this->parser->fromCzechNumberToValue("  12345  "),
        );

        $this->assertEquals(
            12345.0,
            $this->parser->fromCzechNumberToValue("12345"),
        );

        $this->assertEquals(
            12345.67,
            $this->parser->fromCzechNumberToValue("12345.67"),
        );

        $this->assertEquals(
            12345.67,
            $this->parser->fromCzechNumberToValue("12345,67"),
        );

        $this->assertEquals(
            12345.67,
            $this->parser->fromCzechNumberToValue("12 345.67"),
        );


        $this->expectException(ParsingException::class);
        $this->parser->fromCzechNumberToValue("12.345,67");
    }

    public function testfromCzechNumberToYear(): void
    {
        $this->assertEquals(
            2000,
            $this->parser->fromCzechNumberToYear("  2000  "),
        );

        $this->expectException(ParsingException::class);
        $this->parser->fromCzechNumberToYear("12345");
    }

    public function testParseValue(): void
    {
        $this->assertEquals(
            null,
            $this->parser->parseValue(null, 100),
        );

        $this->assertEquals(
            100.0,
            $this->parser->parseValue("", 100),
        );

        $this->assertEquals(
            42,
            $this->parser->parseValue("42", 100),
        );

        $this->expectException(ParsingException::class);
        $this->parser->parseValue("foo", 100);
    }

    public function testParseValues(): void
    {
        $this->assertEquals(
            null,
            $this->parser->parseValues(null),
        );

        $this->assertEquals(
            array(),
            $this->parser->parseValues(""),
        );

        $this->assertEquals(
            array(array('year' => 2000, 'value' => 123.0), array('year' => 1999, 'value' => 12345.0)),
            $this->parser->parseValues("2000;123\n \n1 999; 12 345\n\n"),
        );

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage(
            "Řádek 2: Rok '1900' není podporovaný. Podporavené roky jsou v rozmezí 1993 až 2033.\n" .
            "Řádek 3: Rok 'aaa' není podporovaný. Podporavené roky jsou v rozmezí 1993 až 2033.\n" .
            "Řádek 3: 'bbbb' není platná hodnota. Platné hodnoty jsou 12345; 12345.67; 12345,67; 12 345.67\n" .
            "Řádek 5: '' není platná hodnota. Platné hodnoty jsou 12345; 12345.67; 12345,67; 12 345.67\n" .
            "Řádek 6: '1999;2000;2000' není ve formátu rok;hodnota"
        );
        $this->parser->parseValues("2000;123\n1900;2000\naaa;bbbb\n\n1999;\n1999;2000;2000");
    }
}
