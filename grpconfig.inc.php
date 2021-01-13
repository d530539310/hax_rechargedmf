<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IDENTIFIER','hax_rechargedmf');
global $_G;
loadcache('plugin');
$hax_rechargedmf = $_G['cache']['plugin'][IDENTIFIER];
$lang = $scriptlang['hax_rechargedmf'];
$pluginurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier='.IDENTIFIER.'&pmod=grpconfig';
if (submitcheck("forumset")) {
	$grpform = daddslashes($_GET['grpform']);
	if($grpform) {
		foreach($grpform as $grpid => $val) {
			if($grpform[$grpid]){
			    $val['state'] = abs($val['state']);
			 //   $val['name'] = abs($val['state']);
			 //   $val['moneyop'] = abs($val['state']);
			 //   $val['scredit'] = abs($val['state']);
			 //   $val['descr'] = abs($val['state']);
			    $val['width'] = abs($val['width']);
			    $val['shunxu'] = abs($val['shunxu']);
				C::t('#hax_rechargedmf#hax_rechargedmf_group')->insert(array('groupid' => $grpid,'name' => $val['name'],'moneyop' => $val['moneyop'],'scredit' => $val['scredit'],'descr' => $val['descr'],'modescr' => $val['modescr'],'state' => $val['state'],'width' => $val['width'],'shunxu' => $val['shunxu']), false, true);
			}
		}
	}
	require_once libfile('function/cache');
	savecache('hax_rechargedmf_group', $grpform);
	cpmsg(lang('plugin/hax_rechargedmf', 'slang7'), 'action=plugins&operation=config&identifier=hax_rechargedmf&pmod=grpconfig', 'succeed');
}

showtips(lang('plugin/hax_rechargedmf', 'slang98'));
$groupdata = C::t('#hax_rechargedmf#hax_rechargedmf_group')->fetch_all();
$sgroups = array();

foreach(C::t('common_usergroup')->range_orderby_creditshigher() as $group) {
	if($group['type'] == 'special' && $group['radminid'] == '0') {

		// $specialgroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

		$sgroups[$group['groupid']] = $group;
		// $sgroupids .= ','.$group['groupid'];
	}
}
foreach($sgroups as $k=>$v){
    $sgroups[$k]['state'] = $groupdata[$k]['state'];
    $sgroups[$k]['name'] = $groupdata[$k]['name'];
    $sgroups[$k]['moneyop'] = $groupdata[$k]['moneyop'];
    $sgroups[$k]['scredit'] = $groupdata[$k]['scredit'];
    $sgroups[$k]['descr'] = str_replace('\\','',$groupdata[$k]['descr']);
    $sgroups[$k]['modescr'] = str_replace('\\','',$groupdata[$k]['modescr']);
    $sgroups[$k]['width'] = $groupdata[$k]['width'];
    $sgroups[$k]['shunxu'] = $groupdata[$k]['shunxu'];
    $sgroups[$k]['checked'] = $groupdata[$k]['state'] ? 'checked="checked"' : '';
}
include template('hax_rechargedmf:grpconfig');
?>