<?php

namespace InflationCalculator;

class ParsingException extends \Exception
{
    private array $errors = array();

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct(implode("\n", $errors));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
