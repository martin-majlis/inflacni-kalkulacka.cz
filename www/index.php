<?php
require_once('Calculator.php');

use InflationCalculator\Calculator;

// https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage
// TODO: just PSR1.Files.SideEffects.FoundWithSymbols - does not work :/
// phpcs:disable
define('DEFAULT_VALUE', 10000);
define('LOOKAHEAD_YEARS', 5);

define('YEAR_CURRENT', intval(date('Y')));
define('YEAR_DEFAULT', "" . (YEAR_CURRENT - 1));
define('YEAR_MAX_TARGET', YEAR_CURRENT + LOOKAHEAD_YEARS);

function web_link(float $v, int $y, int $t): string
{
    return 'https://' . $_SERVER['HTTP_HOST'] . "/?year=$y&value=$v&target=$t";
}
// phpcs:enable

$year = intval($_GET['year'] ?? YEAR_DEFAULT);
$target = intval($_GET['target'] ?? 0);
if ($target == 0) {
    $target = $year - LOOKAHEAD_YEARS;
    if ($target < YEAR_MIN) {
        $target = $year + LOOKAHEAD_YEARS;
    }
}

$value = floatval($_GET['value'] ?? DEFAULT_VALUE);
if ($value == 0) {
    $value = DEFAULT_VALUE;
}
$format = $_GET['format'] ?? 'html';

$web_url = web_link($value, $year, $target);
$api_url = $web_url . '&format=json';

if ($year < YEAR_MIN || $year > YEAR_MAX) {
    http_response_code(400);
    echo "Year $year is not valid. It has to be between " . YEAR_MIN . " and " . YEAR_MAX;
    exit(0);
}

// print("YEAR: $year; VALUE: $value; Format: $format");

$calculator = new Calculator();
$table = $calculator->conversionTable($value, $year);

if ($format == 'json') {
    header('Content-Type: application/json');
    echo json_encode(
        array(
            'input' => array(
                'year' => $year,
                'value' => $value,
            ),
            'year' => $table
        )
    );
    exit(0);
} elseif ($format != 'html') {
    // Permanent 301 redirection
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $web_url");
    exit(0);
}

$messages = $calculator->messages($value, $year, $target);

$title = 'Inflační kalkulačka';
$description = (
    'Inflační kalkulačka vám spočítá, jakou hodnotu měly Vaše peníze v minulosti a ' .
    'jakou budou mit hodnotu v budoucnosti.'
);
if (isset($_GET['year']) && isset($_GET['value']) && isset($_GET['target'])) {
    $title .= " - hodnota $value z roku $year";
    $description = html_entity_decode(
        strip_tags('Inflační kalkulačka: ' . implode('; ', $messages))
    );
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
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css"
            rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x"
            crossorigin="anonymous"
        />
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

        <form class="row">
        <div class="mb-3">
                <label for="value" class="control-label">Hodnota (Kč)</label>
                <div class="input-group">
                    <input
                        type="number"
                        class="form-control-lg"
                        id="value"
                        name="value"
                        placeholder="<?php echo DEFAULT_VALUE; ?>"
                        value="<?php echo $value; ?>"
                    />
                    <!-- <span class="input-group-text"> Kč</span> //-->
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
            echo '<ul>';
            foreach ($messages as $msg) {
                echo "<li>$msg</li>\n";
            }
            echo '</ul>';
            ?>

            <h2>Tabulka</h2>
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                    <th scope="col">Rok</th>
                    <th scope="col">Hodnota&nbsp;Kč&nbsp;-&nbsp;Nákup</th>
                    <th scope="col">Hodnota&nbsp;Kč&nbsp;-&nbsp;Úspory</th>
                    <th scope="col">Inflace</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                for ($y = YEAR_MIN; $y <= YEAR_MAX_TARGET; $y++) {
                    $mark = ($y >= YEAR_CURRENT ? '* ' : ' ');
                    echo '<tr';
                    if ($y == $year) {
                        echo ' class="table-primary"';
                    } elseif ($y == $target) {
                        echo ' class="table-secondary"';
                    }
                    echo '>';
                    echo '<td>' . $mark . '<a href="' . web_link($value, $year, $y) . '">' . $y . '</a></td>';
                    echo '<td>' . $mark . round($table[$y][VALUE_PURCHASE]) . '</td>';
                    echo '<td>' . $mark . round($table[$y][VALUE_SAVING]) . '</td>';
                    echo '<td>' . $mark . sprintf("%0.1f", $calculator->inflation($y)) . '%</td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>


        </div>


        <div>
            <h2>Odkazy</h2>

            <ul>
                <li>Data:
                    <a href="https://www.czso.cz/csu/czso/mira_inflace">
                        ČSÚ: Inflace - druhy, definice, tabulky
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
