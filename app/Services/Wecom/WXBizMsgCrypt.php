<?php

namespace App\Services\Wecom;

class WXBizMsgCrypt
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
        // 取前16个字节作为初始向量
        $this->iv = substr($aesKey, 0, 16);
    }

    public function verifyURL(string $msgSignature, string $timestamp, string $nonce, string $echoStr): string|false
    {
        $echoStr = urldecode($echoStr);

        if (!$this->verifyMsgSignature($msgSignature, $timestamp, $nonce, $echoStr)) {
            return false;
        }

        return $this->decrypt($echoStr);
    }

    private function verifyMsgSignature(string $msgSignature, string $timestamp, string $nonce, string $data): bool
    {
        $array = [$this->token, $timestamp, $nonce, $data];
        sort($array, SORT_STRING);
        $str = implode($array);
        $signature = sha1($str);

        return $signature === $msgSignature;
    }

    private function decrypt(string $encrypted): string|false
    {
        try {
            // base64 解码密文
            $ciphertext = base64_decode($encrypted);
            if ($ciphertext === false) {
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
                return false;
            }

            // 去除补位字符
            $pad = ord(substr($decrypted, -1));
            if ($pad < 1 || $pad > 32) {
                return false;
            }
            $result = substr($decrypted, 0, (strlen($decrypted) - $pad));

            // 去除16位随机字符串
            if (strlen($result) < 16) {
                return false;
            }

            $content = substr($result, 16);
            $lenList = unpack("N", substr($content, 0, 4));
            if (!$lenList) {
                return false;
            }

            $msgLen = $lenList[1];
            $msg = substr($content, 4, $msgLen);
            $receiveId = substr($content, $msgLen + 4);

            if ($receiveId !== $this->corpId) {
                return false;
            }

            return $msg;
        } catch (\Exception $e) {
            Log::error('WeChatVerification decrypt error: ' . $e->getMessage());
            return false;
        }
    }
}
