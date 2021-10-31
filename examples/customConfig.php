<?php

define('APP_PATH', dirname(__DIR__));

/*
 * -------------------------------------------------------------------
 * Initialising Composer Packages....
 * -------------------------------------------------------------------
 */

include_once APP_PATH . '/vendor/autoload.php';

$customConfig = [
    'itemValue' => 5,
    'currencySymbol' => '$',
    'mailFromName' => 'Santa',
    'mailFromEmail' => 'santa@companyName.com',
    'mailFromName' => 'Your Name',
    'mailReplyToEmail' => 'yourName@companyName.com',
    'mailSubject' => 'Your Secret Santa person is...',
    // You can replace keywords in the body of the email:
    // Current replacements are:
    // $replacements = [
    //     '{{name}}' => $giver['name'],
    //     '{{givingToName}}' => $giver['givingTo']['name'],
    //     '{{givingToEmail}}' => $giver['givingTo']['email'],
    //     '{{price}}' => $this->currencySymbol . sprintf("%01.2f", $this->itemValue),
    // ];
    'mailBody' => "Hello {{name}}, 
For Secret Santa this year you will be buying a present for {{givingToName}} ({{givingToEmail}})

Presents should all be around {{price}}

We will be doing the gift exchange on December the 18th.

Good luck and Merry Christmas,
Santa"
];

$santa = new secretSanta($customConfig);
$santa->run(
    [
        ['name'=>'Test 1','email'=>'test1@example.com'],
        ['name'=>'Test 2','email'=>'test2@example.com'],
        ['name'=>'Test 3','email'=>'test3@example.com'],
    ]
);
// simple output
print_r($santa->getSentLog());