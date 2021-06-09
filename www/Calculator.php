<?php

namespace InflationCalculator;

// https://www.czso.cz/csu/czso/mira_inflace
// https://vdb.czso.cz/vdbvo2/faces/cs/index.jsf?page=vystup-objekt&skupId=43&katalog=31779&z=T&f=TABULKA&pvo=CEN08C&pvo=CEN08C
// https://eprehledy.cz/vyvoj_inflace_cr.php
// http://www.czso.cz/csu/redakce.nsf/i/mira_inflace

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
    // To make code simpler
    2021 => 0.0,
);

define('YEAR_MIN', min(array_keys(YEAR_TABLE)));
define('YEAR_MAX', max(array_keys(YEAR_TABLE)) - 1);

const UNKNOWN_YEAR = 2.0;

class Calculator
{
    public function conversionTable(float $value, int $year): array
    {
        // global $YEAR_TABLE, $YEAR_MIN, $YEAR_MAX;
        if (! array_key_exists($year, YEAR_TABLE)) {
            throw new Exception("Unsupported year $year");
        }

        $years = array();

        $coef = 1.0;
        $years[$year] = array('value' => $coef * $value, 'coef' => $coef);

        for ($y = ($year - 1); $y >= YEAR_MIN; $y--) {
            $coef /= ((100.0 + YEAR_TABLE[$y + 1]) / 100.0);
            $years[$y] = array('value' => $coef * $value, 'coef' => $coef);
        }
        $coef = 1.0;
        for ($y = $year; $y <= YEAR_MAX; $y++) {
            $years[$y] = array('value' => $coef * $value, 'coef' => $coef);
            $coef /= (100.0 / (100.0 + YEAR_TABLE[$y + 1]));
        }

        ksort($years, SORT_NUMERIC);

        return $years;
    }

    public function inflation(int $year): float
    {
        return YEAR_TABLE[$year];
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
