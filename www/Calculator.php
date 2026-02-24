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
    2023 => 10.7,
    2024 => 2.5,
    2025 => 2.6,
    2026 => 2.2,
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


define('YEAR', 'year');
define('YEAR_INPUT', 'year_input');
define('VALUE_INPUT', 'value_input');
define('VALUE_PURCHASE', 'value_purchase');
define('COEF_PURCHASE', 'coef_purchase');
define('VALUE_SAVING', 'value_saving');
define('COEF_SAVING', 'coef_saving');

class Calculator
{
    public const YEAR_TABLE = YEAR_TABLE;
    public const YEAR_MIN = YEAR_MIN;
    public const YEAR_MAX = YEAR_MAX;
    public const YEAR = YEAR;
    public const YEAR_INPUT = YEAR_INPUT;
    public const VALUE_INPUT = VALUE_INPUT;
    public const VALUE_PURCHASE = VALUE_PURCHASE;
    public const COEF_PURCHASE = COEF_PURCHASE;
    public const VALUE_SAVING = VALUE_SAVING;
    public const COEF_SAVING = COEF_SAVING;

    public function conversionTable(float $value, int $year): array
    {
        if (! array_key_exists($year, self::YEAR_TABLE)) {
            throw new \InvalidArgumentException("Unsupported year $year");
        }

        $years = array();

        $coef_p = 1.0;
        $coef_s = 1.0;
        $years[$year] = array(
            self::YEAR => $year,
            self::YEAR_INPUT => $year,
            self::VALUE_INPUT => $value,
            self::VALUE_PURCHASE => $coef_p * $value,
            self::COEF_PURCHASE => $coef_p,
            self::VALUE_SAVING => $coef_s * $value,
            self::COEF_SAVING => $coef_s,
        );

        for ($y = ($year - 1); $y >= self::YEAR_MIN; $y--) {
            $coef_p /= ((100.0 + self::YEAR_TABLE[$y + 1]) / 100.0);
            $coef_s /= (100.0 / (100.0 + self::YEAR_TABLE[$y]));
            $years[$y] = array(
                self::YEAR => $y,
                self::YEAR_INPUT => $year,
                self::VALUE_INPUT => $value,
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
                self::YEAR => $y,
                self::YEAR_INPUT => $year,
                self::VALUE_INPUT => $value,
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

    public function totalTable(array $values, int $target): array
    {
        $table = array();

        $total_value = 0.0;
        $total_purchase = 0.0;
        $total_saving = 0.0;
        foreach ($values as $row) {
            $raw = $this->conversionTable($row['value'], $row['year']);
            array_push(
                $table,
                array(
                    self::VALUE_INPUT => $row['value'],
                    self::YEAR_INPUT => $row['year'],
                    self::YEAR => $target,
                    self::VALUE_PURCHASE => $raw[$target][self::VALUE_PURCHASE],
                    self::COEF_PURCHASE => $raw[$target][self::COEF_PURCHASE],
                    self::VALUE_SAVING => $raw[$target][self::VALUE_SAVING],
                    self::COEF_SAVING => $raw[$target][self::COEF_SAVING],
                )
            );
            $total_value += $row['value'];
            $total_purchase += $raw[$target][self::VALUE_PURCHASE];
            $total_saving += $raw[$target][self::VALUE_SAVING];
        }



        return array(
            'table' => $table,
            'total' => array(
                self::VALUE_INPUT => $total_value,
                self::VALUE_PURCHASE => $total_purchase,
                self::VALUE_SAVING => $total_saving,
            )
        );
    }

    public function inflation(int $year): float
    {
        return self::YEAR_TABLE[$year];
    }

    public function messagesValue(float $value, int $year, int $target, array $table): array
    {
        $messages = array();
        $value = number_format(round($table[$target][self::VALUE_INPUT]), 0, ',', ' ');
        $pValue = number_format(round($table[$target][self::VALUE_PURCHASE]), 0, ',', ' ');
        $sValue = number_format(round($table[$target][self::VALUE_SAVING]), 0, ',', ' ');
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

    public function messagesValues(array $values, int $year, int $target, array $table): array
    {
        $messages = array();
        $value = number_format(round($table['total'][self::VALUE_INPUT]), 0, ',', ' ');
        $pValue = number_format(round($table['total'][self::VALUE_PURCHASE]), 0, ',', ' ');
        $sValue = number_format(round($table['total'][self::VALUE_SAVING]), 0, ',', ' ');
        /*
        $messages[] = (
            "<strong>$value&nbsp;Kč</strong> v roce <strong>$year</strong> " .
            "mělo stejnou hodnotu jako <strong>$tValue&nbsp;Kč</strong> v roce $target."
        );
        */
        $messages[] = (
            "Za <strong>$pValue&nbsp;Kč</strong> v roce <strong>$target</strong> se dalo nakoupit " .
            "stejně jako za <strong>$value&nbsp;Kč</strong> v uvedených letech."
        );
        $messages[] = (
            "<strong>$sValue&nbsp;Kč</strong> z roku <strong>$target</strong> uložených v šuplíku " .
            "má stejnou hodnotu jako získání <strong>$value&nbsp;Kč</strong> v uvedených letech."
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
