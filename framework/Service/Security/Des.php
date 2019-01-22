<?php

namespace Framework\Service\Security;

use Framework\Facade\Config;

class Des {

    /**
     * 加密方法
     */
    private $strAesCipher = 'AES-128-CBC';

    /**
     * 计算字符串的md5散列值
     * @param string $strValue 需要计算的字符串
     * @param string $strSalt 盐值
     */
    public function md5($strValue, $strSalt = '') {
        return md5(md5($strValue) . $strSalt);
    }

    /**
     * 对密码进行加密
     * @param string $strPassword 密码
     */
    public function passwordHash($strPassword) {
        return $strPassword === '' ? $strPassword : password_hash($strPassword, PASSWORD_BCRYPT);
    }

    /**
     * 对密码进行验证
     * @param string $strPassword 明码
     * @param string $strPasswordHash 加密的密码
     */
    public function passwordVerify($strPassword, $strPasswordHash) {
        return password_verify($strPassword, $strPasswordHash);
    }

    /**
     * 对字符串进行加密
     * @param string $strValue 需要加密的字符串
     */
    public function encrypt($strValue) {
        $intIvLen = openssl_cipher_iv_length($this->strAesCipher);
        //生成向量
        $strIv = openssl_random_pseudo_bytes($intIvLen);
        $strCiphertext = openssl_encrypt($strValue, $this->strAesCipher, Config::get('des.aes_key'), OPENSSL_RAW_DATA, $strIv);
        $strHMac = hash_hmac('sha256', $strCiphertext, Config::get('des.aes_key'), true);
        return base64_encode($strIv . $strHMac . $strCiphertext);
    }

    /**
     * 对字符串进行解密，失败返回原值
     * @param string $strValue 需要解密的字符串
     */
    public function decrypt($strValue) {
        $strOldValue = $strValue;
        $strValue = base64_decode($strValue);
        $intIvLen = openssl_cipher_iv_length($this->strAesCipher);
        //获取向量
        $strIv = substr($strValue, 0, $intIvLen);
        //获取密钥hash值
        $intShaLen = 32;
        $strHMac = substr($strValue, $intIvLen, $intShaLen);
        $strCiphertext = substr($strValue, $intIvLen + $intShaLen);
        //格式验证
        if (strlen($strIv) != 16 || strlen($strHMac) != 32 || strlen($strCiphertext) <= 0) {
            return $strOldValue;
        }
        $strOriginaltext = openssl_decrypt($strCiphertext, $this->strAesCipher, Config::get('des.aes_key'), OPENSSL_RAW_DATA, $strIv);
        $strCalcMac = hash_hmac('sha256', $strCiphertext, Config::get('des.aes_key'), true);
        return hash_equals($strHMac, $strCalcMac) ? $strOriginaltext : $strOldValue;
    }

}
