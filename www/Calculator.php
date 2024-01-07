<?php

namespace InflationCalculator;

const TARGET_INFLATION_RATE = 2.0;

// https://www.czso.cz/csu/czso/mira_inflace
// https://vdb.czso.cz/vdbvo2/faces/cs/index.jsf?page=vystup-objekt&skupId=43&katalog=31779&z=T&f=TABULKA&pvo=CEN08C&pvo=CEN08C
// https://eprehledy.cz/vyvoj_inflace_cr.php
// http://www.czso.cz/csu/redakce.nsf/i/mira_inflace


// https://www.cnb.cz/cs/menova-politika/prognoza/
// https://www.czso.cz/csu/czso/mira_inflace

const YEAR_TABLE = array(
    1993 => 20.8,
    1994 => 10.0,
    1995 => 9.1,
    1996 => 8.8,
    1997 => 8.5,
    1998 => 10.7,
    1999 => 2.1,
    2000 => 3.9,
    2001 => 4.7,
    2002 => 1.8,
    2003 => 0.1,
    2004 => 2.8,
    2005 => 1.9,
    2006 => 2.5,
    2007 => 2.8,
    2008 => 6.3,
    2009 => 1.0,
    2010 => 1.5,
    2011 => 1.9,
    2012 => 3.3,
    2013 => 1.4,
    2014 => 0.4,
    2015 => 0.3,
    2016 => 0.7,
    2017 => 2.5,
    2018 => 2.1,
    2019 => 2.8,
    2020 => 3.2,
    2021 => 3.8,
    2022 => 15.1,
    2023 => 10.8,
    2024 => 2.6,
    2025 => 2.1,
    2026 => TARGET_INFLATION_RATE,
    2027 => TARGET_INFLATION_RATE,
    2028 => TARGET_INFLATION_RATE,
    2029 => TARGET_INFLATION_RATE,
    2030 => TARGET_INFLATION_RATE,
    2031 => TARGET_INFLATION_RATE,
    2032 => TARGET_INFLATION_RATE,
    2033 => TARGET_INFLATION_RATE,
    2034 => TARGET_INFLATION_RATE,
);

define('YEAR_MIN', min(array_keys(YEAR_TABLE)));
define('YEAR_MAX', max(array_keys(YEAR_TABLE)) - 1);

define('VALUE_PURCHASE', 'value_p');
define('COEF_PURCHASE', 'coef_p');
define('VALUE_SAVING', 'value_s');
define('COEF_SAVING', 'coef_s');

class Calculator
{
    public const YEAR_TABLE = YEAR_TABLE;
    public const YEAR_MIN = YEAR_MIN;
    public const YEAR_MAX = YEAR_MAX;
    public const VALUE_PURCHASE = VALUE_PURCHASE;
    public const COEF_PURCHASE = COEF_PURCHASE;
    public const VALUE_SAVING = VALUE_SAVING;
    public const COEF_SAVING = COEF_SAVING;

    public function conversionTable(float $value, int $year): array
    {
        if (! array_key_exists($year, self::YEAR_TABLE)) {
            throw new Exception("Unsupported year $year");
        }

        $years = array();

        $coef_p = 1.0;
        $coef_s = 1.0;
        $years[$year] = array(
            self::VALUE_PURCHASE => $coef_p * $value,
            self::COEF_PURCHASE => $coef_p,
            self::VALUE_SAVING => $coef_s * $value,
            self::COEF_SAVING => $coef_s,
        );

        for ($y = ($year - 1); $y >= self::YEAR_MIN; $y--) {
            $coef_p /= ((100.0 + self::YEAR_TABLE[$y + 1]) / 100.0);
            $coef_s /= (100.0 / (100.0 + self::YEAR_TABLE[$y]));
            $years[$y] = array(
                self::VALUE_PURCHASE => $coef_p * $value,
                self::COEF_PURCHASE => $coef_p,
                self::VALUE_SAVING => $coef_s * $value,
                self::COEF_SAVING => $coef_s,
            );
        }
        $coef_p = 1.0;
        $coef_s = 1.0;
        for ($y = $year; $y <= self::YEAR_MAX; $y++) {
            $years[$y] = array(
                self::VALUE_PURCHASE => $coef_p * $value,
                self::COEF_PURCHASE => $coef_p,
                self::VALUE_SAVING => $coef_s * $value,
                self::COEF_SAVING => $coef_s,
            );
            $coef_p /= (100.0 / (100.0 + self::YEAR_TABLE[$y + 1]));
            $coef_s /= ((100.0 + self::YEAR_TABLE[$y]) / 100.0);
        }

        ksort($years, SORT_NUMERIC);

        return $years;
    }

    public function inflation(int $year): float
    {
        return self::YEAR_TABLE[$year];
    }

    public function messages(float $value, int $year, int $target): array
    {
        $table = $this->conversionTable($value, $year);
        $messages = array();
        $value = round($table[$year][self::VALUE_PURCHASE]);
        $pValue = round($table[$target][self::VALUE_PURCHASE]);
        $sValue = round($table[$target][self::VALUE_SAVING]);
        /*
        $messages[] = (
            "<strong>$value&nbsp;Kč</strong> v roce <strong>$year</strong> " .
            "mělo stejnou hodnotu jako <strong>$tValue&nbsp;Kč</strong> v roce $target."
        );
        */
        $messages[] = (
            "Za <strong>$pValue&nbsp;Kč</strong> v roce <strong>$target</strong> se dalo nakoupit " .
            "stejně jako za <strong>$value&nbsp;Kč</strong> v roce <strong>$year</strong>."
        );
        $messages[] = (
            "<strong>$sValue&nbsp;Kč</strong> z roku <strong>$target</strong> uložených v šuplíku " .
            "má stejnou hodnotu jako získání <strong>$value&nbsp;Kč</strong> v roce <strong>$year</strong>."
        );

        return $messages;
    }
}

/*
$COEFS = array();
$coef = 1.0;
for ($y = $YEAR_MIN; $y <= $YEAR_MAX; $y++) {
    $COEFS[$y] = $coef;
    if (array_key_exists($y, $YEAR_TABLE)) {
        $coef /= ((100 + $YEAR_TABLE[$y]) / 100.0);
    }
}

function conversion_table_2(float $value, int $year): array {
    global $COEFS, $YEAR_MIN, $YEAR_MAX;

    return array();
}
*/
