<?php

namespace Framework\Service\WeChat;

use Framework\Facade\Cache;
use Framework\Facade\Config;

/**
 * 微信token
 */
trait WeChatToken {

    /**
     * 获取AccessToken值
     */
    public function getAccessTokenCache($strGzhKey) {
        $strKey = Config::get("wechat.gzh.{$strGzhKey}.access_token_redis_key");
        $strAccessToken = Cache::exec('get', $strKey);
        if ($strAccessToken !== false) {
            return json_decode($strAccessToken, true);
        } else {
            return ['accesstoken' => '', 'expiretime' => 1];
        }
    }

    /**
     * 设置AccessToken值
     */
    public function setAccessTokenCache($strGzhKey, $strAccessToken) {
        $strKey = Config::get("wechat.gzh.{$strGzhKey}.access_token_redis_key");
        $strAccessToken = json_encode(['accesstoken' => $strAccessToken, 'expiretime' => time() + 7000]);
        Cache::exec('set', ['key' => $strKey, 'value' => $strAccessToken]);
    }

    /**
     * 获取JsapiTicket值
     */
    public function getJsapiTicketCache($strGzhKey) {
        $strKey = Config::get("wechat.gzh.{$strGzhKey}.jsapi_ticket_redis_key");
        $strJsapiTicket = Cache::exec('get', $strKey);
        if ($strJsapiTicket !== false) {
            return json_decode($strJsapiTicket, true);
        } else {
            return ['jsapiticket' => '', 'expiretime' => 1];
        }
    }

    /**
     * 设置JsapiTicket值
     */
    public function setJsapiTicketCache($strGzhKey, $strNewJsapiTicket, $strOldJsapiTicket) {
        //当jsapiticket有更新时，才会更新值
        if ($strNewJsapiTicket != '' && $strNewJsapiTicket != $strOldJsapiTicket) {
            $strKey = Config::get("wechat.gzh.{$strGzhKey}.jsapi_ticket_redis_key");
            $strJsapiTicket = json_encode(['jsapiticket' => $strNewJsapiTicket, 'expiretime' => time() + 7190]);
            Cache::exec('set', ['key' => $strKey, 'value' => $strJsapiTicket]);
        }
    }

}
