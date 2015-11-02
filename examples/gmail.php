<?php

define('APP_PATH', dirname(__DIR__));

/*
 * -------------------------------------------------------------------
 * Initialising Composer Packages....
 * -------------------------------------------------------------------
 */

include_once APP_PATH . '/vendor/autoload.php';

$santa = new SecretSanta();

$smtpConfig = [
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    'debugLevel' => 2,
    // ssl (deprecated) or tls
    'encryption' => 'tls',
    'host' => 'smtp.gmail.com',
    'port' => '587',
    'username' => 'emailAddress@gmail.com',
    'password' => 'yourPassword',
];
$santa->useSMTP($smtpConfig);

$santa->run(
    [
        ['name'=>'Test 1','email'=>'test1@example.com'],
        ['name'=>'Test 2','email'=>'test2@example.com'],
        ['name'=>'Test 3','email'=>'test3@example.com'],
    ]
);
// simple output
print_r($santa->getSentEmails());