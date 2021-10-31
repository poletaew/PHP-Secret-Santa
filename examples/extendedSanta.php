<?php
/**
 * Extended Santa Script allows you to use csv files with custom set of fields.
 * The only thing you have to have receiver name & email in csv items. The rest fields are fully customizable.
 * Just use field position of your CSV items in `body` template like {{1}}. You also may use in the template
 * any variables you specified in $config calling them by name, like `{{itemValue}}`.
 */
define('APP_PATH', dirname(__DIR__));


// Don't forget to run `php composer.phar install` before


include_once APP_PATH . '/vendor/autoload.php';

$config = [
    'csvFile' => 'list.txt',//put the file in `source` directory
    'csvSeparator' => ";",
    'itemValue' => 20,
    'currencySymbol' => '$',
    'fromName' => 'Secret Santa Randomizer',
    'fromEmail' => 'noreply@northpole.com',
    'replyToName' => 'Santa',
    'replyToEmail' => 'santa@northpole.com',
    'subject' => 'Secret Santa',
    'receiverNamePosition' => 0,
    'receiverEmailPosition' => 1,
    'body' => 'Hello {{name}},

This year you are chosen as a Secret Santa for {{0}}.

Postal address: {{2}}

Presents should all be around {{currencySymbol}}{{itemValue}}.

Good luck and Merry Christmas,
Yours Secret Santa Randomizer'
];

$santa = new extendedSecretSanta($config);

try {
    $santa->runFromCsv();
} catch (Exception $e) {
    die($e->getMessage());
}

// simple output
print_r($santa->getSentLog());