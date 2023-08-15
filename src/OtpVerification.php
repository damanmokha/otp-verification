<?php

namespace Damanmokha\OtpVerification;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\DB;

class OtpVerification
{
    protected $twilioClient;
    protected $otpSize;

    public function __construct()
    {
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        $twilioPhoneNumber = config('services.twilio.from');

        if (!$twilioSid || !$twilioToken || !$twilioPhoneNumber) {
            throw new \Exception('Twilio credentials are missing. Make sure to set Twilio SID, token, and from number in your .env or services.php configuration.');
        }

        $this->twilioClient = new Client($twilioSid, $twilioToken);
        $this->otpSize = env('OTP_SIZE', 4);
    }

    public function send(string $phoneNumber, string $message)
    {
        // Check if an unused OTP already exists for the phone number
        $existingOtp = DB::table('otp')
            ->where('phonenumber', $phoneNumber)
            ->where('status', false)
            ->first();

        if ($existingOtp) {
            //Resend Otp
            $formattedMessage = str_replace('{otp}', $existingOtp->otp, $message);
            $this->sendOtpViaTwilio($phoneNumber, $formattedMessage);

            return $existingOtp;
        }

        $otp = $this->generateOtp();
        $formattedMessage = str_replace('{otp}', $otp, $message);

        $return = $this->saveOtp($phoneNumber, $otp);
        // Send OTP using Twilio
        $this->sendOtpViaTwilio($phoneNumber, $formattedMessage);

        return (object) $return;
    }

    public function verify(string $phoneNumber, string $otp)
    {
        $otpEntry = DB::table('otp')
            ->where('phonenumber', $phoneNumber)
            ->where('otp', $otp)
            ->where('status', false)
            ->first();

        if ($otpEntry) {
            // Mark OTP as used
            DB::table('otp')
                ->where('id', $otpEntry->id)
                ->update(['status' => true]);

            return true;
        }

        return false;
    }

    protected function generateOtp()
    {
        return strval(rand(pow(10, $this->otpSize - 1), pow(10, $this->otpSize) - 1));
    }

    protected function saveOtp(string $phoneNumber, string $otp)
    {
        return DB::table('otp')->insert([
             'phonenumber' => $phoneNumber,
             'otp' => $otp,
             'status' => false,
             'created_at' => now(),
             'updated_at' => now(),
         ]);
    }

    protected function sendOtpViaTwilio(string $phoneNumber, string $message)
    {

        try {
            $twillio = $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => config('services.twilio.from'), // Use the configured Twilio phone number
                    'body' => $message,
                ]
            );

            return $twillio;
        } catch (RestException $e) {
            // Handle Twilio API errors here
            // You can log the error or take other actions
            return null;
        }
    }
}
