<?php 
    //
    // Set this appropriately for your time zone
    //
    date_default_timezone_set('Europe/London');

    // Set this appropriately for your website.  The leading . is significant...
    $cookie_domain = '.example.com';

    //
    // Your access key, secret key, and SOAP Endpoint are all available in the
    // Admin section of the Marketo Lead Management appliaction under "SOAP API Setup"
    //
    
    $access_key = 'company1_2312312312312312312312';
    $secret_key = '123ABC123ABC123ABC123ABC123ABC123ABC123ABC12';

    //
    // The endpoint is in the "SOAP API Setup" page in the Marketo Admin section
    // ex. $soap_end_point = 'https://xx-1.marketo.com/soap/mktows/';
    //
    $soap_end_point = 'https://na-l.marketo.com/soap/mktows/1_6';

    //
    // Errors are sent to this email address.  Your web server
    // must be configured to send email for this to work correctly.
    //
    $error_email_address = 'email@example.com';

?>