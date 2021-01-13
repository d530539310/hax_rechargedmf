<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IDENTIFIER','hax_rechargedmf');
global $_G;
loadcache('plugin');
$hax_rechargedmf = $_G['cache']['plugin'][IDENTIFIER];
$lang = $scriptlang['hax_rechargedmf'];
$pluginurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier='.IDENTIFIER.'&pmod=czconfig';
if (submitcheck("forumset")) {
	$crdform = daddslashes($_GET['crdform']);
	if($crdform) {
		foreach($crdform as $crdid => $val) {
			if($crdform[$crdid]){
			    $val['bili'] = abs(round($val['bili'], 1));
			    $val['state'] = abs($val['state']);
			    $val['custom'] = abs($val['custom']);
			    $val['shunxu'] = abs($val['shunxu']);
				C::t('#hax_rechargedmf#hax_rechargedmf_credit')->insert(array('creditid' => $crdid,'descr' => $val['descr'],'money' => $val['money'],'send' => $val['send'],'custom' => $val['custom'],'bili' => $val['bili'],'state' => $val['state'],'shunxu' => $val['shunxu']), false, true);
			}
		}
	}
	require_once libfile('function/cache');
	savecache('hax_rechargedmf_credit', $crdform);
	cpmsg(lang('plugin/hax_rechargedmf', 'slang7'), 'action=plugins&operation=config&identifier=hax_rechargedmf&pmod=czconfig', 'succeed');
}

showtips(lang('plugin/hax_rechargedmf', 'slang51'));
$creditdata = C::t('#hax_rechargedmf#hax_rechargedmf_credit')->fetch_all();
$crdlist = array();

foreach($_G['setting']['extcredits'] as $k=>$v){
    $crdlist[$k]['title'] = $v['title'];
    $crdlist[$k]['descr'] = $creditdata[$k]['descr'];
    $crdlist[$k]['money'] = $creditdata[$k]['money'];
    $crdlist[$k]['send'] = $creditdata[$k]['send'];
    $crdlist[$k]['cuschecked'] = $creditdata[$k]['custom'] ? 'checked="checked"' : '';
    $crdlist[$k]['bili'] = htmlspecialchars($creditdata[$k]['bili']);
    $crdlist[$k]['shunxu'] = htmlspecialchars($creditdata[$k]['shunxu']);
    $crdlist[$k]['checked'] = $creditdata[$k]['state'] ? 'checked="checked"' : '';
}
include template('hax_rechargedmf:czconfig');
?>