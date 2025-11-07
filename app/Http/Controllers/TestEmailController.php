<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        $email = 's2ndungu@gmail.com';
        
        try {
            Mail::send('emails.test', [], function($message) use ($email) {
                $message->to($email)
                       ->subject('Bima Connect - Test Email');
            });
            
            return "Test email sent to {$email}";
        } catch (\Exception $e) {
            return "Failed to send test email: " . $e->getMessage();
        }
    }
}
