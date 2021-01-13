<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IDENTIFIER','hax_rechargedmf');
global $_G;
loadcache('plugin');
$hax_rechargedmf = $_G['cache']['plugin'][IDENTIFIER];
$lang = $scriptlang['hax_rechargedmf'];
$pluginurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier='.IDENTIFIER.'&pmod=payconfig';
if (submitcheck("forumset")) {
	$payform = daddslashes($_GET['payform']);
	if($payform) {
		foreach($payform as $payid => $val) {
			if($payform[$payid]){
			    $val['state'] = abs($val['state']);
				C::t('#hax_rechargedmf#hax_rechargedmf_paysetup')->insert(array('paymethodid' => $payid,'paymentkey01' => $val['paymentkey01'],'paymentkey02' => $val['paymentkey02'],'paymentkey03' => $val['paymentkey03'],'state' => $val['state']), false, true);
			}
		}
	}
	require_once libfile('function/cache');
	savecache('hax_rechargedmf_paysetup', $payform);
	cpmsg(lang('plugin/hax_rechargedmf', 'slang7'), 'action=plugins&operation=config&identifier=hax_rechargedmf&pmod=payconfig', 'succeed');
}

$hax_paysetup = C::t('#hax_rechargedmf#hax_rechargedmf_paysetup')->fetch_all();


include template('hax_rechargedmf:payconfig');