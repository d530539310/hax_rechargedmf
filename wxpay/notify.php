<?php
/**
 * 异步回调通知
 * 说明：需要在支付文件中（如native.php或者jsapi.php）的填写回调地址。例如：http://www.xxx.com/wx/notify.php
 * 付款成功后，微信服务器会将付款结果通知到该页面
 */
define('IN_API', true);
define('CURSCRIPT', 'api');
define('DISABLEXSSCHECK', true);

require_once '../../../../source/class/class_core.php';
$discuz = C::app();
$discuz->init();

loadcache('plugin');
$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
$hax_paysetup = $_G['cache']['hax_rechargedmf_paysetup'] ? $_G['cache']['hax_rechargedmf_paysetup'] : C::t('#hax_rechargedmf#hax_rechargedmf_paysetup')->fetch_all();
// header('Content-type:text/html; Charset=utf-8');
$mchid = $hax_paysetup[2]['paymentkey02'];          //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
$appid = $hax_paysetup[2]['paymentkey01'];  //公众号APPID 通过微信支付商户资料审核后邮件发送
$apiKey = $hax_paysetup[2]['paymentkey03'];   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
$wxPay = new WxpayService($mchid,$appid,$apiKey);
$result = $wxPay->notify();
if($result){
    //完成你的逻辑
    //例如连接数据库，获取付款金额$result['cash_fee']，获取订单号$result['out_trade_no']，修改数据库中的订单状态等;
	//现金支付金额：$result['cash_fee']
	//订单金额：$result['total_fee']
	//商户订单号：$result['out_trade_no']
	//付款银行：$result['bank_type']
	//货币种类：$result['fee_type']
	//是否关注公众账号：$result['is_subscribe']
	//用户标识：$result['openid']
	//业务结果：$result['result_code']  SUCCESS/FAIL
	//支付完成时间：$result['time_end']  格式为yyyyMMddHHmmss
	//具体详细请看微信文档：https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_7&index=8
	if($result['result_code']=='SUCCESS'){
	    $orderdata= C::t('#hax_rechargedmf#hax_rechargedmf_orderlog')->fetch($result['out_trade_no']);
        if($orderdata['state']==0){
            $orderarr=array(
    			'state'=>'1',
    			'zftime'=>$_G['timestamp'],
    			'sn'=>$result['transaction_id'],
    			'payid'=>$result['openid'],
    		);
    		C::t('#hax_rechargedmf#hax_rechargedmf_orderlog')->update($orderdata, $orderarr);
    		
    		updatemembercount($orderdata['uid'], array('extcredits'.$orderdata['credittype']=>$orderdata['credit']), true, '', 0, '',lang('plugin/hax_rechargedmf', 'slang21'),lang('plugin/hax_rechargedmf', 'slang22'));
    		if($orderdata['credit2']!="0"){
    		    $orderdata['credit2'] = explode("|",$orderdata['credit2']);
    		    updatemembercount($orderdata['uid'], array('extcredits'.$orderdata['credit2'][0]=>$orderdata['credit2'][1]), true, '', 0, '',lang('plugin/hax_rechargedmf', 'slang23'),lang('plugin/hax_rechargedmf', 'slang24'));
    		}
        }
	}
}else{
    echo 'pay error';
}
class WxpayService
{
    protected $mchid;
    protected $appid;
    protected $apiKey;
    public function __construct($mchid, $appid, $key)
    {
        $this->mchid = $mchid;
        $this->appid = $appid;
        $this->apiKey = $key;
    }

    public function notify()
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        $postStr = file_get_contents('php://input');
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);        
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }
        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }
        $arr = (array)$postObj;
        unset($arr['sign']);
        if (self::getSign($arr, $config['key']) == $postObj->sign) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return $arr;
        }
    }

    /**
     * 获取签名
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}