<?php

namespace App\Services;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ZoomService
{
    private string $apiUrl = 'https://api.zoom.us';

    private Client $client;

    private string $token;

    public function __construct()
    {
        $this->client = $this->createClient();
        $this->token = $this->createToken();
    }

    private function createClient(): Client
    {
        return new Client([
            'base_uri' => $this->apiUrl,
        ]);
    }

    private function createToken(): string
    {
        $key = env('ZOOM_API_SECRET');
        $payload = [
            'iss' => env('ZOOM_API_KEY'),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * @throws GuzzleException
     */
    public function create(array $report): array
    {
        $response = $this->client->request('POST', '/v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
            'json' => [
                'topic' => $report['topic'],
                'agenda' => $report['description'],
                'type' => 2,
                'start_time' => $report['start_datetime'],
                'duration' => $report['duration'],
                'password' => '12345678',
            ],
        ]);

        $meetingInfo = json_decode($response->getBody(), true);

        if (str_contains($meetingInfo['start_time'], 'T')) {
            $meetingInfo['start_time'] = rtrim($meetingInfo['start_time'], 'Z');
            $startDateTime = explode('T', $meetingInfo['start_time']);
            $meetingInfo['start_time'] = "{$startDateTime[0]} {$startDateTime[1]}";
        }

        return $meetingInfo;
    }

    /**
     * @throws GuzzleException
     */
    public function remove(int $meetingId): void
    {
        $this->client->request('DELETE', "/v2/meetings/$meetingId", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function update(array $report): array
    {
        $response = $this->client->request('PATCH', "/v2/meetings/{$report['meeting_id']}", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
            'json' => [
                'topic' => $report['topic'],
                'agenda' => $report['description'],
                'type' => 2,
                'start_time' => $report['start_datetime'],
                'duration' => $report['duration'],
                'password' => '12345678',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getMeeting(int $meetingId)
    {
        $response = $this->client->request('GET', "/v2/meetings/$meetingId", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
        ]);

        return json_decode($response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function getAllMeetings()
    {
        $response = $this->client->request('GET', '/v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
        ]);
        $data = json_decode($response->getBody());

        return $data->meetings;
    }
}
