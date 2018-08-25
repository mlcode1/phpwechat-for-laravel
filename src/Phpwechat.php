<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2018/8/14
 * Time: 15:19
 */

namespace Marin\Phpwechat;
use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository;

class Phpwechat
{
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @var Repository
     */
    protected $config;
    /**
     * Packagetest constructor.
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }
    /**
     * @function 获取用户的基本信息
     * @param string $msg
     * @return string
     */
    public function getUserInfo($code){
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".config('services.weixinweb.client_id')."&secret=".config('services.weixinweb.client_secret')."&code=".$code."&grant_type=authorization_code";
        $jsonRes = file_get_contents($url);
        $res = json_decode($jsonRes);
        if(isset($res->errcode)){
            return ['status' => false,'code' => $res->errcode,'msg' => $res->errmsg];
        }else {
            $access_token = $res->access_token;
            $open_id = $res->openid;
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $open_id . "&lang=zh_CN";
            $jsonRes = file_get_contents($url);
            $res = json_decode($jsonRes);
            if(isset($res->errcode)){
                return ['status' => false,'code' => $res->errcode,'msg' => $res->errmsg];
            }else {
                return ['status' => true, 'code' => 0, 'msg' => $res];
            }
        }
    }

    /**
     * @function 微信公众号推送相关信息
     * @param $msg 消息内容
     * @return mixed
     */
    public function sendMessage($open_id,$msg){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('services.weixinweb.client_gz_id')."&secret=".config('services.weixinweb.client_gz_secret');
        $jsonRes = file_get_contents($url);
        $res = json_decode($jsonRes);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$res->access_token;
        $headers[] = 'Content-Type:application/json';
        $post_data = json_encode([
            'touser' => $open_id,
            'msgtype' => 'text',
            'text' => ['content' => $msg]
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $file_contents = curl_exec($ch);//运行curl
        curl_close ( $ch );
        return $file_contents;
    }

    /**
     * @function 发送模板语言
     * @param $msg 消息内容
     * @return mixed
     */
    public function sendTemplateMessage($post_data){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('services.weixinweb.client_gz_id')."&secret=".config('services.weixinweb.client_gz_secret');
        $jsonRes = file_get_contents($url);
        $res = json_decode($jsonRes);
        if(isset($res->errcode)){
            return ['status' => false,'code' => $res->errcode,'msg' => $res->errmsg];
        }else {
            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $res->access_token;
            $headers[] = 'Content-Type:application/json';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
            curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);//要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $file_contents = curl_exec($ch);//运行curl
            curl_close($ch);
            return ['status' => true,'code' => 0,'msg' => json_decode($file_contents)];
        }
    }

    public function getAllUserUnionIds(){
        $open_ids = $this->getAllUserOpenIds();
        $access_token = $this->getAccessToken();
        foreach ($open_ids as $open_id) {
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$open_id;
            $jsonRes = file_get_contents($url);
            $res = json_decode($jsonRes);
            $data = [
                'open_id' => $open_id,
                'union_id' => $res->unionid
            ];
//            DB::table('openUnion')->insert($data);
        }
        return true;
    }

    /**
     * @function 得到关注公众号的关注着的openID
     * @return mixed
     */
    public function getAllUserOpenIds(){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&next_openid=";
        $jsonRes = file_get_contents($url);
        $res = json_decode($jsonRes);
        $rs = $res->data;
        $open_ids = $rs->openid;
        return $open_ids;
    }

    /**
     * @function 得到公共号返回的access_token
     * @return mixed
     */
    public function getAccessToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('services.weixinweb.client_gz_id')."&secret=".config('services.weixinweb.client_gz_secret');
        $jsonRes = file_get_contents($url);
        $res = json_decode($jsonRes);
        $access_token = $res->access_token;
        return $access_token;
    }
}