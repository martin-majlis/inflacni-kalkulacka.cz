<?php

namespace InflationCalculator;

use InflationCalculator\ParsingException;
use InflationCalculator\Calculator;

class Parser
{
    public function fromCzechNumberToValue(string $value): float
    {
        // first, remove all spaces
        $value = trim(str_replace(' ', '', $value));

        // it's float => convert to float
        if (is_numeric($value)) {
            return floatval($value);
        }

        // in Czech , serves as floating point => try to replace it
        $value = str_replace(',', '.', $value);
        if (is_numeric($value)) {
            return floatval($value);
        }

        // TODO: we can try more tricks
        throw new ParsingException(
            array(
                "'$value' není platná hodnota. " .
                "Platné hodnoty jsou 12345; 12345.67; 12345,67; 12 345.67"
            )
        );
    }


    public function fromCzechNumberToYear(string $value): int
    {
        // first, remove all spaces
        $value = trim(str_replace(' ', '', $value));

        if (is_numeric($value)) {
            $year = intval($value);
            if ($year >= \YEAR_MIN && $year <= \YEAR_MAX) {
                return $year;
            }
        }

        throw new ParsingException(
            array(
                "Rok '" . $value . "' není podporovaný. " .
                "Podporavené roky jsou v rozmezí " . YEAR_MIN . ' až ' . YEAR_MAX . '.'
            )
        );
    }

    public function parseValue(?string $value, float $default): ?float
    {
        if (is_null($value)) {
            return $value;
        }
        $value = trim($value);
        if ($value == "") {
            return $default;
        }

        return $this->fromCzechNumberToValue($value);
    }

    public function parseValues(?string $values): ?array
    {
        if (is_null($values)) {
            return $values;
        }

        $res = array();
        $errors = array();

        // break into lines
        $lines = preg_split("/\r\n|\n|\r/", \trim($values));
        foreach ($lines as $lineNo => $line) {
            // remove trailing spaces from each line
            $line = trim($line);
            $lineNo += 1;

            // skip empty line
            if ($line == "") {
                continue;
            }

            // split by separator
            $parts = explode(";", $line);
            if (count($parts) != 2) {
                array_push($errors, "Řádek $lineNo: '$line' není ve formátu rok;hodnota");
                continue;
            }

            // check whether year is valid
            try {
                $year = $this->fromCzechNumberToYear($parts[0]);
            } catch (ParsingException $e) {
                array_push($errors, "Řádek $lineNo: " . $e->getErrors()[0]);
            }

            // check whether value is valid
            try {
                $value = $this->fromCzechNumberToValue($parts[1]);
            } catch (ParsingException $e) {
                array_push($errors, "Řádek $lineNo: " . $e->getErrors()[0]);
            }

            // if it's valid, add row
            if (isset($year) && isset($value)) {
                array_push($res, array("year" => $year, "value" => $value));
            }
        }

        // if there are errors, throw them
        if (count($errors)) {
            throw new ParsingException($errors);
        }

        return $res;
    }
}
