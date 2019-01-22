<?php

namespace Framework\Service\Security;

/**
 * 微信小程序解密
 */
class WXBizDataCrypt {

    /**
     * 检验数据的真实性，并且获取解密后的明文
     * <br>此接口调用失败，需要触发wx.login，返回err_code=1001
     * <br>参见E:\vagrant-box\htdocs\Interview2\app\Http\Model\MiniApi\Interview\Common\LoginModel.php->verifyPhone
     * @param $strSessionKey string 用户在小程序登录后获取的会话密钥
     * @param $strAppId string 小程序的appid
     * @param $strEncryptedData string 加密的用户数据
     * @param $strIv string 与用户数据一同返回的初始向量
     * @return mix 成功:解密后的内容 失败:false
     */
    public function decryptData($strSessionKey, $strAppId, $strEncryptedData, $strIv) {
        if (strlen($strSessionKey) != 24) {
            return '41001';
        }
        $strAesKey = base64_decode($strSessionKey);

        if (strlen($strIv) != 24) {
            return '41002';
        }

        $strAesIV = base64_decode($strIv);
        $strAesCipher = base64_decode($strEncryptedData);
        $mixResult = openssl_decrypt($strAesCipher, "AES-128-CBC", $strAesKey, 1, $strAesIV);

        $arrDataObj = json_decode($mixResult, true);
        if (!is_array($arrDataObj)) {
            return '41003';
        }
        if ($arrDataObj['watermark']['appid'] != $strAppId) {
            return '41004';
        }
        return $arrDataObj;
    }

}
