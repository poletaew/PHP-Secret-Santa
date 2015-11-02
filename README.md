# Secret Santa
This is a simple script which allows you to send secret santa emails out, ensuring everyone gets their secret santa assigned randomly, and can't get assigned themselves.

## Dependancies / Installation
This script has been built to use [PHP Mailer](https://github.com/PHPMailer/PHPMailer), which is loaded in via [composer](https://getcomposer.org/).

To install the composer dependancies, run the following command from the repository directory on your server:

    bin/composer.phar install

## Basic Usage
To run, all you need to pass into the script is an array containing (a minimum of) 3 participants

    $santa = new SecretSanta();
    $santa->run(
        ['name'=>'Test 1','email'=>'test1@example.com'],
        ['name'=>'Test 2','email'=>'test2@example.com'],
        ['name'=>'Test 3','email'=>'test3@example.com'],
    );

Have a look at the [examples directory](https://github.com/nickedwards/php-secret-santa/tree/master/examples/) for more detailed examples, and extra functionality.

## Examples
See the [examples directory](https://github.com/nickedwards/php-secret-santa/tree/master/examples/) for some examples showing the different functionality of this script.