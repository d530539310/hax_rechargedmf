<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IDENTIFIER','hax_rechargedmf');
require_once("alipay/vip_function.func.php");
global $_G;
loadcache('plugin');
$hax_rechargedmf = $_G['cache']['plugin'][IDENTIFIER];
// $lang = $scriptlang['hax_rechargedmf'];
$pluginurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier='.IDENTIFIER.'&pmod=grporder';
if (submitcheck("forumsets")) {
	if(is_array($_GET['delete'])) {
			C::t('#hax_rechargedmf#hax_rechargedmf_grporder')->delete($_GET['delete']);
	}
	cpmsg(lang('plugin/hax_rechargedmf', 'slang36'), 'action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier=hax_rechargedmf&pmod=grporder', 'succeed');
}
if (submitcheck("del_all")) {
	C::t('#hax_rechargedmf#hax_rechargedmf_grporder')->del_order();
	cpmsg(lang('plugin/hax_rechargedmf', 'slang36'), 'action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier=hax_rechargedmf&pmod=grporder', 'succeed');
}

$orderid = htmlspecialchars($_GET['orderid']);
$username = htmlspecialchars($_GET['username']);
$optime = htmlspecialchars($_GET['optime']);
$edtime = htmlspecialchars($_GET['edtime']);

$acountarr = acountgrporder();

$where = $param = '';
if($_GET['orderid']){
	$where .= " AND orderid='".daddslashes(htmlspecialchars($_GET['orderid'])).'\'';
	$param .= '&orderid='.urlencode($_GET['orderid']);
}
if($_GET['state']){
    if($_GET['state'] == 2){$gstate = 0;}
    if($_GET['state'] == 1){$gstate = 1;}
	$where .= " AND state=".intval($gstate);
	$param .= '&state='.intval($_GET['state']);
}
if($_GET['type']){
    $where .= " AND type=".intval($_GET['type']);
	$param .= '&type='.intval($_GET['type']);
}
if($_GET['username']){
	$where .= " AND usname='".daddslashes(htmlspecialchars($_GET['username'])).'\'';
	$param .= '&username='.urlencode($_GET['username']);
	
}
if($_GET['optime']){
	$where .= " AND time>".strtotime($_GET['optime']);
	$param .= '&optime='.urlencode($_GET['optime']);
}
if($_GET['edtime']){
	$where .= " AND time<".strtotime($_GET['edtime']);
	$param .= '&edtime='.urlencode($_GET['edtime']);
}

$hyurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier=hax_rechargedmf&pmod=grporder'.$param;
$perpage = $hax_rechargedmf['czpages'] ? $hax_rechargedmf['czpages'] : 20;
$curpage = max(1, intval($_GET['page']));
$start = ($curpage-1)*$perpage;
$hycount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null $where"));
$thisczcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null $where AND state = 1"));
$thisczmoney = DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null $where AND type != 4 AND state = 1"));

if ($hycount) {
    $hylist = array();
    $query = DB::query("SELECT * FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null $where ORDER BY time DESC LIMIT $start,$perpage");
    while ($value = DB::fetch($query)) {
        $hylist[] = $value;
    }
}
$blank = '<span style="color:#ccc;">--</span>';
foreach($hylist as $k => $val){
    $hylist[$k]['grpname'] = DB::result(DB::query("SELECT grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid = $val[groupid]"));
    if($val['credit']){
        $val['credit'] = explode("|",$val['credit']);
        $hylist[$k]['creditt'] = $val['credit'][1].$_G['setting']['extcredits'][$val['credit'][0]]['title'];
    }
    $hylist[$k]['grouptime'] = $val['grouptime'] ? '(+<font color="#4caf50">'.$val['grouptime'].'</font>'.lang('plugin/hax_rechargedmf', 'slang34').')' : '';
    $hylist[$k]['totime'] = $val['totime'] ? dgmdate($val['totime'], 'Y/m/d H:i') : '<font color="red">'.lang('plugin/hax_rechargedmf', 'slang33').'</font>';
    if($val['state']==0){$hylist[$k]['grouptime']='';$hylist[$k]['totime']='<font color="#CCCCCC">--</font>';}
    $hylist[$k]['state'] = $val['state']==0 ? '<font color="#607D8B">'.lang('plugin/hax_rechargedmf', 'slang25').'</font>' : '<font color="#F44336">'.lang('plugin/hax_rechargedmf', 'slang26').'</font>' ;
    $hylist[$k]['zfcrd'] = explode("|",$val['zfcrd']);
    $hylist[$k]['zfcrd'][0] = $_G['setting']['extcredits'][$hylist[$k]['zfcrd'][0]]['title'];
    $hylist[$k]['money'] = $val['type']==4 ? $hylist[$k]['zfcrd'][1].$hylist[$k]['zfcrd'][0] : $hylist[$k]['money'].lang('plugin/hax_rechargedmf', 'slang6');
    if($val['type']==1){
        $hylist[$k]['type'] = lang('plugin/hax_rechargedmf', 'slang27');
    }elseif ($val['type']==2) {
        $hylist[$k]['type'] = lang('plugin/hax_rechargedmf', 'slang28');
    }elseif ($val['type']==3) {
        $hylist[$k]['type'] = lang('plugin/hax_rechargedmf', 'slang29');
    }elseif ($val['type']==4) {
        $hylist[$k]['type'] = $hax_rechargedmf['grp_jfzfname'] ? $hax_rechargedmf['grp_jfzfname'] : lang('plugin/hax_rechargedmf', 'slang94');
    }
}
$multi = multi($hycount, $perpage, $curpage, $_G['siteurl'].$hyurl);
include template('hax_rechargedmf:grporder');
?>