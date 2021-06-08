<?php
// https://www.czso.cz/csu/czso/mira_inflace

$UNKNOWN_YEAR = 2.0;

$YEAR_TABLE = array(
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

$YEAR_MIN = min(array_keys($YEAR_TABLE));
$YEAR_MAX = max(array_keys($YEAR_TABLE)) - 1;

function conversion_table_1(float $value, int $year): array {
    global $YEAR_TABLE, $YEAR_MIN, $YEAR_MAX;
    if (! array_key_exists($year, $YEAR_TABLE)) {
        throw new Exception("Unsupported year $year");
    }

    $years = array();

    $coef = 1.0;
    $years[$year] = array('value' => $coef * $value, 'coef' => $coef);

    for ($y = ($year - 1); $y >= $YEAR_MIN; $y--) {
        $coef /= ((100.0 + $YEAR_TABLE[$y + 1]) / 100.0);
        $years[$y] = array('value' => $coef * $value, 'coef' => $coef);
    }
    $coef = 1.0;
    for ($y = $year; $y <= $YEAR_MAX; $y++) {
        $years[$y] = array('value' => $coef * $value, 'coef' => $coef);
        $coef /= (100.0 / (100.0 + $YEAR_TABLE[$y + 1]));
    }

    ksort($years, SORT_NUMERIC);

    return $years;
}

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

?>