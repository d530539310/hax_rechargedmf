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
$pluginurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier='.IDENTIFIER.'&pmod=czorder';
if (submitcheck("forumsets")) {
	if(is_array($_GET['delete'])) {
			C::t('#hax_rechargedmf#hax_rechargedmf_orderlog')->delete($_GET['delete']);
	}
	cpmsg(lang('plugin/hax_rechargedmf', 'slang36'), 'action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier=hax_rechargedmf&pmod=czorder', 'succeed');
}
if (submitcheck("del_all")) {
	C::t('#hax_rechargedmf#hax_rechargedmf_orderlog')->del_order();
	cpmsg(lang('plugin/hax_rechargedmf', 'slang36'), 'action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier=hax_rechargedmf&pmod=czorder', 'succeed');
}
$orderid = htmlspecialchars($_GET['orderid']);
$username = htmlspecialchars($_GET['username']);
$optime = htmlspecialchars($_GET['optime']);
$edtime = htmlspecialchars($_GET['edtime']);

$acountarr = acountorderlog();

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
$creditdata = $_G['cache']['hax_rechargedmf_credit'] ? $_G['cache']['hax_rechargedmf_credit'] : C::t('#hax_rechargedmf#hax_rechargedmf_credit')->fetch_all();
$czurl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin["pluginid"].'&identifier=hax_rechargedmf&pmod=czorder'.$param;
$perpage = $hax_rechargedmf['czpages'] ? $hax_rechargedmf['czpages'] : 20;
$curpage = max(1, intval($_GET['page']));
$start = ($curpage-1)*$perpage;
$czcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null $where"));
$thisczcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null $where AND state = 1"));
$thisczmoney = DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null $where AND state = 1"));

if ($czcount) {
    $czlist = array();
    $query = DB::query("SELECT * FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null $where ORDER BY time DESC LIMIT $start,$perpage");
    while ($value = DB::fetch($query)) {
            $czlist[] = $value;
    }
}
$blank = '<span style="color:#ccc;">--</span>';
foreach($czlist as $k => $val){
	$czlist[$k]['time']=dgmdate($val['time'], 'Y/m/d H:i');
	if($val['credit2']){
	    $val['credit2']=explode("|",$val['credit2']);
	    $val['credit22']='+(<i style="color:#4caf50;">'.$val['credit2'][1].$_G['setting']['extcredits'][$val['credit2'][0]]['title'].'</i>)';
	}
	$czlist[$k]['cradit']='<b style="color:#c30;">'.$val['credit'].'</b>'.$_G['setting']['extcredits'][$val['credittype']]['title'].$val['credit22'];
	$czlist[$k]['state']=$val['state']==0 ? '<font color="#607D8B">'.lang('plugin/hax_rechargedmf', 'slang25').'</font>' : '<font color="#F44336">'.lang('plugin/hax_rechargedmf', 'slang26').'</font>' ;
	if($val['type']==1){
	    $czlist[$k]['type'] = lang('plugin/hax_rechargedmf', 'slang27');
	}elseif($val['type']==2){
	    $czlist[$k]['type'] = lang('plugin/hax_rechargedmf', 'slang28');
	}elseif($val['type']==3){
	    $czlist[$k]['type'] = lang('plugin/hax_rechargedmf', 'slang29');
	}
	$czlist[$k]['zftime']=$val['zftime'] ? dgmdate($val['zftime'], 'Y/m/d H:i') : $blank;
}
$multi = multi($czcount, $perpage, $curpage, $_G['siteurl'].$czurl);
include template('hax_rechargedmf:czorder');
?>