<?php

namespace App\Http\Controllers;

use Aws\BedrockAgentRuntime\BedrockAgentRuntimeClient;
use GuzzleHttp\Pool;
use GuzzleHttp\Exception\RequestException;

use App\Services\Line\LineService;
use Exception;

class WebhookController extends Controller
{
    protected $accessToken = 'cUYfqJxtscVU6X5VTCaJVzuilshsxOfBB01idRGE+agy5lhK/k6d/Vov+7vdcFVPEjEfx6Ou9e+fJKfAF/nxJfyWS/vhdIbIQmcD2pLur3DonHXNIsPYS5E/T+G9ZHzeRyLkiT0PD/bVywJyjSEYVAdB04t89/1O/w1cDnyilFU=';

    protected $lineClient;

    protected $lineService;

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    public function receive()
    {
        $record = file_get_contents('php://input');
        $this->lineService->putWebhookRecord($record);
        $message = json_decode($record);
        $replyToken = $message->events[0]->replyToken;
        if ($message->events[0]->type !== 'message' || $message->events[0]->message->type !== 'text') {
            return '';
        }
        $client = new BedrockAgentRuntimeClient([
            'region' => 'us-east-1',
            'version' => 'latest',
        ]);
        try {
            $result = $client->retrieveAndGenerate([
                'input' => [
                    'text' => $message->events[0]->message->text,
                ],
                // 'sessionId' => 'be7b6adf-990c-46a7-a7dd-6cbe19c0d118',
                'retrieveAndGenerateConfiguration' => [
                    'type' => 'KNOWLEDGE_BASE',
                    // 'type' => 'EXTERNAL_SOURCES',
                    'knowledgeBaseConfiguration' => [
                        'knowledgeBaseId' => 'SWRVOSBX7U',
                        // Llama 3 2 11B Instruct
                        // 'modelArn' => 'arn:aws:bedrock:us-east-1:699475932583:inference-profile/us.meta.llama3-2-11b-instruct-v1:0',
                        // Llama 3 2 90B Instruct
                        // 'modelArn'=>'arn:aws:bedrock:us-east-1:699475932583:inference-profile/us.meta.llama3-2-90b-instruct-v1:0',
                        // Claude 3 Haiku
                        'modelArn' => 'arn:aws:bedrock:us-east-1::foundation-model/anthropic.claude-3-haiku-20240307-v1:0',
                        // Claude 3.5 Sonnet
                        // 'modelArn' => 'arn:aws:bedrock:us-east-1::foundation-model/anthropic.claude-3-5-sonnet-20240620-v1:0',
                        'retrievalConfiguration' => [
                            'vectorSearchConfiguration' => [
                                'numberOfResults' => 5
                            ]
                        ],
                        'generationConfiguration' => [
                            'maxTokens' => 1024,
                            'temperature' => 1,
                            'topP' => 1
                        ]
                    ],
                ],
            ]);
            // .PHP_EOL.$result['sessionId']
            $this->lineService->reply($result['output']['text'].PHP_EOL.$result['sessionId'], $replyToken);
        } catch (Exception $error) {
            $this->lineService->reply($error->getMessage(), $replyToken);
        }
        return '';
    }
    public function export()
    {
        $dates = $this->lineService->getWebhookRecordDates();
        if (empty($dates)) {
            return response()->json(['error' => 'No records.'], 200);
        }
        $output = $this->lineService->exportWebhookRecordsByDates($dates);
        return response($output, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="export.csv"');
    }

    private function _getLineUsers($usersId)
    {
        $users = collect();
        $requests = [];
        foreach ($usersId as $userId) {
            $requests[] = $this->lineClient->request('GET', 'v2/bot/profile/' . $userId);
        }
        $pool = new Pool($this->lineClient, $requests,  [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use (&$users) {
                echo "Request {$index} was successful\n";
                $users->push(json_decode((string)$response->getBody()));
            },
            'rejected' => function (RequestException $reason, $index) {
                echo "Request {$index} failed: " . $reason->getMessage() . "\n";
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();
        return $users;
    }

    private function _getGroups($groupsId)
    {
        $response = $this->lineClient->request('GET', 'v2/bot/profile/' . 'U0cfb8950dfc3f35e1fc92f611271d59f' . '', [
            // 'headers' => [
            //     'Authorization' => 'Bearer ' . $token->access_token,
            // ],
        ]);
        dd($response->getBody()->getContents());
    }
}
