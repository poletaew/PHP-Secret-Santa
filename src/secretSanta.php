<?php
/*
 * PHP Secret Santa
 * A simple PHP Secret Santa Script
 *
 * @Author Nick Edwards (2015)
 *
 * Basic Usage:
 *     $santa = new SecretSanta();
 *     $santa->run(
 *         ['name'=>'Test 1','email'=>'test1@example.com'],
 *         ['name'=>'Test 2','email'=>'test2@example.com'],
 *         ['name'=>'Test 3','email'=>'test3@example.com'],
 *     );
 */
Class SecretSanta {
    private $itemValue = 10;
    private $currencySymbol = '£';

    private $useSmtp = false;
    private $smtpConfig = [
        'debugLevel' => 0,
        'encryption' => 'tls',
        'host' => 'smtp.gmail.com',
        'port' => '587',
        'username' => 'example@gmail.com',
        'password' => '',
    ];

    private $mailFromName = 'Santa';
    private $mailFromEmail = 'santa@northpole.com';
    private $mailReplyToName = 'Santa';
    private $mailReplyToEmail = 'santa@northpole.com';
    private $mailSubject = 'Secret Santa';
    private $mailBody = "Hello {{name}}, 
For Secret Santa this year you will be buying a present for {{givingToName}} ({{givingToEmail}})

Presents should all be around {{price}}

Good luck and Merry Christmas,
Santa";

    private $sentLog = [];
    private $assignedUsers = [];

    /**
     * Construct
     * Sets variables where needed
     * 
     * @param $usersArray Array
     * @return true/false on success/failure
     */
    public function __construct($config = []) {
        // set any details passed in via the config array
        if (isset($config['itemValue']) && is_numeric($config['itemValue'])) {
            $this->itemValue = $config['itemValue'];
        }
        if (isset($config['currencySymbol'])) $this->currencySymbol = $config['currencySymbol'];
        if (isset($config['mailFromName'])) $this->mailFromName = $config['mailFromName'];
        if (isset($config['mailFromEmail'])) $this->mailFromEmail = $config['mailFromEmail'];
        if (isset($config['mailReplyToName'])) $this->mailReplyToName = $config['mailReplyToName'];
        if (isset($config['mailReplyToEmail'])) $this->mailReplyToEmail = $config['mailReplyToEmail'];
        if (isset($config['mailSubject'])) $this->mailSubject = $config['mailSubject'];
        if (isset($config['mailBody'])) $this->mailBody = $config['mailBody'];
    }

    /**
     * use SMTP
     * Use SMTP instead of local email server
     * @param  array  $config [description]
     * @return true on success
     */
    public function useSMTP($config = []) {
        $this->useSmtp = true;
        $keys = array_keys($this->smtpConfig);
        foreach ($keys as $key) {
            if (isset($config[$key])) $this->smtpConfig[$key] = $config[$key];
        }
        return true;
    }
    
    /**
     * Run
     * Runs the secret santa script on an array of users 
     * Checks to see if the array is valid
     * Everyone is assigned a secret santa (that is not themselves)
     * Emails are sent out
     * 
     * @param $usersArray Array of users
     * @return true/false on success/failure
     */
    public function run($usersArray){
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
     * @param $usersArray Array of users
     * @return true if valid. Exception thrown if not.
     */
    private function validateArray($usersArray){
        // Check more than 3 participents
        if (sizeof($usersArray) < 3){
            throw new Exception('A minimum of 3 secret santa participants is required');
        }

        // Check for duplicate emails
        $tmpEmails = [];
        foreach ($usersArray as $u) {
            if (in_array($u['email'], $tmpEmails)){
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
     * @param $usersArray of users
     * @return array of assigned users
     */
    private function assignUsers($usersArray){
        $givers     = $usersArray;
        $receivers  = $usersArray;

        foreach($givers as $i => $user){
            $notAssigned = true;
            while($notAssigned){
                // randomly choose a person
                $randomUser = mt_rand(0, sizeof($receivers)-1);

                // if chosen user isn't themselves
                if($user['email'] !== $receivers[$randomUser]['email']) {
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
     */
    private function sendEmails() {
        if (sizeof($this->assignedUsers) == 0) {
            throw new Exception('Users have not been assigned a secret santa yet.');
        }
        
        foreach($this->assignedUsers as $giver){
            // replace keywords in the body of the email
            $replacements = [
                '{{name}}' => $giver['name'],
                '{{givingToName}}' => $giver['givingTo']['name'],
                '{{givingToEmail}}' => $giver['givingTo']['email'],
                '{{price}}' => $this->currencySymbol . sprintf("%01.2f", $this->itemValue),
            ];
            $mailBody = str_replace(array_keys($replacements), $replacements, $this->mailBody);

            // log that the email has been sent
            $this->sentLog[] = $giver['name'] . ' (' . $giver['email'] . ')' . ' should get a gift for ' . $giver['givingTo']['name'] . ' (' . $giver['givingTo']['email'] . ')';

            // send emails using phpMailer
            $mail = new PHPMailer;

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
            $mail->From = $this->mailFromEmail;
            $mail->FromName = $this->mailFromName;
            $mail->AddReplyTo($this->mailReplyToEmail, $this->mailReplyToName);
            $mail->Subject = $this->mailSubject;
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
    }
    
    /**
     * Get a list of all sent emails
     * Useful to keep a list of who gets who in secret santa, incase you need to remind people who they got, or re-jig the chosen people if someone drops out
     * @return Array of emails that were sent out
     */
    public function getSentEmails() {
        return $this->sentLog;
    }
}