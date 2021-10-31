<?php

define('APP_PATH', dirname(__DIR__));

/*
 * -------------------------------------------------------------------
 * Initialising Composer Packages....
 * -------------------------------------------------------------------
 */

include_once APP_PATH . '/vendor/autoload.php';

$santa = new secretSanta();
$santa->run(
    [
        ['name'=>'Test 1','email'=>'test1@example.com'],
        ['name'=>'Test 2','email'=>'test2@example.com'],
        ['name'=>'Test 3','email'=>'test3@example.com'],
    ]
);
// simple output
print_r($santa->getSentLog());