<?php
/*
 * PHP Secret Santa
 * A simple PHP Secret Santa Script
 *
 * @Author Nick Edwards (2015)
 *
 * Basic Usage:
 *     $santa = new secretSanta();
 *     $santa->run([
 *         ['name'=>'Test 1','email'=>'test1@example.com'],
 *         ['name'=>'Test 2','email'=>'test2@example.com'],
 *         ['name'=>'Test 3','email'=>'test3@example.com'],
 *     ]);
 */
Class secretSanta 
{
    private $useSmtp = false;
    private $smtpConfig = [
        'debugLevel' => 0,
        'encryption' => 'tls',
        'host' => 'smtp.gmail.com',
        'port' => '587',
        'username' => 'example@gmail.com',
        'password' => '',
    ];

    private $mailConfig = [
        'itemValue' => 10,
        'currencySymbol' => '£',
        'fromName' => 'Santa',
        'fromEmail' => 'santa@northpole.com',
        'replyToName' => 'Santa',
        'replyToEmail' => 'santa@northpole.com',
        'subject' => 'Secret Santa',
        'body' => 'Hello {{name}}, 

For Secret Santa this year you will be buying a present for {{givingToName}} ({{givingToEmail}})

Presents should all be around {{price}}

Good luck and Merry Christmas,
Santa'
    ];

    private $sentLog = [];
    private $assignedUsers = [];

    /**
     * Initialises variables where needed
     * 
     * @param array $config configuration settings
     * @return true/false on success/failure
     */
    public function __construct($config = []) 
    {
        // set any details passed in via the config array
        $keys = array_keys($this->mailConfig);
        foreach ($keys as $key) {
            if (isset($config[$key])) $this->mailConfig[$key] = $config[$key];
        }
    }

    /**
     * Use SMTP instead of local email server
     * 
     * @param  array  $config configuration settings
     * @return true on success
     */
    public function useSMTP($config = []) 
    {
        $this->useSmtp = true;
        $keys = array_keys($this->smtpConfig);
        foreach ($keys as $key) {
            if (isset($config[$key])) $this->smtpConfig[$key] = $config[$key];
        }
        return true;
    }
    
    /**
     * Runs the secret santa script on an array of users 
     * Checks to see if the array is valid
     * Everyone is assigned a secret santa (that is not themselves)
     * Emails are sent out
     * 
     * @param array $usersArray array of users
     * @return true/false on success/failure
     */
    public function run($usersArray) 
    {
        try {
            //Check array is safe to use
            if ($this->validateArray($usersArray)) {
                $this->assignUsers($usersArray);
                return $this->sendEmails();
            }
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage() . "<br />\n";
        }
    }
    
    /**
     * Validate Array of users
     * Ensure array is safe to use in Secret Santa Script
     * 
     * @param array $usersArray array of users
     * @return true if valid. Exception thrown if not.
     */
    private function validateArray($usersArray) 
    {
        // Check more than 3 participents
        if (sizeof($usersArray) < 3) {
            throw new Exception('A minimum of 3 secret santa participants is required');
        }

        // Check for duplicate emails
        $tmpEmails = [];
        foreach ($usersArray as $u) {
            if (in_array($u['email'], $tmpEmails)) {
                throw new Exception('Duplicate emails found. Users cannot have the same email address');
            }
            $tmpEmails[] = $u['email'];
        }
        return true;
    }
    
    /**
     * Assign each user in the array their secret santa
     * Make sure everyone is assigned randomly
     * Make sure no one is assigned themselves
     *
     * @param array $usersArray array of users
     * @return array of assigned users
     */
    private function assignUsers($usersArray) 
    {
        $givers     = $usersArray;
        $receivers  = $usersArray;

        foreach($givers as $i => $user) {
            $notAssigned = true;
            while ($notAssigned) {
                // randomly choose a person
                $randomUser = mt_rand(0, sizeof($receivers)-1);

                // if chosen user isn't themselves
                if (
                    $user['email'] !== $receivers[$randomUser]['email'] 
                    && (!isset($receivers[$randomUser]['recievingFrom']) || $receivers[$randomUser]['recievingFrom'] != $user['email'])
                ) {
                    $receivers[$randomUser]['recievingFrom'] = $user['email'];
                    // assign the user the randomly picked user
                    $givers[$i]['givingTo'] = $receivers[$randomUser];
                    
                    // remove them from future receivers list
                    unset($receivers[$randomUser]);

                    // reset array keys allowing next iteration
                    $receivers = array_values($receivers);

                    $notAssigned = false;
                } else if (sizeof($receivers) === 1) {
                    // if only one person left, and they've been assigned themselves
                    // swap givingTo person from the first user
                    $givers[$i]['givingTo'] = $givers[0]['givingTo'];
                    $givers[0]['givingTo'] = $givers[$i];
                    $notAssigned = false;
                }
            }
        }

        $this->assignedUsers = $givers;
        return $this->assignedUsers;
    }
    
    /**
     * Send Emails
     * Email all users to tell them who they've been assigned for secret santa
     * 
     * @return true if valid. Exception thrown if not.
     */
    private function sendEmails() 
    {
        if (sizeof($this->assignedUsers) == 0) {
            throw new Exception('Users have not been assigned a secret santa yet.');
        }
        
        foreach($this->assignedUsers as $giver) {
            // replace keywords in the body of the email
            $replacements = [
                '{{name}}' => $giver['name'],
                '{{givingToName}}' => $giver['givingTo']['name'],
                '{{givingToEmail}}' => $giver['givingTo']['email'],
                '{{price}}' => $this->mailConfig['currencySymbol'] . sprintf("%01.2f", $this->mailConfig['itemValue']),
            ];
            $mailBody = str_replace(array_keys($replacements), $replacements, $this->mailConfig['body']);

            // log that the email has been sent
            $this->sentLog[] = $giver['name'] . ' (' . $giver['email'] . ')' . ' should get a gift for ' . $giver['givingTo']['name'] . ' (' . $giver['givingTo']['email'] . ')';

            // send emails using phpMailer
            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';

            // set SMTP settings if being used
            if ($this->useSmtp) {
                $mail->IsSMTP();
                $mail->SMTPDebug = $this->smtpConfig['debugLevel'];
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = $this->smtpConfig['encryption'];
                $mail->Host = $this->smtpConfig['host'];
                $mail->Port = $this->smtpConfig['port'];
                $mail->Username = $this->smtpConfig['username'];
                $mail->Password = $this->smtpConfig['password'];
            }

            // set shared mailer settings
            $mail->From = $this->mailConfig['fromEmail'];
            $mail->FromName = $this->mailConfig['fromName'];
            $mail->AddReplyTo($this->mailConfig['replyToEmail'], $this->mailConfig['replyToName']);
            $mail->Subject = $this->mailConfig['subject'];
            $mail->Body = $mailBody;
            $mail->IsHTML(false);
            $mail->AddAddress($giver['email'], $giver['name']);

            // send email
            if (!$mail->send()) {
                throw new Exception($mail->ErrorInfo);
            } else {
                echo "sent<br />\n";
                $mail->ClearAddresses();
            }
        }
        return true;
    }
    
    /**
     * Get a list of all sent emails
     * Useful to keep a list of who gets who in secret santa, incase you need to remind people who they got, or re-jig the chosen people if someone drops out
     * 
     * @return Array of emails that were sent out
     */
    public function getSentEmails() 
    {
        return $this->sentLog;
    }
}
