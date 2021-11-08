<?php

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Created by Michael Poletaew <poletaew@gmail.com>
 * at 21:55, 30.10.2021 GMT+4
 */

class extendedSecretSanta extends secretSanta
{
    protected array $mailConfig = [
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
        'isHtml' => false,
        'body' => 'Hello {{name}},

This year you are chosen as a Secret Santa for {{0}}.

Postal address: {{2}}

Presents should all be around {{currencySymbol}}{{itemValue}}.

Good luck and Merry Christmas,
Yours Secret Santa Randomizer'
    ];

    public function runFromCsv()
    {
        $filePath = __DIR__ . '/../source/' . $this->mailConfig['csvFile'];
        $finalArray = [];
        if (!file_exists($filePath)) {
            throw new Exception("CSV file '$filePath' is not found");
        }
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000, $this->mailConfig['csvSeparator'])) !== FALSE) {
                $finalArray[] = $data;
            }
            fclose($handle);
        }

        $this->run($finalArray);
    }

    protected function validateArray($usersArray): bool
    {
        // Check more than 3 participents
        if (sizeof($usersArray) < 3) {
            throw new Exception('A minimum of 3 secret santa participants is required');
        }

        // Check for duplicate emails
        $tmpEmails = [];
        foreach ($usersArray as $u) {
            $email = $this->getUserEmail($u);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email '$email' is invalid");
            }

            if (in_array($email, $tmpEmails)) {
                throw new Exception('Duplicate emails found. Users cannot have the same email address');
            }
            $tmpEmails[] = $email;
        }
        return true;
    }

    protected function getUserEmail($user)
    {
        return $user['email'] ?? $user[$this->mailConfig['receiverEmailPosition']];
    }

    protected function getUserName($user)
    {
        return $user['name'] ?? $user[$this->mailConfig['receiverNamePosition']];
    }

    protected function getMailBody($giver)
    {
        $replacements = [
            '{{name}}' => $this->getUserName($giver),
        ];

        $this->appendDynamicReplacements($giver['givingTo'], $replacements);

        return str_replace(array_keys($replacements), $replacements, $this->mailConfig['body']);
    }

    /**
     * @param array $replacements
     * Method try to replace all placeholders like `{{0}}` or `{{test}}` from CSV line item (by position)
     * or $mailConfig (by name)
     */
    private function appendDynamicReplacements(array $receiver, array &$replacements)
    {
        if(preg_match_all('/\{\{[^\}]+\}\}/m', $this->mailConfig['body'], $matches)){
            foreach ($matches[0] as $match){
                $key = str_replace(['{', '}'], '', $match);
                $value =  is_numeric($key) ? $receiver[(int)$key] : $this->mailConfig[$key];
                if (!empty($value) && !isset($replacements[$key])){
                    $replacements[$match] = $value;
                }
            }
        }
    }
}