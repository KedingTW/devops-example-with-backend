<?php

namespace App\Services\Line;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.line.me',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
            ]
        ]);
    }

    public function reply($message, $replyToken)
    {
        Log::debug($message);
        Log::debug($replyToken);
        return $this->client->request('POST', '/v2/bot/message/reply', [
            'json' => [
                'replyToken' => $replyToken,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ]
                ]
            ]
        ]);
    }
}
