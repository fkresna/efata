<?php
namespace App\Twilio;

class Twilio
{
    private $twilioClient;

    public function __construct(TwilioClientInterface $twilioClient)
    {
        $this->twilioClient = $twilioClient;
    }

    public function sendBirthdayText($number)
    {
        return $this->twilioClient->sendtext($number, 'Happy Birthday');
    }
}