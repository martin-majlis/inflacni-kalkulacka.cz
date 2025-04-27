<?php
require_once('Calculator.php');
require_once('ParsingException.php');
require_once('Parser.php');


use InflationCalculator\Calculator;
use InflationCalculator\Parser;
use InflationCalculator\ParsingException;

// https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage
// TODO: just PSR1.Files.SideEffects.FoundWithSymbols - does not work :/
// phpcs:disable
define('DEFAULT_VALUE_SINGLE', 10000);
define('DEFAULT_VALUE_MULTIPLE', "1994;10000\n1995;10000\n1996;10000\n1997;10000\n1998;10000\n1999;10000\n2000;10000");
define('LOOKAHEAD_YEARS', 5);

define('YEAR_CURRENT', intval(date('Y')));
define('YEAR_DEFAULT', "" . (YEAR_CURRENT - 1));
define('YEAR_MAX_TARGET', YEAR_CURRENT + LOOKAHEAD_YEARS);

function web_link(?string $value, ?string $values, int $y, int $t): string
{
    return 'https://' . $_SERVER['HTTP_HOST'] . '/?year=' . urlencode($y) . '&value=' . urlencode($value) . '&values=' . urlencode($values) . '&target=' . urlencode($t);
}
// phpcs:enable

$ERRORS = array();

$parser = new Parser();

if (isset($_GET['year'])) {
    try {
        $year = $parser->fromCzechNumberToYear($_GET['year']);
    } catch (ParsingException $e) {
        $ERRORS = array_merge($ERRORS, $e->getErrors());
    }
}
if (!isset($year)) {
    $year = YEAR_DEFAULT;
}


if (isset($_GET['target'])) {
    try {
        $target = $parser->fromCzechNumberToYear($_GET['target']);
    } catch (ParsingException $e) {
        $ERRORS = array_merge($ERRORS, $e->getErrors());
    }
}
if (! isset($target)) {
    $target = $year - LOOKAHEAD_YEARS;
    if ($target < YEAR_MIN) {
        $target = $year + LOOKAHEAD_YEARS;
    }
}


try {
    $value = $parser->parseValue($_GET['value'] ?? DEFAULT_VALUE_SINGLE, DEFAULT_VALUE_SINGLE);
} catch (ParsingException $e) {
    $ERRORS = array_merge($ERRORS, $e->getErrors());
}

try {
    $values = $parser->parseValues($_GET['values'] ?? DEFAULT_VALUE_MULTIPLE, DEFAULT_VALUE_MULTIPLE);
} catch (ParsingException $e) {
    $ERRORS = array_merge($ERRORS, $e->getErrors());
}

$format = $_GET['format'] ?? 'html';

$web_url = web_link($_GET['value'] ?? null, $_GET['values'] ?? null, $year, $target);
$api_url = $web_url . '&format=json';



// print("YEAR: $year; VALUE: $value; Format: $format");

$calculator = new Calculator();
if (isset($value)) {
    $tableSingle = $calculator->conversionTable($value, $year);
}
if (isset($values)) {
    $tableMultiple = $calculator->totalTable($values, $target);
}


if (count($ERRORS)) {
    header('HTTP/1.1 400 Bad Request');
}
if ($format == 'json') {
    header('Content-Type: application/json');
    echo json_encode(
        array(
            'input' => array(
                'year' => $year,
                'value' => $value,
                'values' => $values,
            ),
            'result' => $table,
            'errors' => $ERRORS,
        )
    );
    exit(0);
} elseif ($format != 'html') {
    // Permanent 301 redirection
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $web_url");
    exit(0);
}

$title = 'Inflační kalkulačka';
$description = (
    'Inflační kalkulačka vám spočítá, jakou hodnotu měly Vaše peníze v minulosti a ' .
    'jakou budou mit hodnotu v budoucnosti.'
);

if (isset($value)) {
    $messagesSingle = $calculator->messagesValue($value, $year, $target, $tableSingle);
}

if (isset($values)) {
    $messagesMultiple = $calculator->messagesValues($values, $year, $target, $tableMultiple);
}

$classSingle = 'active';
$classMultiple = '';
if (isset($_GET['year']) && isset($_GET['value']) && isset($_GET['target']) && isset($messagesSingle)) {
    $title .= " - hodnota $value z roku $year";
    $description = html_entity_decode(
        strip_tags('Inflační kalkulačka: ' . implode('; ', $messagesSingle))
    );
}
if (isset($_GET['values']) && isset($_GET['target'])) {
    if (isset($messagesMultiple)) {
        $title .= " - hodnota v roce $target";
        $description = html_entity_decode(
            strip_tags('Inflační kalkulačka: ' . implode('; ', $messagesMultiple))
        );
    }
    $classMultiple = 'active';
    $classSingle = '';
}

