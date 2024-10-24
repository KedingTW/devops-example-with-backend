<?php

namespace App\Http\Controllers\Wecom;

use App\Http\Controllers\Controller;
use Aws\BedrockAgentRuntime\BedrockAgentRuntimeClient;

use App\Services\Wecom\WecomService;
use App\Services\Wecom\WXBizMsgCrypt;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class WecomWebhookController extends Controller
{
    protected $wecomService;

    public function __construct(WecomService $wecomService)
    {
        $this->wecomService = $wecomService;
    }

    public function receive(Request $request): Response
    {
        // $client = new BedrockAgentRuntimeClient([
        //     'region' => 'us-east-1',
        //     'credentials' => [
        //         'key' => env('AWS_ACCESS_KEY'),
        //         'secret' => env('AWS_SECRET_KEY'),
        //     ],
        //     'version' => 'latest',
        // ]);
        try {
            // 从配置文件获取参数
            $token = env('WECOM_TOKEN');
            $encodingAesKey = env('WECOM_ENCODING_AES_KEY');
            $corpId = env('WECOM_CORP_ID');

            // 获取请求参数
            $msgSignature = $request->input('msg_signature');
            $timestamp = $request->input('timestamp');
            $nonce = $request->input('nonce');
            $encryptedMsg = $request->getContent();
            // 验证必要参数
            if (!$msgSignature || !$timestamp || !$nonce || !$encryptedMsg) {
                return response('Invalid parameters', 400);
            }
            Log::debug(($encryptedMsg));
            $wxcrypt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId,);
            $decryptedMessage = $wxcrypt->verifyMsgSignature($msgSignature, $timestamp, $nonce, $encryptedMsg);
            Log::debug($decryptedMessage);
            if (!$decryptedMessage) {
                return new Response('Decryption failed', 500);
            }
            $xml = simplexml_load_string($decryptedMessage, 'SimpleXMLElement', LIBXML_NOCDATA);
            $msgType = (string) $xml->MsgType;
            // 回應不同消息類型
            if ($msgType === 'text') {
                $content = (string) $xml->Content;
                $reply = '你发送了文字消息：' . $content;
            } else {
                $reply = '暂不支持处理此消息类型。';
            }
            Log::debug($reply);
            //     $result = $client->retrieveAndGenerate([
            //         'input' => [
            //             'text' => $message->events[0]->message->text,
            //         ],
            //         // 'sessionId' => 'be7b6adf-990c-46a7-a7dd-6cbe19c0d118',
            //         'retrieveAndGenerateConfiguration' => [
            //             'type' => 'KNOWLEDGE_BASE',
            //             // 'type' => 'EXTERNAL_SOURCES',
            //             'knowledgeBaseConfiguration' => [
            //                 'knowledgeBaseId' => 'SWRVOSBX7U',
            //                 // Llama 3 2 11B Instruct
            //                 // 'modelArn' => 'arn:aws:bedrock:us-east-1:699475932583:inference-profile/us.meta.llama3-2-11b-instruct-v1:0',
            //                 // Llama 3 2 90B Instruct
            //                 // 'modelArn'=>'arn:aws:bedrock:us-east-1:699475932583:inference-profile/us.meta.llama3-2-90b-instruct-v1:0',
            //                 // Claude 3 Haiku
            //                 'modelArn' => 'arn:aws:bedrock:us-east-1::foundation-model/anthropic.claude-3-haiku-20240307-v1:0',
            //                 // Claude 3.5 Sonnet
            //                 // 'modelArn' => 'arn:aws:bedrock:us-east-1::foundation-model/anthropic.claude-3-5-sonnet-20240620-v1:0',
            //                 'retrievalConfiguration' => [
            //                     'vectorSearchConfiguration' => [
            //                         'numberOfResults' => 5
            //                     ]
            //                 ],
            //                 'generationConfiguration' => [
            //                     'maxTokens' => 1024,
            //                     'temperature' => 1,
            //                     'topP' => 1
            //                 ]
            //             ],
            //         ],
            //     ]);
            //     // .PHP_EOL.$result['sessionId']
            //     $this->wecomService->reply($result['output']['text'] . PHP_EOL . $result['sessionId'], $replyToken);
        } catch (Exception $error) {
            Log::error('WecomWebhookController error: ' . $error->getMessage());
            return response('Server error', 500);
        }
        return response([]);
    }

    public function verify(Request $request): Response
    {
        try {
            // 从配置文件获取参数
            $token = env('WECOM_TOKEN');
            $encodingAesKey = env('WECOM_ENCODING_AES_KEY');
            $corpId = env('WECOM_CORP_ID');

            // 获取请求参数
            $msgSignature = $request->input('msg_signature');
            $timestamp = $request->input('timestamp');
            $nonce = $request->input('nonce');
            $echostr = $request->input('echostr');
            // 验证必要参数
            if (!$msgSignature || !$timestamp || !$nonce || !$echostr) {
                return response('Invalid parameters', 400);
            }

            // 初始化加解密类
            $wxcrypt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);

            // 验证URL有效性
            $msg = $wxcrypt->verifyURL($msgSignature, $timestamp, $nonce, $echostr);

            if ($msg === false) {
                Log::error('WeChatVerification failed');
                return response('Verification failed', 401);
            }

            // 返回解密后的明文（不带引号、BOM头和换行符）
            return response(trim($msg))
                ->header('Content-Type', 'text/plain; charset=utf-8');
        } catch (Exception $e) {
            Log::error('WeChatVerification error: ' . $e->getMessage());
            return response('Server error', 500);
        }
    }
}
