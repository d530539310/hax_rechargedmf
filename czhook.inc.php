<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('IDENTIFIER','hax_rechargedmf');
require_once("alipay/vip_function.func.php");
global $_G;
$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
if(!$_G['uid']&&!$hax_rechargedmf['ykkj']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
$navtitle = $hax_rechargedmf['paytitle'];
$acmax = acountmax();
loadcache('hax_rechargedmf_credit');
$hax_paysetup = $_G['cache']['hax_rechargedmf_paysetup'] ? $_G['cache']['hax_rechargedmf_paysetup'] : C::t('#hax_rechargedmf#hax_rechargedmf_paysetup')->fetch_all();
$creditdata = $_G['cache']['hax_rechargedmf_credit'] ? $_G['cache']['hax_rechargedmf_credit'] : C::t('#hax_rechargedmf#hax_rechargedmf_credit')->fetch_all();
$groupdata = $_G['cache']['hax_rechargedmf_group'] ? $_G['cache']['hax_rechargedmf_group'] : C::t('#hax_rechargedmf#hax_rechargedmf_group')->fetch_all();

// $creditdatabk = $creditdata;
$shunxu = array();  
foreach($creditdata as $k=>$v){
    if($v['state']==0){
        unset($creditdata[$k]);
    }else{
        $shunxu[$k] = $v['shunxu'];
    }
}
array_multisort($shunxu, $creditdata);
$lcrd = current($creditdata);
$lcrdid = $lcrd['creditid'];
$lcrdname = $_G['setting']['extcredits'][$lcrdid]['title'];
$lcrdnum = getuserprofile('extcredits'.$lcrdid);
$query = DB::query("SELECT money FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE uid = $_G[uid] AND state = 1 ");
    while ($value = DB::fetch($query)) {
        $mnlist[] = $value['money'];
    }
$ljmoney = array_sum($mnlist);

$wtlj = $hax_rechargedmf['wtlj'];

$allod_grp = unserialize($hax_rechargedmf['allod_grp']);
if (in_array($_G['groupid'], $allod_grp)){
    if($_GET['mod']=='allczod'){
        $pluginurl = $_G['siteurl'].'plugin.php?id='.IDENTIFIER.':czhook&mod=allczod';
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
        $czurl = $_G['siteurl'].'plugin.php?id='.IDENTIFIER.':czhook&mod=allczod'.$param;
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
        $multi = multi($czcount, $perpage, $curpage, $czurl);
    }elseif ($_GET['mod']=='allvipod') {
        $pluginurl = $_G['siteurl'].'plugin.php?id='.IDENTIFIER.':czhook&mod=allvipod';
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
        
        $hyurl = $_G['siteurl'].'plugin.php?id='.IDENTIFIER.':czhook&mod=allvipod'.$param;
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
        $blank = '<font color="#CCC">--</font>';
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
        $multi = multi($hycount, $perpage, $curpage, $hyurl);
    }
}

if($_GET['mod']=='vipjl'){
    if(!$hax_rechargedmf['grp_switch']){
        if ($hax_rechargedmf['crd_switch']) {
            dheader('location: plugin.php?id=hax_rechargedmf:czhook');
        }else{
            showmessage(lang("plugin/hax_rechargedmf", "slang95"),$_G['siteurl'], array(), array('locationtime'=>true,'refreshtime'=>3, 'showdialog'=>1, 'showmsg' => true));
        }
    }
    if(!$_G['uid']) {
    	showmessage('not_loggedin', NULL, array(), array('login' => 1));
    }
    $preg = "/^http(s)?:\\/\\/.+/";
    $grpicon = preg_match($preg,$_G[group][icon]) ? $_G[group][icon] : "data/attachment/common/".$_G[group][icon];
    $czurl = $_G['siteurl'].'plugin.php?id=hax_rechargedmf:czhook&mod=vipjl';
    $perpage = $hax_rechargedmf['czpages'] ? $hax_rechargedmf['czpages'] : 20;
    $curpage = max(1, intval($_GET['page']));
    $start = ($curpage-1)*$perpage;
    $hycount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE uid = $_G[uid]"));
    
    if ($hycount) {
        $hylist = array();
        $query = DB::query("SELECT * FROM ".DB::table('hax_rechargedmf_grporder')." WHERE uid = $_G[uid] ORDER BY time DESC LIMIT $start,$perpage");
        while ($value = DB::fetch($query)) {
                $hylist[] = $value;
        }
    }
    for($i=0;$hylist[$i];$i++){
        $groupid = $hylist[$i]['groupid'];
        $hylist[$i]['grpname'] = DB::result(DB::query("SELECT grouptitle FROM ".DB::table('common_usergroup')." WHERE groupid = $groupid"));
        $hylist[$i]['groupname'] = '<i style="font-size: 12px;color:#607D8B;">'.$hylist[$i]['groupname'].'</i>';
        $hylist[$i]['time'] = date("Y-m-d H:i:s",$hylist[$i]['time']);
        if($hylist[$i]['state']==0){
            $hylist[$i]['totime'] = '--';
        }else{
            $hylist[$i]['totime'] = $hylist[$i]['totime'] ? date("Y-m-d H:i:s",$hylist[$i]['totime']) : lang("plugin/hax_rechargedmf", "slang33");
        }
        $hylist[$i]['grouptime'] = $hylist[$i]['grouptime']==0 ? '(<i class="igreen" style="font-style:normal;">'.lang("plugin/hax_rechargedmf", "slang33").'</i>)' : '<i style="font-style:normal;">(+'.$hylist[$i]['grouptime'].lang("plugin/hax_rechargedmf", "slang34").')</i>';
        if(!empty($hylist[$i]['credit'])){
            $hylist[$i]['credit'] = explode("|",$hylist[$i]['credit']);
            $hylist[$i]['credit'][0] = $_G['setting']['extcredits'][$hylist[$i]['credit'][0]]['title'];
            $hylist[$i]['credit'] = "<i style='color:#4caf50;font-size: 12px;'>(+".$hylist[$i]['credit'][1].$hylist[$i]['credit'][0].")</i>";
        }else{
            $hylist[$i]['credit'] = "";
        }
        
        $hylist[$i]['state'] = $hylist[$i]['state']==0 ? lang("plugin/hax_rechargedmf", "slang25") : "<i style='font-style: normal;color: #E91E63;'>".lang("plugin/hax_rechargedmf", "slang26")."</i>";
        if($hylist[$i]['type']==1){
            $hylist[$i]['type'] = '<span style="color:#2196f3;font-size: 12px;">'.lang("plugin/hax_rechargedmf", "slang27").'</span>';
            $hylist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang47");
            $hylist[$i]['money'] = '<span>'.lang("plugin/hax_rechargedmf", "slang0").$hylist[$i]['money'].'</span>';
        }elseif ($hylist[$i]['type']==2) {
            $hylist[$i]['type'] = '<span style="color:#0aba07;font-size: 12px;">'.lang("plugin/hax_rechargedmf", "slang28").'</span>';
            $hylist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang47");
            $hylist[$i]['money'] = '<span>'.lang("plugin/hax_rechargedmf", "slang0").$hylist[$i]['money'].'</span>';
        }elseif ($hylist[$i]['type']==3) {
            $hylist[$i]['type'] = '<span style="color:#673ab7;font-size: 12px;">'.lang("plugin/hax_rechargedmf", "slang29").'</span>';
            $hylist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang29");
            $hylist[$i]['money'] = '<span>'.lang("plugin/hax_rechargedmf", "slang0").$hylist[$i]['money'].'</span>';
        }elseif ($hylist[$i]['type']==4) {
            $hylist[$i]['type'] = $hax_rechargedmf['grp_jfzfname'] ? $hax_rechargedmf['grp_jfzfname'] : lang("plugin/hax_rechargedmf", "slang94");
            $hylist[$i]['type'] = '<span style="color:#FF9800;font-size: 12px;">'.$hylist[$i]['type'].'</span>';
            $hylist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang47");
            $hylist[$i]['zfcrd'] = explode("|",$hylist[$i]['zfcrd']);
            $hylist[$i]['zfcrd'][0] = $_G['setting']['extcredits'][$hylist[$i]['zfcrd'][0]]['title'];
            if(checkmobile()){
                $hylist[$i]['money'] = $hylist[$i]['zfcrd'][1].$hylist[$i]['zfcrd'][0];
            }else{
                $hylist[$i]['money'] = '<span style="font-size: 12px;color: #9E9E9E; text-decoration: line-through;">'.lang("plugin/hax_rechargedmf", "slang0").$hylist[$i]['money'].'</span></br>';
                $hylist[$i]['zfcrd'] = $hylist[$i]['zfcrd'][1].$hylist[$i]['zfcrd'][0];
            }
            
        }
    }
    $multi = multi($hycount, $perpage, $curpage, $czurl);
    
    $expirylist = getgrouplist();
    $nowgrouptime = getnowgrouptime();
    
}elseif($_GET['mod']=='vip'){
    if(!$hax_rechargedmf['grp_switch']){
        if ($hax_rechargedmf['crd_switch']) {
            dheader('location: plugin.php?id=hax_rechargedmf:czhook');
        }else{
            showmessage(lang("plugin/hax_rechargedmf", "slang95"),$_G['siteurl'], array(), array('locationtime'=>true,'refreshtime'=>3, 'showdialog'=>1, 'showmsg' => true));
        }
    }
    $preg = "/^http(s)?:\\/\\/.+/";
    $grpicon = preg_match($preg,$_G[group][icon]) ? $_G[group][icon] : "data/attachment/common/".$_G[group][icon];
    $sgroups = array();
    foreach(C::t('common_usergroup')->range_orderby_creditshigher() as $group) {
    	if($group['type'] == 'special' && $group['radminid'] == '0') {
    		$sgroups[] = $group['groupid'];
    	}
    }
    $grpshunxu = array();  
    foreach($groupdata as $k=>$v){
    $grpshunxu[$k] = $v['shunxu'];
    }
    array_multisort($grpshunxu, $groupdata);
    $n = 0;
    foreach($groupdata as $k => $group){
        if (in_array($group['groupid'], $sgroups)&&$group['state']){
            $groupdata[$k]['grp_active'] = $n==0?'grp_active':'';
            $groupdata[$k]['name'] = explode("|",$group['name']);
            $groupdata[$k]['moneyop'] = explode("\n",$group['moneyop']);
            foreach($groupdata[$k]['moneyop'] as $ka => $v){
                $groupdata[$k]['moneyop'][$ka] = explode("|",$v);
                foreach($groupdata[$k]['moneyop'][$ka] as $kb => $va){
                    if(strpos($va,',') !== false){
                        $groupdata[$k]['moneyop'][$ka][$kb] = explode(",",$va);
                    }
                }
            }
            $groupdata[$k]['scredit'] = explode("\n",$group['scredit']);
            foreach($groupdata[$k]['scredit'] as $ka => $v){
                $groupdata[$k]['scredit'][$ka] = explode("|",$v);
            }
            $groupdata[$k]['descr'] = str_replace('\\','',$groupdata[$k]['descr']);
            $groupdata[$k]['modescr'] = str_replace('\\','',$groupdata[$k]['modescr']);
            $n++;
        }else{
            unset($groupdata[$k]);
        }
    }
    $grp_dtip = $hax_rechargedmf['grp_dtip'];
    $viped_tip = $hax_rechargedmf['viped_tip'];
    $nowgrouptime = getnowgrouptime();
    if($hax_paysetup[4]['state']){
        $hax_paysetup[4]['state'] = in_array($hax_paysetup[4]['paymentkey01'], array_column($creditdata, 'creditid')) ? $hax_paysetup[4]['state'] : 0;
    }
    if($hax_paysetup[4]['state']){
        $grp_zfopname = $hax_paysetup[4]['paymentkey02'] ? $hax_paysetup[4]['paymentkey02'] : lang("plugin/hax_rechargedmf", "slang94");
        $grp_zfcrdname = $_G['setting']['extcredits'][$hax_paysetup[4]['paymentkey01']]['title'];
        $grp_zfcrdnum = getuserprofile('extcredits'.$hax_paysetup[4]['paymentkey01']);
        $grp_zfcrdnum = $grp_zfcrdnum ? $grp_zfcrdnum : 0;
        foreach ($creditdata as $k=>$v){
            if($hax_paysetup[4]['paymentkey01']==$v['creditid']){
                $grp_zfcrdbili = $v['bili'];
            }
        }
    }
    
}elseif($_GET['mod']=='czjl'){
    if(!$hax_rechargedmf['crd_switch']){
        if ($hax_rechargedmf['grp_switch']) {
            dheader('location: plugin.php?id=hax_rechargedmf:czhook&mod=vip');
        }else{
            showmessage(lang("plugin/hax_rechargedmf", "slang95"),$_G['siteurl'], array(), array('locationtime'=>true,'refreshtime'=>3, 'showdialog'=>1, 'showmsg' => true));
        }
    }
    if(!$_G['uid']) {
    	showmessage('not_loggedin', NULL, array(), array('login' => 1));
    }
    foreach($creditdata as $k=>$v){
        if($v['state']==1){
            $creditdata[$k]['name'] = $_G['setting']['extcredits'][$creditdata[$k]['creditid']]['title'];
            $creditdata[$k]['num'] = getuserprofile('extcredits'.$creditdata[$k]['creditid']);
        }
    }
    $czurl = $_G['siteurl'].'plugin.php?id=hax_rechargedmf:czhook&mod=czjl';
    $perpage = $hax_rechargedmf['czpages'] ? $hax_rechargedmf['czpages'] : 20;
    $curpage = max(1, intval($_GET['page']));
    $start = ($curpage-1)*$perpage;
    $czcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE uid = $_G[uid]"));
    
    if ($czcount) {
        $czlist = array();
        $query = DB::query("SELECT * FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE uid = $_G[uid] ORDER BY time DESC LIMIT $start,$perpage");
        while ($value = DB::fetch($query)) {
                $czlist[] = $value;
        }
    }
    for($i=0;$czlist[$i];$i++){
        $czlist[$i]['time'] = date("Y-m-d H:i:s",$czlist[$i]['time']);
        $czlist[$i]['state'] = $czlist[$i]['state']==0 ? lang("plugin/hax_rechargedmf", "slang25") : "<i style='font-style: normal;color: #E91E63;'>".lang("plugin/hax_rechargedmf", "slang26")."</i>";
        if($czlist[$i]['credit2']){
            $czlist[$i]['credit2'] = explode("|",$czlist[$i]['credit2']);
            $czlist[$i]['credit222'] = "<i class='smallfont'>(+".$czlist[$i]['credit2'][1].$_G['setting']['extcredits'][$czlist[$i]['credit2'][0]]['title'].")</i>";
        }
        $czlist[$i]['credit'] = $czlist[$i]['credit'].$_G['setting']['extcredits'][$czlist[$i]['credittype']]['title'].$czlist[$i]['credit222'];
        if($czlist[$i]['type']==1){
            $czlist[$i]['type'] = '<span style="color:#2196f3">'.lang("plugin/hax_rechargedmf", "slang27").'</span>';
            $czlist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang47");
        }elseif ($czlist[$i]['type']==2) {
            $czlist[$i]['type'] = '<span style="color:#0aba07">'.lang("plugin/hax_rechargedmf", "slang28").'</span>';
            $czlist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang47");
        }elseif ($czlist[$i]['type']==3) {
            $czlist[$i]['type'] = '<span style="color:#673ab7">'.lang("plugin/hax_rechargedmf", "slang29").'</span>';
            $czlist[$i]['ororkm'] = lang("plugin/hax_rechargedmf", "slang29");
        }
        
    }
    $multi = multi($czcount, $perpage, $curpage, $czurl);
}else {
    if(!$hax_rechargedmf['crd_switch']){
        if ($hax_rechargedmf['grp_switch']) {
            dheader('location: plugin.php?id=hax_rechargedmf:czhook&mod=vip');
        }else{
            showmessage(lang("plugin/hax_rechargedmf", "slang95"),$_G['siteurl'], array(), array('locationtime'=>true,'refreshtime'=>3, 'showdialog'=>1, 'showmsg' => true));
        }
    }
    $i = '';
    foreach($creditdata as $k=>$v){
		if($v['state']==1){
		    $creditdata[$k]['crd_active'] = '';
		    $creditdata[$k]['nowcredit'] = getuserprofile('extcredits'.$v['creditid']);
		    $creditdata[$k]['creditname'] = $_G['setting']['extcredits'][$v['creditid']]['title'];
		    $creditdata[$k]['money'] = explode("|",$v['money']);
		    foreach($creditdata[$k]['money'] as $kk => $vv){
		        $creditdata[$k]['money'][$kk] = array('mon'=>$vv,'crd'=>intval($vv*$v['bili']));
		    }
		    $creditdata[$k]['send'] = explode("\n",$v['send']);
		    foreach($creditdata[$k]['send'] as $kk => $vv){
		        $vv = explode("|",$vv);
		        $kkk = $vv[0]-1;
		        if($creditdata[$k]['money'][$kkk]&&$_G['setting']['extcredits'][$vv[1]]['title']){
		            $creditdata[$k]['money'][$kkk]['sendid'] = $vv[1];
    		        $creditdata[$k]['money'][$kkk]['sendnum'] = $vv[2];
		        }
		    }
		    if(!$i){
		        $creditdata[$k]['crd_active'] = 'crd_active';
		        $crdidst = $v['creditid'];
		        $crdnamest = $creditdata[$k]['creditname'];
		        $nowcrdst = $creditdata[$k]['nowcredit'];
		    }
			$i++;
		}
	}
	
    $xylj = $hax_rechargedmf['xylj'];
    $ali_tip = $hax_rechargedmf['ali_tip'];
}

if(checkmobile()){
    include template('hax_rechargedmf:index'); 
}else{
    include template("diy:index",0,'source/plugin/hax_rechargedmf/template');
}