// https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML/The_head_metadata_in_HTML
// https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/summary-card-with-large-image
// https://ogp.me/

?>

<html lang="cs">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
            crossorigin="anonymous">
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>

        <title><?php echo $title; ?></title>
        <meta property="og:site_name" content="Inflační kalkulačka" />
        <meta property='og:url' content="<?php echo $web_url; ?>" />
        <meta property="og:title" content="<?php echo $title; ?>">
        <meta property="og:description" content="<?php echo $description; ?>">
        <meta name="twitter:title" content="<?php echo $title; ?>">
        <meta name="twitter:description" content="<?php echo $description; ?>">

        <meta property="description" content="<?php echo $description; ?>">
        <meta name="keywords" content="inflace, hodnota peněz, úspory, inflační kalkulačka">
        <link rel="canonical" href="<?php echo $web_url; ?>">

        <style>
            td, th {
                text-align: right;
            }
            h1 a {
                color: inherit;
                text-decoration: inherit;
            }
        </style>
    </head>
<body>
    <div class="container">
        <h1><a href="/">Inflační kalkulačka</a></h1>
        <p>
            Inflační kalkulačka vám spočítá, jakou hodnotu měly Vaše peníze v minulosti a
            jakou budou mit hodnotu v budoucnosti.
        </p>

        <?php

        if (count($ERRORS)) {
            echo '<div id="alers">';
            foreach ($ERRORS as $error) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }
            echo '</div>';
        }
        ?>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link <?php echo $classSingle; ?>"
                    id="single-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#single"
                    type="button"
                    role="tab"
                    aria-controls="single"
                    aria-selected="true"
                >Jednotlivě</button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link <?php echo $classMultiple; ?>"
                    id="multi-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#multi"
                    type="button"
                    role="tab"
                    aria-controls="multi"
                    aria-selected="false"
                >V čase</button>
            </li>
        </ul>


        <div class="tab-content" id="myTabContent">

        <div class="tab-pane show <?php echo $classSingle; ?>" id="single" role="tabpanel" aria-labelledby="single-tab">

            <form class="row">
            <div class="mb-3">
                    <label for="value" class="control-label">Hodnota (Kč)</label>
                    <div class="input-group">
                        <input
                            type="number"
                            class="form-control-lg"
                            id="value"
                            name="value"
                            placeholder="<?php echo DEFAULT_VALUE_SINGLE; ?>"
                            value="<?php echo $_GET['value'] ?? DEFAULT_VALUE_SINGLE; ?>"
                        />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="year" class="control-label">z roku</label>
                    <select
                        class="form-select form-select-lg"
                        name="year"
                        id="year"
                    >
                        <?php
                        for ($y = YEAR_MIN; $y <= YEAR_DEFAULT; $y++) {
                            echo "<option value='$y'";
                            if ($y == $year) {
                                echo " selected";
                            }

                            echo ">$y</option>\n";
                        }
                        ?>
                    </select>
                </div>



                <div class="mb-3">
                    <label for="target" class="control-label">má hodnotu v roce</label>
                    <select
                        class="form-select form-select-lg"
                        name="target"
                        id="target"
                    >
                        <?php
                        for ($y = YEAR_MIN; $y <= YEAR_MAX_TARGET; $y++) {
                            echo "<option value='$y'";
                            if ($y == $target) {
                                echo " selected";
                            }

                            echo ">$y</option>\n";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button
                        type="submit"
                        class="btn-lg btn-primary"
                    >
                        Spočítej
                    </button>
                </div>
            </form>

            <div>
                <h2>Hodnoty</h2>
                <?php
                if (isset($messagesSingle)) {
                    echo '<ul>';
                    foreach ($messagesSingle as $msg) {
                        echo "<li>$msg</li>\n";
                    }
                    echo '</ul>';
                }
                ?>

                <h2>Tabulka</h2>
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                        <th scope="col">Rok</th>
                        <th scope="col">Nákup&nbsp;Kč</th>
                        <th scope="col">Úspory&nbsp;Kč</th>
                        <th scope="col">Inflace</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (isset($tableSingle)) {
                        $year_min = max(YEAR_MIN, min($year, $target) - 2);
                        $year_max = min(YEAR_MAX, max($year, $target) + 2);
                        for ($y = $year_min; $y <= $year_max; $y++) {
                            $mark = ($y >= YEAR_CURRENT ? '* ' : ' ');
                            echo '<tr';
                            if ($y == $year) {
                                echo ' class="table-primary"';
                            } elseif ($y == $target) {
                                echo ' class="table-secondary"';
                            }
                            echo '>';
                            echo '<td>' . $mark . '<a href="' .
                                web_link($_GET['value'] ?? null, $_GET['values'] ?? null, $year, $y) .
                                '">' . $y . '</a></td>';
                            echo '<td>' . $mark . number_format(round($tableSingle[$y][VALUE_PURCHASE]), 0, ',', ' ') . '</td>';
                            echo '<td>' . $mark . number_format(round($tableSingle[$y][VALUE_SAVING]), 0, ',', ' ') . '</td>';
                            echo '<td>' . $mark . sprintf("%0.1f", $calculator->inflation($y)) . '%</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                    </tbody>
                </table>
                </div>


        </div>

        <div class="tab-pane <?php echo $classMultiple; ?>" id="multi" role="tabpanel" aria-labelledby="multi-tab">
        <form class="row">
            <div class="mb-3">
                    <label for="values" class="control-label">Rok; Hodnoty (Kč)</label>
                    <div class="input-group">
                        <textarea
                            class="form-control-lg"
                            id="values"
                            name="values"
                            placeholder="<?php echo DEFAULT_VALUE_MULTIPLE; ?>"
                            rows="8"
                        ><?php echo trim($_GET['values'] ?? DEFAULT_VALUE_MULTIPLE); ?></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="target" class="control-label">má hodnotu v roce</label>
                    <select
                        class="form-select form-select-lg"
                        name="target"
                        id="target"
                    >
                        <?php
                        for ($y = YEAR_MIN; $y <= YEAR_MAX_TARGET; $y++) {
                            echo "<option value='$y'";
                            if ($y == $target) {
                                echo " selected";
                            }

                            echo ">$y</option>\n";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button
                        type="submit"
                        class="btn-lg btn-primary"
                    >
                        Spočítej
                    </button>
                </div>
            </form>

            <div>
            <h2>Hodnoty</h2>
                <?php
                if (isset($messagesMultiple)) {
                    echo '<ul>';
                    foreach ($messagesMultiple as $msg) {
                        echo "<li>$msg</li>\n";
                    }
                    echo '</ul>';
                }
                ?>
            <h2>Tabulka</h2>
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                        <th scope="col">Rok</th>
                        <th scope="col">Hodnota&nbsp;Kč</th>
                        <th scope="col">Nákup&nbsp;Kč</th>
                        <th scope="col">Úspory&nbsp;Kč</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (isset($tableMultiple)) {
                        foreach ($tableMultiple['table'] as $row) {
                            echo '<tr>';
                            echo '<td><a href="' .
                                web_link($row[VALUE_INPUT], null, $row[YEAR_INPUT], $target) .
                                '">' . $row[YEAR_INPUT] . '</a></td>';
                            echo '<td>' . number_format($row[VALUE_INPUT], 0, ',', ' ') . '</td>';
                            echo '<td>' . number_format(round($row[VALUE_PURCHASE]), 0, ',', ' ') . '</td>';
                            echo '<td>' . number_format(round($row[VALUE_SAVING]), 0, ',', ' ') . '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td scope="col">Celkem v roce <?php echo $target; ?></td>
                        <?php
                        if (isset($tableMultiple)) {
                            echo '<td>' . number_format(round($tableMultiple['total'][VALUE_INPUT]), 0, ',', ' ') . '</td>';
                            echo '<td>' . number_format(round($tableMultiple['total'][VALUE_PURCHASE]), 0, ',', ' ') . '</td>';
                            echo '<td>' . number_format(round($tableMultiple['total'][VALUE_SAVING]), 0, ',', ' ') . '</td>';
                        }
                        ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        </div>


        <div>
            <h2>Odkazy</h2>

            <ul>
                <li>Data:
                    <a href="https://www.czso.cz/csu/czso/mira_inflace">
                        ČSÚ: Inflace - druhy, definice, tabulky
                    </a>,
                    <a href="https://www.cnb.cz/cs/menova-politika/prognoza/">
                        ČNB: Prognóza
                    </a>
                </li>
                <li>API:
                    <a
                        href="<?php echo $api_url; ?>"
                    ><?php echo $api_url; ?>
                    </a>
                </li>
                <li>GitHub:
                    <a href="https://github.com/martin-majlis/inflacni-kalkulacka.cz">
                        https://github.com/martin-majlis/inflacni-kalkulacka.cz
                    </a>
                </li>
            </p>
        </div>
    </div>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-322031-31"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-322031-31');
    </script>
</body>
</html>
