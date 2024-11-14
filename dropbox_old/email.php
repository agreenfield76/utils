<?php

// $me = "W.Edmunds@exeter.ac.uk,W.Edmunds@exeter.ac.uk";
// $me = "W.Edmunds@exeter.ac.uk";
$me = "W.Edmunds@exeter.ac.uk, W.Edmunds@@exeter.ac.uk";

function check_email_address($email)
{
        // This checks that there is exactly one `@' sign as well:
        return preg_match("/^([A-Za-z][A-Za-z0-9\._-]*)@(\w[\w\d\.-]*)$/", $email);
}

function check_email($emaillist)
{
        // This checks number of email addresses and checks each one:
        //$emailarray[] = mb_split(",\s?", $emaillist);
        $emailarray = preg_split("/,\s?/", $emaillist);
        foreach ($emailarray as $email) {
                echo "1 $email\n";
                if (check_email_address($email))
                         echo "2 $email\n";
        }
}

// echo check_email_address($me) . "\n";
echo check_email($me) . "\n";

?>
