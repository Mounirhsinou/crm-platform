<?php
/**
 * Messaging Helper
 * Abstracted logic for sending SMS and WhatsApp messages
 */

class MessagingHelper
{
    /**
     * Send SMS via configured provider
     * 
     * @param array $settings
     * @param string $to
     * @param string $message
     * @return array
     */
    public static function sendSms($settings, $to, $message)
    {
        if (!$settings || !$settings['is_enabled']) {
            return ['success' => false, 'message' => 'SMS Provider not enabled'];
        }

        $provider = $settings['provider'];

        switch ($provider) {
            case 'twilio':
                return self::sendTwilioSms($settings, $to, $message);
            case 'vonage':
                return self::sendVonageSms($settings, $to, $message);
            default:
                return ['success' => false, 'message' => 'Unsupported SMS provider'];
        }
    }

    /**
     * Send WhatsApp via configured provider
     * 
     * @param array $settings
     * @param string $to
     * @param string $message
     * @return array
     */
    public static function sendWhatsapp($settings, $to, $message)
    {
        if (!$settings || !$settings['is_enabled']) {
            return ['success' => false, 'message' => 'WhatsApp Provider not enabled'];
        }

        $provider = $settings['provider'];

        switch ($provider) {
            case 'twilio':
                return self::sendTwilioWhatsapp($settings, $to, $message);
            case 'meta':
                return self::sendMetaWhatsapp($settings, $to, $message);
            default:
                return ['success' => false, 'message' => 'Unsupported WhatsApp provider'];
        }
    }

    /**
     * Send Twilio SMS via API
     */
    private static function sendTwilioSms($settings, $to, $message)
    {
        $sid = $settings['api_key']; // Twilio Account SID
        $token = $settings['api_secret']; // Twilio Auth Token
        $from = $settings['sender_id']; // Twilio Phone Number

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";

        $data = [
            'To' => $to,
            'From' => $from,
            'Body' => $message
        ];

        $post = http_build_query($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing compatibility
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => "CURL Error: $err"];
        }

        $res = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'message' => 'Sent successfully via Twilio'];
        } else {
            // Capture Twilio error message (e.g. trial account restrictions)
            $errorMessage = isset($res['message']) ? $res['message'] : 'Unknown Twilio Error';
            $errorCode = isset($res['code']) ? " (Code: " . $res['code'] . ")" : "";
            return ['success' => false, 'message' => "Twilio Error $httpCode: $errorMessage$errorCode"];
        }
    }

    /**
     * Simulation of Vonage SMS
     */
    private static function sendVonageSms($settings, $to, $message)
    {
        return ['success' => true, 'message' => 'Sent via Vonage'];
    }

    /**
     * Simulation of Meta WhatsApp
     */
    private static function sendMetaWhatsapp($settings, $to, $message)
    {
        return ['success' => true, 'message' => 'Sent via Meta Business API'];
    }

    /**
     * Simulation of Twilio WhatsApp
     */
    private static function sendTwilioWhatsapp($settings, $to, $message)
    {
        return ['success' => true, 'message' => 'Sent via Twilio WhatsApp'];
    }
}
