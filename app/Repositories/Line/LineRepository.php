<?php

namespace App\Repositories\Line;

use App\Repositories\Common\CommonRepository;
use GuzzleHttp\Client;

class LineRepository extends CommonRepository
{

    protected $webhookStorePath;
    protected $client;

    public function __construct()
    {
        $this->webhookStorePath = storage_path('public/upload/line-webhook');
        $this->client = new Client([
            'base_uri' => 'https://api.line.me',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
            ]
        ]);
    }

    public function putWebhookRecord($record)
    {

        $filename = $this->webhookStorePath . '/record-' . date('Ymd') . '.json';
        return file_put_contents($filename, $record . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function getWebhookRecordDates()
    {
        $dates = [];
        $jsonFiles = glob($this->webhookStorePath . '/*.json');
        foreach ($jsonFiles as $file) {
            preg_match('/record-(\d{10})\.json$/', $file, $matches);
            if (isset($matches[1])) {
                $dates[] = $matches[1];
            }
        }
        return $dates;
    }

    public function getWebhookRecordsByDate($date)
    {
        $handler = fopen($this->webhookStorePath . '/record-' . $date . '.json', 'r');
        if (!$handler) {
            return [];
        }
        $records = [];
        while (($line = fgets($handler)) !== false) {
            $rawdata = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }
            $event = isset($rawdata['events'][0]) ? $rawdata['events'][0] : new \stdClass();
            if ($event['type'] !== 'message') {
                continue;
            }
            if ($event['message']['type'] !== 'text') {
                $message = $event['message']['type'];
            } else {
                $message = $event['message']['text'];
            }
            $records[] = [
                'userId' => $event['source']['userId'],
                'groupId' => $event['source']['type'] === 'group' ? $event['source']['groupId'] : null,
                'message' => $message,
                'datetime' => date('Y/m/d H:i:s', $event['timestamp'] / 1000),
                'timestamp' => $event['timestamp'],
            ];
        }
        fclose($handler);
        return $records;
    }

    public function reply($message, $replyToken)
    {
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
