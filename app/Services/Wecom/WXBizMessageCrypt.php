<?php

namespace App\Services\Wecom;

use Exception;
use Illuminate\Support\Facades\Log;

class WXBizMessageCrypt
{
    private string $token;
    private string $encodingAesKey;
    private string $corpId;
    private string $key;
    private string $iv;

    public function __construct(string $token, string $encodingAesKey, string $corpId)
    {
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->corpId = $corpId;

        // 解码 AES key
        $aesKey = base64_decode($encodingAesKey . '=');
        $this->key = $aesKey;
        $this->iv = substr($aesKey, 0, 16);
    }

    public function verifyURL(string $messageSignature, string $timestamp, string $nonce, string $echoStr): string|false
    {
        if (!$this->verifyMessageSignature($messageSignature, $timestamp, $nonce, $echoStr)) {
            Log::error('Signature verification failed');
            return false;
        }

        $decrypted = $this->decrypt($echoStr);
        if ($decrypted === false) {
            Log::error('Decryption failed');
            return false;
        }

        return $decrypted;
    }

    public function verifyMessage($messageSignature, $timestamp, $nonce, $body)
    {
        Log::debug('Received message', ['body' => $body]);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        $encryptedMessage = $xml->Encrypt[0];
        if (!$this->verifyMessageSignature($messageSignature, $timestamp, $nonce, $encryptedMessage)) {
            Log::error('Signature verification failed');
            return false;
        }
        $decrypted = $this->decrypt($encryptedMessage);
        if ($decrypted === false) {
            Log::error('Decryption failed');
            return false;
        }
        Log::debug('Decrypted message', ['message' => $decrypted]);
        $message = simplexml_load_string($decrypted, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $message;
    }


    public function verifyMessageSignature(string $messageSignature, string $timestamp, string $nonce, string $encryptedMessage): bool
    {
        $array = [$this->token, $timestamp, $nonce, $encryptedMessage];
        sort($array, SORT_STRING);
        $str = implode($array);
        $calculatedSignature = sha1($str);
        return $calculatedSignature === $messageSignature;
    }

    public function decrypt(string $encrypted): string|false
    {
        try {
            // base64 解码密文
            $ciphertext = base64_decode($encrypted);
            if ($ciphertext === false) {
                Log::error('Base64 decode failed');
                return false;
            }

            // 使用 AES-256-CBC 模式解密
            $decrypted = openssl_decrypt(
                $ciphertext,
                'AES-256-CBC',
                $this->key,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                $this->iv
            );

            if ($decrypted === false) {
                Log::error('OpenSSL decrypt failed');
                return false;
            }
            // 获取填充值
            $pad = ord(substr($decrypted, -1));
            if ($pad < 1 || $pad > 32) {
                Log::error('Invalid padding value', ['pad' => $pad]);
                return false;
            }
            // 去除补位
            $result = substr($decrypted, 0, (strlen($decrypted) - $pad));
            // 去除16位随机字符串
            if (strlen($result) < 16) {
                Log::error('Result too short after removing padding');
                return false;
            }
            $content = substr($result, 16);
            $lenList = unpack("N", substr($content, 0, 4));
            if (!$lenList) {
                Log::error('Failed to unpack length');
                return false;
            }
            $xmlLen = $lenList[1];
            $xml = substr($content, 4, $xmlLen);
            $fromCorpId = substr($content, $xmlLen + 4);
            if ($fromCorpId !== $this->corpId) {
                return false;
            }
            return $xml;
        } catch (Exception $e) {
            return false;
        }
    }
}
