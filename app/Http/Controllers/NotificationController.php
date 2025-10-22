<?php

namespace App\Http\Controllers;

use App\Mail\CustomerBalanceEmail; // Ensure you are importing the correct Mailable class
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Correctly import the Log facade
use App\Models\Policy;
use App\Mail\RenewalNotification;
use App\Models\RenewalNotice;

//use AfricasTalking\SDK\AfricasTalking;

class NotificationController extends Controller
{
    public function sendRenewalEmail($id)
    {
        $policy = Policy::findOrFail($id);
        Mail::to($policy->customer->email)->send(new RenewalNotification($policy));
        // record notice
        try {
            RenewalNotice::create([
                'fileno' => $policy->fileno,
                'policy_id' => $policy->id,
                'customer_code' => $policy->customer_code,
                'channel' => 'email',
                'sent_at' => now(),
                'sent_by' => auth()->id() ?? null,
                'message_id' => null,
                'meta' => null,
            ]);
        } catch (\Throwable $ex) {
            Log::error('NotificationController: failed to record renewal notice', ['error' => $ex->getMessage()]);
        }
        return back()->with('success', 'Renewal email sent successfully.');
    }

    public function sendRenewalSms($id)
    {
        $policy = Policy::findOrFail($id);
        $customer = $policy->customer;
        $phone = '254' . substr($customer->phone, -9);

        $message = "Dear {$customer->customer_name},\n";
        $message .= "Your policy {$policy->policy_no} for ";
        if (in_array($policy->policy_type_id, [35, 36, 37])) {
            $message .= "{$policy->make} {$policy->model} (Reg: {$policy->reg_no})";
        } else {
            $message .= "{$policy->description}";
        }
        $message .= " is expiring on " . \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') . ".\n";
        $message .= "Please contact us to renew.\n";
        $message .= "File No: {$policy->fileno}";

        $this->send_message_bulksms($phone, $message);

        // record sms notice
        try {
            RenewalNotice::create([
                'fileno' => $policy->fileno,
                'policy_id' => $policy->id,
                'customer_code' => $policy->customer_code,
                'channel' => 'sms',
                'sent_at' => now(),
                'sent_by' => auth()->id() ?? null,
                'message_id' => null,
                'meta' => ['body' => $message],
            ]);
        } catch (\Throwable $ex) {
            Log::error('NotificationController: failed to record renewal sms notice', ['error' => $ex->getMessage()]);
        }

        return back()->with('success', 'Renewal SMS sent successfully.');
    }
    public function sendEmail($customerCode, Request $request)
    {
        // Log the attempt to send an email
        Log::info("Attempting to send email to customer with code: {$customerCode}");
    
        // Fetch the customer data along with their policies
        $customer = Customer::with('policies.policyType')->where('customer_code', $customerCode)->first();
    
        if (!$customer) {
            Log::warning("Customer with code: {$customerCode} not found.");
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }
    
        // Prepare the balance data
        $balances = $customer->policies->map(function ($policy) {
            return [
                'fileno' => $policy->fileno, // Assuming policy_no is the field for serial number
                'type' => $policy->policyType->type_name, // Make sure the PolicyType model has a 'type_name' attribute
                'gross_premium' => $policy->gross_premium, // Include gross premium
                'paid_amount' => $policy->paid_amount, // Include paid amount
                'balance' => $policy->balance,
            ];
        });
    
        // Try to send the email using the Mailable
        try {
            Mail::to($customer->email)->send(new CustomerBalanceEmail($customer->customer_name, $balances));
            Log::info("Email successfully sent to: {$customer->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$customer->email}. Error: {$e->getMessage()}");
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    
        // Return a success response
        return response()->json(['success' => true, 'message' => 'Email sent successfully']);
    }
    
    
    public function sendSMS($customerCode, Request $request)
    {
        $customer = Customer::where('customer_code', $customerCode)->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $balance = number_format($request->input('balance'), 2);
        $phone = '254' . substr($customer->phone, -9);
        $msg = "Hello {$customer->customer_name}, your current balance is KES {$balance}.";
        
        return $this->send_message_bulksms($phone, $msg);
        //return $this->send_message_africastalking($phone, $msg);
    }
    
    // Send SMS using BulkSMS.com 
    public function send_message_bulksms($phone, $msg){
        $username = env('BULKSMS_USERNAME');
        $password   = env('BULKSMS_PASSWORD');
        $mode   = env('BULKSMS_MODE');
        
        if($mode=="sandbox"){
            $phone = '+254' . substr('0729502099', -9);    
        }
        $phone = '+254' . substr($phone, -9);
        
        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("$username:$password"),
            ])->timeout(20)
              ->post('https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30', [
                  'to' => $phone,
                  'body' => $msg,
            ]);
            
        Log::info('BulkSMS Response:', [
            'phone' => $phone, 
            'message' => $msg,
            'response' => $response
        ]);
        
        // Handle the response
        if ($response->successful()) {
            $data = $response->json();
            return response()->json(['success' => true, 'message' => 'SMS sent successfully', 'data'=>$data]);
        } else {
            $statusCode = $response->status();
            $error = json_decode($response->body());
            return response()->json(['success' => false, 'message' => 'Failed to send SMS. '. $error->title]);
        }
    }

    // Send SMS using AfricasTalking
    public function send_message_africastalking($phone, $msg)
    {
        $username = env('AFRICASTALKING_USERNAME'); // Use 'sandbox' for test environment
        $apiKey = env('AFRICASTALKING_API_KEY');   // Your Africa's Talking API key
        $mode = env('AFRICASTALKING_MODE');        // Mode (sandbox/production)
    
        // Format phone number
        if($mode == 'sandbox'){
            $phone = '+254' . substr('0726599429', -9);  
        }
        $phone = '+254' . substr($phone, -9);
    
        // Make HTTP request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apiKey' => $apiKey,
        ])->timeout(20)->asForm()->post('https://api.africastalking.com/version1/messaging', [
            'username' => $username,
            'to' => $phone,
            'message' => $msg,
            'enqueue' => 1
        ]);
    
        // Log the response
        Log::info('AfricasTalking Response:', [
            'phone' => $phone,
            'message' => $msg,
            'response' => $response->body() // Log readable response
        ]);
    
        // Handle the response
        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => $response->body()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS. '.$response->body(),
                'response' => $response->body(),
                'username' => $username,
                'to' => $phone,
                'sms' => $msg,
                'mode' => $mode,
                'apiKey' => $apiKey,
            ]);
        }
    }

}
