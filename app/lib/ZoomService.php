<?php

namespace App\lib;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class ZoomService
{
    private $accountId;
    private $clientId;
    private $clientSecret;
    private $userId; // معمولا 'me' یا ایمیل اکانت زوم

    public function __construct()
    {
        $this->accountId    = config('services.zoom.account_id');
        $this->clientId     = config('services.zoom.client_id');
        $this->clientSecret = config('services.zoom.client_secret');
        $this->userId       = config('services.zoom.user_id', 'me');
    }

    /**
     * گرفتن Access Token (و ذخیره در کش)
     */
    private function getAccessToken()
    {
        return Cache::remember('zoom_access_token', 3500, function () {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->asForm()->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $this->accountId,
            ]);

            if ($response->failed()) {
                throw new Exception('Zoom Token Error: ' . $response->body());
            }

            return $response->json()['access_token'];
        });
    }

    /**
     * ساخت یک Meeting زمان‌دار
     */
    public function createMeeting($topic, $localDateTime, $localTimezone = 'Asia/Tehran', $duration = 60)
    {
        $accessToken = $this->getAccessToken();

        // تبدیل زمان محلی به UTC ISO8601 (با Z در انتها)
        $dtLocal = new \DateTime($localDateTime, new \DateTimeZone($localTimezone));
        $dtLocal->setTimezone(new \DateTimeZone('UTC'));
        $utcStart = $dtLocal->format('Y-m-d\TH:i:s\Z');

        $response = Http::withToken($accessToken)
            ->post("https://api.zoom.us/v2/users/{$this->userId}/meetings", [
                'topic'      => $topic,
                'type'       => 2, // Scheduled meeting
                'start_time' => $utcStart,
                'duration'   => (int)$duration,
                'settings'   => [
                    'host_video'        => true,
                    'participant_video' => true,
                    'waiting_room'      => true,
                    'join_before_host'  => false,
                ]
            ]);

        if ($response->failed()) {
            dd($response->body());
            throw new Exception('Zoom Meeting Error: ' . $response->body());
        }

        return $response->json(); // شامل join_url و start_url
    }
}
