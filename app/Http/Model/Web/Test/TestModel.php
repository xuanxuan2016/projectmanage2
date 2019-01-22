<?php

namespace App\Http\Model\Web\Test;

use Framework\Facade\App;
use Framework\Facade\Log;
use Framework\Facade\Des;
use Framework\Facade\Cache;
use Framework\Facade\Config;
use Framework\Facade\Request;
use Framework\Service\Database\DB;
use Framework\Service\Database\HashCacheDB;

class TestModel {

    /**
     * 数据库实例
     */
    protected $objDB;

    /**
     * 数据库实例
     */
    protected $objHashCacheDB;

    /**
     * 构造函数
     */
    public function __construct(DB $objDB, HashCacheDB $objHashCacheDB) {
        $this->objDB = $objDB;
        $this->objHashCacheDB = $objHashCacheDB;
    }

    public function testone() {
//        var_dump(Des::encrypt('xiaopangzi'));
        var_dump(Des::passwordHash('xiaopangzi'));
//        var_dump(json_decode(Request::getCookie('interview|LoginInfo'),true));
//        var_dump(Request::delCookie('a'));
        //var_dump(Request::setCookie('a', 'ffff', time() + 1000));
        //var_dump(Request::setCookie('b', 'ffff', time() + 1000, '/', '51job.com'));
        //var_dump(Request::setCookie('a', 'eeee', time() + 1000, '/', ''));
//        setcookie('a', '11', time() + 100, '/', '51job.com');
        //var_dump(Request::getCookie('b'));
//        var_dump(Des::decrypt('fff'));
//        phpinfo();
//        var_dump(Des::decrypt('GzVv5RL99acJ685NWVNw6Q0uhwR29XC7OnMPx/kto7oq2ETznTmLGXx/He0x8LkeMFLV1DIuCrUS4DmgNynC8g=='));
//        var_dump(Cache::exec('exists','aa'));
//        var_dump(Cache::exec('del','aaa'));
//        var_dump(Cache::exec('expire',['key'=>'aa','expire'=>0]));
//        var_dump(Cache::exec('get', ['aa', 'bb']));
//        var_dump(Cache::exec('get', "interview_cachelogintoken::interview::1582822"));
//        var_dump(Cache::exec('hMget',['key'=>['aa','bb','cc'],'field'=>['a','c','b']]));
//        var_dump(Cache::exec('set', ['key' => 'aa', 'value' => '11', 'expire' => 0]));
//        var_dump(Cache::exec('hMSet', [['key' => 'aa', 'value' => ['a' => 1, 'b' => 2, 'c' => 3], 'expire' => 100], ['key' => 'bb', 'value' => ['a' => 1, 'b' => 2, 'c' => 3], 'expire' => 200]]));
//        var_dump(Cache::exec('lPushx', ['key' => 'aa', 'value' => [1, 2]]));
//        var_dump(Cache::exec('lRange', ['key' => 'aaa', 'start' => 0, 'end' => -1]));
//        var_dump(Cache::exec('lTrim', ['key' => 'aa', 'start' => 0, 'end' => 2]));
//        var_dump(Cache::expire('aa',20));
//        var_dump(App::make('path.storage'));
//        $this->objDB->setMainTable('interview_feedback');
//        var_dump($this->objDB->select('select * from interview_feedback where 1=1 limit 1'));
//        var_dump($this->objDB->setMainTable('interview_invitejobseek')->select('select * from [interview_invitejobseek] where invite_id=:invite_id and jobseek_id=:jobseek_id', [':invite_id' => 3000000031, ':jobseek_id' => 51906193]));
//        var_dump($this->objHashCacheDB->select('interview_accountjobseekwx_wx', 'account_id,wx_id,wx_appid', [['account_id'=>51897792]]));
//        var_dump($this->objHashCacheDB->insert('interview_cachesendmsg', [['id'=>1,'role'=>'jobseek'],['id'=>2,'role'=>'interview']]));
//        var_dump(Cache::get(['test1','test11']));
//        var_dump(Cache::get('test1'));
//        var_dump(Cache::hMGet('test11',['b']))
//        var_dump(Cache::hMGet(['test11'],['b']));
//        var_dump(Cache::hMGet(['test11','test11'],['b','a']));
//        var_dump(Cache::hMSet([['key' => 'test', 'member' => ['a' => 1, 'b' => 2], 'expire' => 100]]));
//        $arrMsg = array(
//            'company_name' => '测试公司',
//            'position' => '测试职位',
//            'interview_time' => '2018-08-08 10:00',
//            'wechat_remark' => "尽快查看面试信息吧！",
//            'url' => 'http://www.baidu.com'
//        );
//        $this->objMsgCreate->create(Config::get('const.MsgCreate.MSG_ROLE_JOBSEEK'), Config::get('const.MsgCreate.MSG_SEND_TYPE_WECHAT'), Config::get('const.MsgCreate.MSG_TYPE_MSYQ'), 51897792, $arrMsg);
//        $arrMsg = [
//            'mail_subject' => '友情提醒：请你准时前来参加明日(' . 'interview_time_date' . ')的面试哦！',
//            'interview_time_date' => 'interview_time_date',
//            'jobseek_name' => 'jobseek_name',
//            'img_src' => 'jobseek/common/yingpinzhe.png',
//            'repeat_item_1' => [
//                [
//                    //面试信息
//                    'position' => 'position',
//                    'position_url' => 'position_id' . '.html',
//                    'interview_time' => substr('interview_time', 0, 16),
//                    'interview_address' => 'interview_address',
//                    'interview_lxr' => 'interview_lxr',
//                    'interview_lxfs' => 'interview_lxfs',
//                    'line' => '<hr style="color:#dfdfdf"/>',
//                ]
//            ]
//        ];
//        $this->objMsgCreate->create(Config::get('const.MsgCreate.MSG_ROLE_JOBSEEK'), Config::get('const.MsgCreate.MSG_SEND_TYPE_EMAIL'), Config::get('const.MsgCreate.MSG_TYPE_MSAP'), 51906571, $arrMsg);
//        $arrMsg = [
//            'mail_subject' => '你有一条新消息',
//            'hr_url' => 'http://www.baidu.com',
//        ];
//        //$objBLLCreateMsg->createMsg(BLLCreateMsg::MSG_ROLE_HR, BLLCreateMsg::MSG_SEND_TYPE_EMAIL, BLLCreateMsg::MSG_TYPE_MSPJ, $arrData['ctm_id'] . '-' . $arrData['hr_id'], $arrMailMsg);
//        $this->objMsgCreate->create(Config::get('const.MsgCreate.MSG_ROLE_HR'), Config::get('const.MsgCreate.MSG_SEND_TYPE_EMAIL'), Config::get('const.MsgCreate.MSG_TYPE_MSPJ'), '1-2', $arrMsg);
    }

}
