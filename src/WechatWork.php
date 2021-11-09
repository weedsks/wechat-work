<?php


namespace Weeds\WechatWork;


use Illuminate\Config\Repository;

/**
 * 企业微信 API
 */
class WechatWork
{
    protected $config;
    /**
     * access_token 前缀
     *
     * @var string
     */
    private $access_token_key = 'WeedsWechat_access_token';

    /**
     * 接口地址
     *
     * @var string
     */
    public $url = 'https://qyapi.weixin.qq.com/cgi-bin/';

    /**
     * 企业ID
     *
     * @var string
     */
    public $corpid      = NULL;


    /**
     * 初始化参数
     * @param Repository $config 配置文件
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('wechatwork');
        $this->corpid       = $this->config['corp_id'];
    }


    /**
     * CURL 请求 [GET]
     *
     * @param string $url
     * @return void
     */
    public function getCurl(string $url)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->get($url);

        // 状态
        if ($response->getStatusCode() != 200) {
            return [false, '请求失败，状态码：' . $response->getStatusCode()];
        }

        // 结果
        $returnContents = json_decode($response->getBody()->getContents(), true);
        if ($returnContents['errcode']) {
            return [false, 'errcode：' . $returnContents['errcode'] . '丨errmsg：' . $returnContents['errmsg']];
        }
        return [true, $returnContents];
    }


    /**
     * CURL 请求 [POST]
     *
     * @param string $url
     * @param array $options
     * @return void
     */
    public function postCurl(string $url, array $options)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
            'json' => $options,

            // 头信息
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,sm;q=0.7',
                'Accept-Encoding' => 'gzip'
            ],
        ]);

        // 状态
        if ($response->getStatusCode() != 200) {
            return [false, '请求失败，状态码：' . $response->getStatusCode()];
        }

        // 结果
        $returnContents = json_decode($response->getBody()->getContents(), true);
        if ($returnContents['errcode']) {
            return [false, 'errcode：' . $returnContents['errcode'] . '丨errmsg：' . $returnContents['errmsg']];
        }
        return [true, $returnContents];
    }


    /**
     * 获取 access_token
     *
     * @return string
     */
    public function access_token($agents='contacts'): array
    {
        $key = $this->access_token_key."-". $agents . '-' . ':' . $this->corpid . ':' . $this->config['agents'][$agents]['secret'];
        if (!cache($key)) {
            $array = [
                'corpid'        => $this->corpid,
                'corpsecret'    => $this->config['agents'][$agents]['secret'],
            ];
            $url = $this->url . 'gettoken?' . http_build_query($array);
            list($status, $re) = $this->getCurl($url);
            if (!$status) {
                return [false, $re];
            }
            cache([$key=>$re['access_token']], $re['expires_in']);
        }
        return [true, cache($key)];
    }

    /**
     * 获取部门列表
     *
     * @param string $userid
     * @return void
     */
    public function department_list()
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $url = $this->url . 'department/list?' . http_build_query($array);
        return $this->getCurl($url);
    }


    /**
     * 获取部门成员
     *
     * @param string $department_id
     * @return void
     */
    public function user_simplelist(string $department_id, int $fetch_child=1)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
            'department_id' => $department_id,
            'fetch_child'=> $fetch_child
        ];
        $url = $this->url . 'user/simplelist?' . http_build_query($array);
        return $this->getCurl($url);
    }


    /**
     * 获取部门成员详情
     *
     * @param string $department_id 获取的部门id
     * @return void
     */
    public function user_list(string $department_id, int $fetch_child=1)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
            'department_id' => $department_id,
            'fetch_child'=> $fetch_child
        ];
        $url = $this->url . 'user/list?' . http_build_query($array);
        return $this->getCurl($url);
    }

    /**
     * 打卡日报数据
     * @param int $starttime
     * @param int $endtime
     * @param array $userlist
     * @return array|void
     */
    public function getcheckin_monthdata(array $userlist, int $starttime = 0, int $endtime = 0)
    {
        if (empty($starttime) || empty($endtime)){
            $starttime = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
            $endtime = mktime(23,59,59,date("m") ,0,date("Y"));
        }
        list($status, $token) = $token = $this->access_token('OA');
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $json_param = [
            'starttime'=>$starttime,
            'endtime'=>$endtime,
            'useridlist' => $userlist
        ];
        $url = $this->url . 'checkin/getcheckin_monthdata?' . http_build_query($array);
        return $this->postCurl($url, $json_param);
    }

    /**
     * 打卡月报数据
     * @param int $starttime
     * @param int $endtime
     * @param array $userlist
     * @return array|void
     */
    public function getcheckin_daydata(array $userlist, int $starttime, int $endtime )
    {
        list($status, $token) = $token = $this->access_token('OA');
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $json_param = [
            'starttime'=>$starttime,
            'endtime'=>$endtime,
            'useridlist' => $userlist
        ];
        $url = $this->url . 'checkin/getcheckin_daydata?' . http_build_query($array);
        return $this->postCurl($url, $json_param);
    }

    /**
     * 读取成员
     *
     * @param string $userid 成员UserID
     * @return void
     */
    public function user_get(string $userid)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
            'userid'        => $userid,
        ];
        $url = $this->url . 'user/get?' . http_build_query($array);
        return $this->getCurl($url);
    }


    /**
     * 获取客户列表
     *
     * @param string $userid 企业成员的userid
     * @return void
     */
    public function externalcontact_lis(string $userid)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
            'userid'        => $userid,
        ];
        $url = $this->url . 'externalcontact/list?' . http_build_query($array);
        return $this->getCurl($url);
    }


    /**
     * 获取客户详情
     *
     * @param string $external_userid 外部联系人的userid，注意不是企业成员的帐号
     * @return void
     */
    public function externalcontact_get(string $external_userid)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'      => $token,
            'external_userid'   => $external_userid,
        ];
        $url = $this->url . 'externalcontact/get?' . http_build_query($array);
        return $this->getCurl($url);
    }


    /**
     * 获取客户群列表
     *
     * @param integer $status_filter
     * @param integer $owner_filter
     * @param integer $userid_list
     * @param integer $partyid_list
     * @param integer $offset
     * @param integer $limit
     * @return void
     */
    public function externalcontact_groupchat_list(int $status_filter = 0, int $owner_filter = 0, int $userid_list = 100, int $partyid_list = 100, int $offset = 0, int $limit = 1000)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
            'status_filter' => $status_filter,
            'owner_filter'  => $owner_filter,
            'userid_list'   => $userid_list,
            'offset'        => $offset,
            'limit'         => $limit,
        ];
        $url = $this->url . 'externalcontact/groupchat/list?' . http_build_query($array);
        return $this->getCurl($url);
    }


    /**
     * 获取客户群详情
     *
     * @param string $chat_id 客户群ID
     * @return void
     */
    public function externalcontact_groupchat_get(string $chat_id)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $url = $this->url . 'externalcontact/groupchat/get?' . http_build_query($array);
        $json = [
            'chat_id'  => $chat_id,
        ];
        return $this->postCurl($url, $json);
    }


    /**
     * 获取联系客户统计数据
     *
     * @return void
     */
    public function externalcontact_get_user_behavior_data(string $userid = NULL, string $partyid = NULL, string $start_time, string $end_time)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $url = $this->url . 'externalcontact/get_user_behavior_data?' . http_build_query($array);
        $json = [
            'start_time' => $start_time,
            'end_time' => $end_time,
        ];
        if (!empty($userid))        $json['userid']        = $userid;
        if (!empty($partyid))       $json['partyid']       = $partyid;
        if (!empty($start_time))    $json['start_time']    = $start_time;
        if (!empty($end_time))      $json['end_time']      = $end_time;
        return $this->postCurl($url, $json);
    }


    /**
     * 获取客户群统计数据
     *
     * @param string $day_begin_time
     * @param string $owner_filter
     * @param string $userid_list
     * @param string $partyid_list
     * @param integer $order_by
     * @param integer $order_asc
     * @param integer $offset
     * @param integer $limit
     * @return void
     */
    public function externalcontact_groupchat_statistic(string $day_begin_time, string $owner_filter = NULL, string $userid_list = NULL, string $partyid_list = NULL, int $order_by = 1, int $order_asc = 0, int $offset = 0, int $limit = 1000)
    {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $url = $this->url . 'externalcontact/groupchat/statistic?' . http_build_query($array);

        if (!empty($day_begin_time))    $json['day_begin_time']     = $day_begin_time;
        if (!empty($owner_filter))      $json['owner_filter']       = $owner_filter;
        if (!empty($userid_list))       $json['userid_list']        = $userid_list;
        if (!empty($partyid_list))      $json['$partyid_list']      = $partyid_list;
        if (!empty($order_by))          $json['order_by']           = $order_by;
        if (!empty($order_asc))         $json['order_asc']          = $order_asc;
        if (!empty($offset))            $json['offset']             = $offset;
        if (!empty($limit))             $json['limit']              = $limit;

        return $this->postCurl($url, $json);
    }



    /**
     * 添加企业群发消息任务
     *
     * @param string $chat_type
     * @param string $external_userid
     * @param string $sender
     * @param string $text_content
     * @param string $image_media_id
     * @param string $image_pic_url
     * @param string $link_title
     * @param string $link_picurl
     * @param string $link_desc
     * @param string $link_url
     * @param string $miniprogram_title
     * @param string $miniprogram_pic_media_id
     * @param string $miniprogram_appid
     * @param string $miniprogram_page
     * @return void
     */
    public function externalcontact_add_msg_template(
        string $chat_type = NULL,
        string $external_userid = NULL,
        string $sender = NULL,
        string $text_content = NULL,
        string $image_media_id = NULL,
        string $image_pic_url = NULL,
        string $link_title = NULL,
        string $link_picurl = NULL,
        string $link_desc = NULL,
        string $link_url = NULL,
        string $miniprogram_title = NULL,
        string $miniprogram_pic_media_id = NULL,
        string $miniprogram_appid = NULL,
        string $miniprogram_page = NULL
    ) {
        list($status, $token) = $token = $this->access_token();
        if (!$status){
            return [false, $token];
        }
        $array = [
            'access_token'  => $token,
        ];
        $url = $this->url . 'externalcontact/add_msg_template?' . http_build_query($array);


        if (!empty($chat_type))                 $json['chat_type']                  = $chat_type;
        if (!empty($external_userid))           $json['external_userid']            = $external_userid;
        if (!empty($sender))                    $json['sender']                     = $sender;
        if (!empty($text_content))              $json['text.content']               = $text_content;
        if (!empty($image_media_id))            $json['image.media_id']             = $image_media_id;
        if (!empty($image_pic_url))             $json['image.pic_url']              = $image_pic_url;
        if (!empty($link_title))                $json['link.title']                 = $link_title;
        if (!empty($link_picurl))               $json['link.picurl']                = $link_picurl;
        if (!empty($link_desc))                 $json['link.desc']                  = $link_desc;
        if (!empty($link_url))                  $json['link.url']                   = $link_url;
        if (!empty($miniprogram_title))         $json['miniprogram.title']          = $miniprogram_title;
        if (!empty($miniprogram_pic_media_id))  $json['miniprogram.pic_media_id']   = $miniprogram_pic_media_id;
        if (!empty($miniprogram_appid))         $json['miniprogram.appid']          = $miniprogram_appid;
        if (!empty($miniprogram_page))          $json['miniprogram.page']           = $miniprogram_page;

        dd($json);
        return $this->postCurl($url, $json);
    }



    public function media_get()
    {
    }
}
