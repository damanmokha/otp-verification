# OTP Verification Package

A Laravel package for sending and verifying OTPs (One-Time Passwords) using Twilio.

## Installation

1. Install the package using Composer:

   ```bash
   composer require damanmokha/otp-verification

2. Publish the migrations to set up the required database table:
    ```
    php artisan vendor:publish --tag=otp-verification-migrations
    php artisan migrate
    ```

3. Add your Twilio credentials to your ```.env``` file:

    ```
    TWILIO_SID=YourTwilioSid
    TWILIO_AUTH_TOKEN=YourTwilioAuthToken
    TWILIO_PHONE_NUMBER=YourTwilioPhoneNumber
    ```
    
    ```Replace `YourTwilioSid`, `YourTwilioAuthToken`, and `YourTwilioPhoneNumber` with your actual Twilio credentials.```
    
    Don't have Twilio credentials? You can get them from [Twilio](https://www.twilio.com/).
    
4. Add your config to services array in ```app/config/services.php```

    ```
    <?php
    return [
        //...Previous keys//
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_PHONE_NUMBER'),
        ],
    ];
    ```
5. You can set `OTP_SIZE` in .env to configure custom OTP size, default is to `4`
    
## Usage

1. Basic usage
    ```
    use Damanmokha\OtpVerification\OtpVerification;
    $otp = new OtpVerification();

    //to send message
    $otpResponse = $otp->send($phoneNumber, $message);

    //to verify 

    $isVerified = $otp->verify($phoneNumber, $otp);
    ```
    
2. Complete controller
    
    ```
    <?php

    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use Damanmokha\OtpVerification\OtpVerification;
    
    class OtpController extends Controller
    {
        protected $otp;
    
        public function __construct()
        {
            $this->otp = new OtpVerification();
        }
    
        public function sendOtp(Request $request)
        {
            $phoneNumber = $request->input('phone_number');
            $message = "Your verification otp is: {otp}";
            $otpResponse = $this->otp->send($phoneNumber, $message);
    
            return $otpResponse;
        }
    
        public function verifyOtp(Request $request)
        {
            $phoneNumber = $request->input('phone_number');
            $otp = $request->input('otp');
            $isVerified = $this->otp->verify($phoneNumber, $otp);
    
            return ['verified' => $isVerified];
        }
    }
    ```

Visit the [GitHub repository](https://github.com/damanmokha/laravel-test/tree/dev-otp-verification) for more details and to explore the implementation.


    
## License

This package is open-sourced software licensed under the [MIT license](https://en.wikipedia.org/wiki/MIT_License).


Feel free to fork, clone, or use this demo application as a reference for implementing OTP verification in your Laravel projects.
