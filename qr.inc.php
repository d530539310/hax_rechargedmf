<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $_G;
$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
require_once("alipay/phpqrcode.class.php");
$aliurl = $_GET['aliurl'];
if($aliurl){
$errorCorrectionLevel = 'L';
$matrixPointSize = $GET['size'] ? $GET['size'] : 4;
// $url= 'source/plugin/hax_rechargedmf/'.$outTradeNo.'.png';
$QR = QRcode::png($aliurl, false, $errorCorrectionLevel, $matrixPointSize, 0);
}