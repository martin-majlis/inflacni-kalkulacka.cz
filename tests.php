<?php
// https://www.php.net/manual/en/function.assert.php
// TODO: Does not work as expected :/

// This is our function to handle
// assert failures
function assert_failure($file, $line, $assertion, $message)
{
    echo "The assertion $assertion in $file on line $line has failed: $message";
}

// This is our test function
function test_assert($parameter)
{
    assert(is_bool($parameter));
}

// Set our assert options
assert_options(ASSERT_ACTIVE,   true);
assert_options(ASSERT_BAIL,     true);
assert_options(ASSERT_WARNING,  false);
assert_options(ASSERT_CALLBACK, 'assert_failure');

// Make an assert that would fail
test_assert(1);


require_once('www/functions.php');

echo "\n2005 Base\n";

$conv_2005 = conversion_table_1(1000, 2005);

print_r($conv_2005);

print_r(conversion_table_1($conv_2005[2020]['value'], 2020));


echo "\n2020 Base\n";

$conv_2020 = conversion_table_1(1000, 2020);

print_r($conv_2020);

print_r(conversion_table_1($conv_2020[2005]['value'], 2005));

https://www.penize.cz/kalkulacky/znehodnoceni-koruny-inflace#inflace
print("\nMa byt 100000: " . conversion_table_1(100000, 2015)[2015]['value']);
print("\nMa byt 100700: " . conversion_table_1(100000, 2015)[2016]['value']);
print("\nMa byt 99305: " . conversion_table_1(100000, 2016)[2015]['value']);
print("\nMa byt 100000: " . conversion_table_1(100000, 2016)[2016]['value']);



assert('1 == 2');
assert(1 == 2);

echo "\nEND";

?>