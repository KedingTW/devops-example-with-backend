<?php

namespace App\Http\Controllers\Line;

use App\Http\Controllers\Controller;
use Aws\BedrockAgentRuntime\BedrockAgentRuntimeClient;

use App\Services\Line\LineService;
use Exception;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    protected $lineService;

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    public function receive()
    {
        $record = file_get_contents('php://input');
        $message = json_decode($record);
        if (count($message->events) < 1) {
            return '';
        }
        if ($message->events[0]->type !== 'message' || $message->events[0]->message->type !== 'text') {
            return '';
        }
        $replyToken = $message->events[0]->replyToken;
        try {
            $client = new BedrockAgentRuntimeClient([
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY'),
                    'secret' => env('AWS_SECRET_KEY'),
                ],
                'version' => 'latest',
            ]);
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
            $result = ['output' => ['text' => '喵喵'], 'sessionId' => 'abc'];
            $this->lineService->reply($result['output']['text'] . PHP_EOL . $result['sessionId'], $replyToken);
        } catch (Exception $error) {
            $this->lineService->reply($error->getMessage(), $replyToken);
        }
        return '';
    }
}
