<?php

function sendSMS($username, $apiKey, $to, $message) {
    $url = 'https://api.africastalking.com/version1/messaging';

    // POST data
    $postData = http_build_query([
        'username' => $username,
        'to' => $to,
        'message' => $message
    ]);

    // cURL configuration
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    // Execute request
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        echo 'Success: '.$response;
    }

    curl_close($ch);
}

// Example usage
$username = 'sandbox'; 
$apiKey = 'atsk_410566827bf297c71d7d7388bcd46eaf8282fda24c69c1c39b363f4fb3eac56ab065971d';   
$to = '+254726599429';       
$message = 'Hello from Bima Connect'; 

sendSMS($username, $apiKey, $to, $message);
