<?php
require_once('functions.php');

$DEFAULT_VALUE = "10000";
$DEFAULT_YEAR = "" . (date("Y") - 1);

$year = intval($_GET['year'] ?? $DEFAULT_YEAR);
$value = floatval($_GET['value'] ?? $DEFAULT_VALUE);
if ($value == 0) {
    $value = $DEFAULT_VALUE;
}
$format = $_GET['format'] ?? 'html';

$api_url = 'https://' . $_SERVER['HTTP_HOST'] . "/?year=$year&value=$value&format=json";

if ($year < $YEAR_MIN || $year > $YEAR_MAX) {
    http_response_code(400);
    echo "Year $year is not valid. It has to be between $YEAR_MIN and $YEAR_MAX";
    exit(0);
}

// print("YEAR: $year; VALUE: $value; Format: $format");

if ($format == 'json') {
    header('Content-Type: application/json');
    echo json_encode(
        array(
            'input' => array(
                'year' => $year,
                'value' => $value,
            ),
            'year' => conversion_table_1($value, $year)
        )
    );
    exit(0);
}

$title = 'Inflační kalkulačka';
if (isset($_GET['year']) && isset($_GET['month'])) {
    $title .= " - hodnota $value z roku $year";
}

?>

<html lang="cs">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title><?php echo $title; ?></title>
    </head>
<body>
    <div class="container">
        <h1>Inflační kalkulačka</h1>

        <form class="row">
            <div class="mb-3">
                <label for="year" class="control-label">Rok</label>
                <select
                    class="form-select form-select-lg"
                    name="year"
                    id="year"
                >
                    <?php
                    for ($y = $YEAR_MIN; $y <= $YEAR_MAX; $y++) {
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
                <label for="value" class="control-label">Hodnota</label>
                <div class="input-group">
                    <input
                        type="number"
                        class="form-control-lg"
                        id="value"
                        name="value"
                        placeholder="<?php echo $DEFAULT_VALUE; ?>"
                        step="<?php echo intval($DEFAULT_VALUE / 10); ?>"
                    />
                    <span class="input-group-text"> Kč</span>
                </div>
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
            $table = conversion_table_1($value, $year);
            $value_min = round($table[$YEAR_MIN]['value']);
            $value_max = round($table[$YEAR_MAX]['value']);
            echo (
                "<p>" .
                    "<strong>$value Kč</strong> v roce <strong>$year</strong> má stejnou hodnotu jako " .
                    "<strong>$value_min Kč</strong> v roce <strong>$YEAR_MIN</strong> " .
                    "nebo <strong>$value_max Kč</strong> v roce <strong>$YEAR_MAX</strong>." .
                "</p>"
            );

            ?>

            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                    <th scope="col">Rok</th>
                    <th scope="col">Odpovídající hodnota</th>
                    <th scope="col">Koeficient</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    for ($y = $YEAR_MIN; $y <= $YEAR_MAX; $y++) {
                        echo '<tr';
                        if ($y == $year) {
                            echo ' class="table-primary"';
                        }
                        echo '>';
                        echo '<td>' . $y . '</td>';
                        echo '<td>' . round($table[$y]['value']) . ' Kč</td>';
                        echo '<td>' . round($table[$y]['coef'], 3) . '</td>';
                        echo '</tr>';
                    }
                ?>
                </tbody>
            </table>


        </div>


        <div>
            <h2>Odkazy</h2>

            <ul>
                <li>Data: <a href="https://www.czso.cz/csu/czso/mira_inflace">ČSÚ: Inflace - druhy, definice, tabulky</a></li>
                <li>API:
                    <a
                        href="<?php echo $api_url; ?>"
                    ><?php echo $api_url; ?>
                    </a>
                </li>
                <li>GitHub: <a href="https://github.com/martin-majlis/inflacni-kalkulacka.cz">https://github.com/martin-majlis/inflacni-kalkulacka.cz</a></li>
            </p>
        </div>
    </div>


</body>
</html>