<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$REFERERurl = parse_url($_SERVER['HTTP_REFERER']);
if($_SERVER['HTTP_HOST']!=$REFERERurl['host']){
    exit('Access Denied');
}
header("Content-type:text/html;charset=utf-8");
global $_G;
if(!$_G['uid']){
	exit('Access Denied');
}
if(empty($_POST['hash']) || $_POST['hash'] != formhash()){
    exit('Formhash Denied');
}
$mod = $_POST['mod'];
$money = number_format($_POST['money'],2,'.','');
$credittype =  intval($_POST['credittype']);
$crd = intval($_POST['crd']);
$crd2 = $_POST['crd2'];
$key =  intval($_POST['key']);
$paytype = $_POST['paytype'];
$cardid = $_POST['cardid'];
$grpid =  intval($_POST['grpid']);
$grptime = $_POST['grptime'];

loadcache('hax_rechargedmf_credit');
require_once("alipay/vip_function.func.php");
$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
$hax_paysetup = $_G['cache']['hax_rechargedmf_paysetup'] ? $_G['cache']['hax_rechargedmf_paysetup'] : C::t('#hax_rechargedmf#hax_rechargedmf_paysetup')->fetch_all();

if($mod=="jf"){
    if($paytype=="zfb"||$paytype=="wxzf"){
        if($hax_rechargedmf['acmax']!=0){
            $acmax = acountmax();
            if($acmax+$money>$hax_rechargedmf['acmax']){
                $errlist = array();
            	$errlist["err"] = "err004";
            	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
            	exit();
            }
        }
        if($hax_rechargedmf['singlemin']>$money){
            $errlist = array();
        	$errlist["err"] = "err001";
        	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
        	exit();
        }
        $zdycrdnum = intval($_POST['zdycrdnum']);
        $crdcheck = checkcrdorder($credittype,$money,$zdycrdnum);
        if(!$crdcheck){
            $errlist = array();
        	$errlist["err"] = "err002";
        	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
        	exit();
        }
        if($paytype=="zfb"){$uppaytype = 1;}elseif($paytype=="wxzf"){$uppaytype = 2;}
        $nowdate=dgmdate($_G['timestamp'], 'YmdHis');
        $orderid=$mod.$nowdate.uniqid();
        $orderarr=array(
        	'orderid'=>$orderid,
        	'uid'=>$_G['uid'],
        	'usname'=>$_G['username'],
        	'money'=>$crdcheck['money'],
        	'type'=>$uppaytype,
        	'time'=>$_G['timestamp'],
        	'credit'=>$crdcheck['crdnum'],
        	'credit2'=>$crdcheck['send'],
        	'credittype'=>$credittype,
        );
        C::t('#hax_rechargedmf#hax_rechargedmf_orderlog')->insert($orderarr, true);
        // require_once("alipay/phpqrcode.class.php");
        if($paytype=="zfb"){
            require_once("alipay/alipay_submit.class.php");
            /*** 请填写以下配置信息 ***/
            $appid = $hax_paysetup[1]['paymentkey01'];  //https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
            $notifyUrl = $_G['siteurl']."source/plugin/hax_rechargedmf/alipay/notify.php";     //付款成功后的异步回调地址
            $outTradeNo = $orderarr['orderid'];     //你自己的商品订单号，不能重复
            $payAmount = $orderarr['money'];          //付款金额，单位:元
            $orderName = str_to_utf8(lang('plugin/hax_rechargedmf', 'slang13').'|'.$_G['setting']['sitename'].'|'.$_G['username']);    //订单标题
            $signType = 'RSA2';			//签名算法类型，支持RSA2和RSA，推荐使用RSA2
            $rsaPrivateKey = $hax_paysetup[1]['paymentkey02'];		//商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
            /*** 配置结束 ***/
            $aliPay = new AlipayService();
            $aliPay->setAppid($appid);
            $aliPay->setNotifyUrl($notifyUrl);
            $aliPay->setRsaPrivateKey($rsaPrivateKey);
            $aliPay->setTotalFee($payAmount);
            $aliPay->setOutTradeNo($outTradeNo);
            $aliPay->setOrderName($orderName);
            
            $result = $aliPay->doPay();
            $result = $result['alipay_trade_precreate_response'];
            if($result['code'] && $result['code']=='10000'){
                $url= $_G['siteurl'].'plugin.php?id=hax_rechargedmf:qr&size=5&aliurl='.$result['qr_code'];
                $zfbrlist = array(
                    'outTradeNo'=>$outTradeNo,
                    'img'=>'<img class="phpqrcode" src="'.$url.'">',
                    'money'=>$payAmount,
                    'aliurl'=>$result['qr_code'],
                    // 'ceshi'=>$notifyUrl,
                );
                
                echo json_encode($zfbrlist, JSON_UNESCAPED_UNICODE);
                exit();
            }else{
                echo $result['msg'].' : '.$result['sub_msg'];
                exit();
            }
        }elseif($paytype=="wxzf"){
            require_once("wxpay/wxpay_submit.class.php");
            $mchid = $hax_paysetup[2]['paymentkey02'];          //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
            $appid = $hax_paysetup[2]['paymentkey01'];  //公众号APPID 通过微信支付商户资料审核后邮件发送
            $apiKey = $hax_paysetup[2]['paymentkey03'];   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
            $wxPay = new WxpayService($mchid,$appid,$apiKey);
            $outTradeNo = $orderarr['orderid'];     //你自己的商品订单号
            $payAmount = $orderarr['money'];          //付款金额，单位:元
            $orderName = str_to_utf8(lang('plugin/hax_rechargedmf', 'slang13').'|'.$_G['setting']['sitename'].'|'.$_G['username']);    //订单标题
            $notifyUrl = $_G['siteurl']."source/plugin/hax_rechargedmf/wxpay/notify.php";     //付款成功后的回调地址(不要有问号)
            $payTime = time();      //付款时间
            $arr = $wxPay->createJsBizPackage($payAmount,$outTradeNo,$orderName,$notifyUrl,$payTime);
            //生成二维码
            $url = $_G['siteurl'].'plugin.php?id=hax_rechargedmf:qr&aliurl='.$arr['code_url'];
            $wxrlist = array(
                'outTradeNo'=>$outTradeNo,
                'img'=>'<img class="phpqrcode" src="'.$url.'">',
                'money'=>$payAmount,
                'aliurl'=>$arr['code_url'],
                // 'ceshi'=>$notifyUrl,
            );
            
            echo json_encode($wxrlist, JSON_UNESCAPED_UNICODE);
            exit();
            // echo "<img src='{$url}' style='width:300px;'><br>";
            // echo '二维码内容：'.$arr['code_url'].'</br>';
            // echo '订单号：'.$outTradeNo;
        }
    }elseif($paytype=="km"){
        if(!$cardid) {
            // echo json_encode("km_weishuru", JSON_UNESCAPED_UNICODE);
			echo "km_weishuru";
			exit();
		}
		if(!($card = C::t('common_card')->fetch($_POST['cardid']))) {
		  //  echo json_encode("km_bucunzai", JSON_UNESCAPED_UNICODE);
		    echo "km_bucunzai";
		    exit();
		} else {
		    if($card['status'] == 2) {
		      //  echo json_encode("km_yishiyong", JSON_UNESCAPED_UNICODE);
				echo "km_yishiyong";
				exit();
			}
			if($card['cleardateline'] < TIMESTAMP) {
			 //   echo json_encode("km_yiguoqi", JSON_UNESCAPED_UNICODE);
				echo "km_yiguoqi";
				exit();
			}
			C::t('common_card')->update($card['id'], array('status' => 2, 'uid' => $_G['uid'], 'useddateline' => $_G['timestamp']));
			updatemembercount($_G[uid], array($card['extcreditskey'] => $card['extcreditsval']), true, 'CDC', 1);
			
            $orderarr=array(
            	'orderid'=>$card['id'],
            	'uid'=>$_G['uid'],
            	'usname'=>$_G['username'],
            	'money'=>$card['price'],
            	'type'=>3,
            	'time'=>$_G['timestamp'],
            	'credit'=>$card['extcreditsval'],
            	'credit2'=>0,
            	'credittype'=>$card['extcreditskey'],
            	'state'=>1,
            );
            C::t('#hax_rechargedmf#hax_rechargedmf_orderlog')->insert($orderarr, true);
            
			echo $_G['setting']['extcredits'][$card['extcreditskey']]['title']."+".$card['extcreditsval'];
			exit();
		}
    }
}elseif ($mod == "hy") {
    
    $hycheck = checkgrp($grpid,$grptime,$money);
    $grpname = $hycheck['name'];
    $credit = $hycheck['crd'] ? $hycheck['crd'][1]."|".$hycheck['crd'][2] : '';
    
    
    if(!$hycheck['check']){
        $errlist = array();
    	$errlist["err"] = "err002";
    	$errlist["msg"] = $grpid;
    	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
    	exit();
    }
    
    if($paytype=="zfb"||$paytype=="wxzf"){
        $acmax = acountmax();
        if($acmax+$money>$hax_rechargedmf['acmax']){
            $errlist = array();
        	$errlist["err"] = "err004";
        	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
        	exit();
        }
        if($hax_rechargedmf['singlemin']>$money){
            $errlist = array();
        	$errlist["err"] = "err001";
        	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
        	exit();
        }
        if($paytype=="zfb"){$uppaytype = 1;}elseif($paytype=="wxzf"){$uppaytype = 2;}
        $nowdate=dgmdate($_G['timestamp'], 'YmdHis');
        $orderid=$mod.$nowdate.uniqid();
        $orderarr=array(
        	'orderid'=>$orderid,
        	'uid'=>$_G['uid'],
        	'usname'=>$_G['username'],
        	'money'=>$money,
        	'type'=>$uppaytype,
        	'time'=>$_G['timestamp'],
        	'groupid'=>$grpid,
        	'groupname'=>$grpname,
        	'grouptime'=>$grptime,
        	'credit'=>$credit,
        );
        C::t('#hax_rechargedmf#hax_rechargedmf_grporder')->insert($orderarr, true);
        if($paytype=="zfb"){
            require_once("alipay/alipay_submit.class.php");
            /*** 请填写以下配置信息 ***/
            $appid = $hax_paysetup[1]['paymentkey01'];  //https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
            $notifyUrl = $_G['siteurl']."source/plugin/hax_rechargedmf/alipay/vipnotify.php";     //付款成功后的异步回调地址
            $outTradeNo = $orderarr['orderid'];     //你自己的商品订单号，不能重复
            $payAmount = $orderarr['money'];          //付款金额，单位:元
            $orderName = str_to_utf8(lang('plugin/hax_rechargedmf', 'slang48').'|'.$_G['setting']['sitename'].'|'.$_G['username']);    //订单标题
            $signType = 'RSA2';			//签名算法类型，支持RSA2和RSA，推荐使用RSA2
            $rsaPrivateKey = $hax_paysetup[1]['paymentkey02'];		//商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
            /*** 配置结束 ***/
            $aliPay = new AlipayService();
            $aliPay->setAppid($appid);
            $aliPay->setNotifyUrl($notifyUrl);
            $aliPay->setRsaPrivateKey($rsaPrivateKey);
            $aliPay->setTotalFee($payAmount);
            $aliPay->setOutTradeNo($outTradeNo);
            $aliPay->setOrderName($orderName);
            
            $result = $aliPay->doPay();
            $result = $result['alipay_trade_precreate_response'];
            if($result['code'] && $result['code']=='10000'){
                $url= $_G['siteurl'].'plugin.php?id=hax_rechargedmf:qr&size=4&aliurl='.$result['qr_code'];
                $zfbrlist = array(
                    'outTradeNo'=>$outTradeNo,
                    'img'=>'<img class="phpqrcode" src="'.$url.'">',
                    'money'=>$payAmount,
                    'aliurl'=>$result['qr_code'],
                );
                
                echo json_encode($zfbrlist, JSON_UNESCAPED_UNICODE);
                exit();
            }else{
                echo $result['msg'].' : '.$result['sub_msg'];
                exit();
            }
        }elseif($paytype=="wxzf"){
            require_once("wxpay/wxpay_submit.class.php");
            $mchid = $hax_paysetup[2]['paymentkey02'];          //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
            $appid = $hax_paysetup[2]['paymentkey01'];  //公众号APPID 通过微信支付商户资料审核后邮件发送
            $apiKey = $hax_paysetup[2]['paymentkey03'];   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
            $wxPay = new WxpayService($mchid,$appid,$apiKey);
            $outTradeNo = $orderarr['orderid'];     //你自己的商品订单号
            $payAmount = $orderarr['money'];          //付款金额，单位:元
            $orderName = str_to_utf8(lang('plugin/hax_rechargedmf', 'slang48').'|'.$_G['setting']['sitename'].'|'.$_G['username']);    //订单标题
            $notifyUrl = $_G['siteurl']."source/plugin/hax_rechargedmf/wxpay/vipnotify.php";     //付款成功后的回调地址(不要有问号)
            $payTime = time();      //付款时间
            $arr = $wxPay->createJsBizPackage($payAmount,$outTradeNo,$orderName,$notifyUrl,$payTime);
            //生成二维码
            $url = $_G['siteurl'].'plugin.php?id=hax_rechargedmf:qr&aliurl='.$arr['code_url'];
            $wxrlist = array(
                'outTradeNo'=>$outTradeNo,
                'img'=>'<img class="phpqrcode" src="'.$url.'">',
                'money'=>$payAmount,
                'aliurl'=>$arr['code_url'],
                // 'ceshi'=>$notifyUrl,
            );
            
            echo json_encode($wxrlist, JSON_UNESCAPED_UNICODE);
            exit();
            
            // echo '3333333';
        }
    }elseif ($paytype=="km") {
        if(!$cardid) {
            // echo json_encode("km_weishuru", JSON_UNESCAPED_UNICODE);
			echo "km_weishuru";
			exit();
		}
		if(!($card = C::t('common_card')->fetch($_POST['cardid']))) {
		  //  echo json_encode("km_bucunzai", JSON_UNESCAPED_UNICODE);
		    echo "km_bucunzai";
		    exit();
		} else {
		    if($card['status'] == 2) {
		      //  echo json_encode("km_yishiyong", JSON_UNESCAPED_UNICODE);
				echo "km_yishiyong";
				exit();
			}
			if($card['cleardateline'] < TIMESTAMP) {
			 //   echo json_encode("km_yiguoqi", JSON_UNESCAPED_UNICODE);
				echo "km_yiguoqi";
				exit();
			}
			if($card['price'] < $money) {
			 //   echo json_encode("km_yiguoqi", JSON_UNESCAPED_UNICODE);
				echo "km_mianzhibuzu";
				exit();
			}
			
			
			C::t('common_card')->update($card['id'], array('status' => 2, 'uid' => $_G['uid'], 'useddateline' => $_G['timestamp']));
			if($hax_rechargedmf['grp_kmjf']==1){
			    updatemembercount($_G[uid], array($card['extcreditskey'] => $card['extcreditsval']), true, 'CDC', 1);
			 //   $kmjfstr = $_G['setting']['extcredits'][$card['extcreditskey']]['title']."+".$card['extcreditsval'];
			}
			if($credit){
			    updatemembercount($_G[uid], array('extcredits'.$hycheck[crd][1]=>$hycheck[crd][2]), true, '', 0, '',lang('plugin/hax_rechargedmf', 'slang23'),lang('plugin/hax_rechargedmf', 'slang35'));
			}
			
			$ret = buygroup($grpid, $grptime,$_G['uid']);
			
            $orderarr=array(
            	'orderid'=>$card['id'],
            	'uid'=>$_G['uid'],
            	'usname'=>$_G['username'],
            	'money'=>$money,
            	'type'=>3,
            	'time'=>$_G['timestamp'],
            	'groupid'=>$grpid,
            	'groupname'=>$grpname,
            	'grouptime'=>$grptime,
            	'totime'=>$ret['times'],
            	'credit'=>$credit,
            	'state'=>1,
            	'zftime'=>$_G['timestamp'],
            );
            C::t('#hax_rechargedmf#hax_rechargedmf_grporder')->insert($orderarr, true);
            if($orderarr['credit']!="0"){
                $orderarr['credit'] = explode("|",$orderarr['credit']);
    		    updatemembercount($orderarr['uid'], array('extcredits'.$orderarr['credit'][0]=>$orderarr['credit'][1]), true, '', 0, '',lang('plugin/hax_rechargedmf', 'slang23'),lang('plugin/hax_rechargedmf', 'slang35'));
    		}
			echo "km_success";
			exit();
		}
    }elseif($paytype=="jfzf"){
        $grp_zfcrdname = $_G['setting']['extcredits'][$hax_paysetup[4]['paymentkey01']]['title'];
        if(!$grp_zfcrdname){
            $errlist = array();
        	$errlist["err"] = "grp_jfzf_is_null";
        	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
        	exit();
        }
        $nowdate=dgmdate($_G['timestamp'], 'YmdHis');
        $orderid = $mod.$nowdate.uniqid();
        $grp_zfopname = $hax_rechargedmf['grp_jfzfname'] ? $hax_rechargedmf['grp_jfzfname'] : lang("plugin/hax_rechargedmf", "slang94");
        $creditdata = $_G['cache']['hax_rechargedmf_credit'] ? $_G['cache']['hax_rechargedmf_credit'] : C::t('#hax_rechargedmf#hax_rechargedmf_credit')->fetch_all();
        if($grp_zfcrdname){
            $grp_zfcrdnum = getuserprofile('extcredits'.$hax_paysetup[4]['paymentkey01']);
            $grp_zfcrdnum = $grp_zfcrdnum ? $grp_zfcrdnum : 0;
            foreach ($creditdata as $k=>$v){
                if($hax_paysetup[4]['paymentkey01']==$v['creditid']){
                    $grp_zfcrdbili = $v['bili'];
                }
            }
            $zfcrd = abs(intval($money * $grp_zfcrdbili));
            $zfcrd = $zfcrd ? $zfcrd : 1;
            if($grp_zfcrdnum<$zfcrd){
                $errlist = array();
            	$errlist["err"] = "grp_jfzf_not_enough";
            	echo json_encode($errlist, JSON_UNESCAPED_UNICODE);
            	exit();
            }
        }
        
        $ret = buygroup($grpid, $grptime,$_G['uid']);
        updatemembercount($_G[uid], array('extcredits'.$hax_paysetup[4]['paymentkey01']=>plus_minus_conversion($zfcrd)), true, '', 0, '',lang('plugin/hax_rechargedmf', 'slang48'),lang('plugin/hax_rechargedmf', 'slang48'));
        $orderarr=array(
        	'orderid'=>$orderid,
        	'uid'=>$_G['uid'],
        	'usname'=>$_G['username'],
        	'money'=>$money,
        	'zfcrd'=>$hax_paysetup[4]['paymentkey01']."|".$zfcrd,
        	'type'=>4,
        	'time'=>$_G['timestamp'],
        	'groupid'=>$grpid,
        	'groupname'=>$grpname,
        	'grouptime'=>$grptime,
        	'totime'=>$ret['times'],
        	'credit'=>$credit,
        	'state'=>1,
        	'zftime'=>$_G['timestamp'],
        );
        C::t('#hax_rechargedmf#hax_rechargedmf_grporder')->insert($orderarr, true);
        
        if($orderarr['credit']!="0"){
            $orderarr['credit'] = explode("|",$orderarr['credit']);
		    updatemembercount($orderarr['uid'], array('extcredits'.$orderarr['credit'][0]=>$orderarr['credit'][1]), true, '', 0, '',lang('plugin/hax_rechargedmf', 'slang23'),lang('plugin/hax_rechargedmf', 'slang35'));
		}
        $reslist = array();
        $reslist["msg"] = "jfzf_success";
        echo json_encode($reslist, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?>