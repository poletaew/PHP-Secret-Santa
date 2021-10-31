# Extended Secret Santa
Based a simple script which allows you to send secret santa emails out, ensuring everyone gets their secret santa assigned randomly, and can't get assigned themselves.

Extended version allows you to use csv files with custom set of fields. 
The only thing you have to have receiver name & email in csv items. The rest fields are fully customizable.
You may use field position of your CSV items in `body` template like `{{1}}`. You also may use in the template any variables you specified in `mailConfig` calling them by name, like `{{itemValue}}`.

## Dependancies / Installation
This script has been built to use [PHP Mailer](https://github.com/PHPMailer/PHPMailer), which is loaded in via [composer](https://getcomposer.org/).

To install the composer dependancies, run:

    bin/composer.phar install

## Basic Usage
To run, all you need to put the CSV file in `source` director and pass file name into the script. 
Be sure that CSV containing a minimum of 3 participants.

    $santa = new extendedSecretSanta(['csvFile' => 'list.txt']);
    $santa->runFromCsv();

Have a look at the [examples directory](https://github.com/poletaew/PHP-Secret-Santa/tree/master/examples) for more detailed examples, and extra functionality.

## Examples
See the [examples directory](https://github.com/poletaew/PHP-Secret-Santa/tree/master/examples) for some examples showing the different functionality of this script.
