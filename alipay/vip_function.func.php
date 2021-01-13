<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function buygroup($groupid, $days, $uids){
	global $_G;
	require_once (libfile('function/forum'));
	$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
	$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
	$memberfieldforum = C::t('common_member_field_forum')->fetch($uids);
	$groupterms = dunserialize($memberfieldforum['groupterms']);
	unset($memberfieldforum);
	$extgroupidsarray = $extgroupids = array();
	foreach (array_unique(array_merge($extgroupids, array(0 => $groupid))) as $extgroupid) {
		if ($extgroupid) {
			$extgroupidsarray[] = $extgroupid;
		}
	}
	$extgroupidsnew = implode("\t", $extgroupidsarray);
	if ($days>0) {
	    $extkey = array_keys($groupterms['ext']);
	    if(in_array($groupid, $extgroupids)&&!in_array($groupid, $extkey)){
	        if ($hax_rechargedmf['viped_auto']==1) {switchgroup($groupid, $uids);}
        	$return['times'] = $groupterms['ext'][$groupid];
        	return $return;
	    }
        $groupterms['ext'][$groupid] = ($groupterms['ext'][$groupid] > TIMESTAMP ? $groupterms['ext'][$groupid] : TIMESTAMP) + $days * 86400;

		$groupexpirynew = groupexpiry($groupterms);

        $newmember = DB::fetch(DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid = $uids"));
        $extgroupids = $newmember['extgroupids'] ? explode("\t", $newmember['extgroupids']) : array();
        if($newmember['groupid']==$groupid){
            C::t('common_member')->update($uids, array('groupexpiry' => $groupexpirynew, ));
        }else{
            $extgroupidss = implode("\t", array_unique(array_merge($extgroupids, array(0 => $groupid))));
		    C::t('common_member')->update($uids, array('groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidss));
        }
// 		updatemembercount($uids, array($creditstrans => "-$amount"), true, 'UGP', $extgroupidsnew);

		C::t('common_member_field_forum')->update($uids, array('groupterms' => serialize($groupterms)));
	}elseif($days==0){
	    unset($groupterms['ext'][$groupid]);
	    if (empty($groupterms['ext'])){unset($groupterms['ext']);}
	    $groupexpirynew = groupexpiry($groupterms);

		C::t('common_member')->update($uids, array('groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));
// 		updatemembercount($uids, array($creditstrans => "-$amount"), true, 'UGP', $extgroupidsnew);

		C::t('common_member_field_forum')->update($uids, array('groupterms' => serialize($groupterms)));
	} else {
		C::t('common_member')->update($uids, array('extgroupids' => $extgroupidsnew));
	}
	$return['times'] = $groupterms['ext'][$groupid];
	if ($hax_rechargedmf['viped_auto']==1) {switchgroup($groupid, $uids);}
	return $return;
}
function switchgroup($groupid, $uids){
    global $_G;
    $groupid = intval($groupid);
    $extgroupidsarray = $extgroupids = $return = array();
// 	$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
    $newmember = DB::fetch(DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid = $uids"));
	$extgroupids = $newmember['extgroupids'] ? explode("\t", $newmember['extgroupids']) : array();
	if(!in_array($groupid, $extgroupids)) {
// 		showmessage('usergroup_not_found');
		$return['msg'] = lang('plugin/hax_rechargedmf', 'slang31');
		$return['state'] = 0;
		return $return;
	}
	if($newmember['groupid'] == 4 && $newmember['groupexpiry'] > 0 && $newmember['groupexpiry'] > TIMESTAMP) {
// 		showmessage('usergroup_switch_not_allow');
		$return['msg'] = lang('plugin/hax_rechargedmf', 'slang32');
		$return['state'] = 0;
		return $return;
	}
	$group = C::t('common_usergroup')->fetch($groupid);
// 	if(submitcheck('groupsubmit')) {
		$memberfieldforum = C::t('common_member_field_forum')->fetch($uids);
		$groupterms = dunserialize($memberfieldforum['groupterms']);
		unset($memberfieldforum);
		$extgroupidsnew = $newmember['groupid'];
		$groupexpirynew = $groupterms['ext'][$groupid];
		foreach($extgroupids as $extgroupid) {
			if($extgroupid && $extgroupid != $groupid) {
				$extgroupidsnew .= "\t".$extgroupid;
			}
		}
		if($newmember['adminid'] > 0 && $group['radminid'] > 0) {
			$newadminid = $newmember['adminid'] < $group['radminid'] ? $newmember['adminid'] : $group['radminid'];
		} elseif($newmember['adminid'] > 0) {
			$newadminid = $newmember['adminid'];
		} else {
			$newadminid = $group['radminid'];
		}

		C::t('common_member')->update($uids, array('groupid' => $groupid, 'adminid' => $newadminid, 'groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));
// 		showmessage('usergroups_switch_succeed', "home.php?mod=spacecp&ac=usergroup".($_GET['gid'] ? "&gid=$_GET[gid]" : '&do=list'), array('group' => $group['grouptitle']), array('showdialog' => 3, 'showmsg' => true, 'locationtime' => true));
// 	}
    $return['state'] = 1;
	return $return;
}
function getgrouplist(){
	global $_G;
	$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
	$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
	$groupterms = dunserialize($memberfieldforum['groupterms']);
	unset($memberfieldforum);
	$expgrouparray = $expirylist = $termsarray = array();

	if(!empty($groupterms['ext']) && is_array($groupterms['ext'])) {
		$termsarray = $groupterms['ext'];
	}
	if(!empty($groupterms['main']['time']) && (empty($termsarray[$_G['groupid']]) || $termsarray[$_G['groupid']] > $groupterm['main']['time'])) {
		$termsarray[$_G['groupid']] = $groupterms['main']['time'];
	}

	foreach($termsarray as $expgroupid => $expiry) {
		if($expiry <= TIMESTAMP) {
			$expgrouparray[] = $expgroupid;
		}
	}

	if(!empty($groupterms['ext'])) {
		foreach($groupterms['ext'] as $extgroupid => $time) {
			$expirylist[$extgroupid] = array('time' => dgmdate($time, 'd'), 'type' => 'ext', 'noswitch' => $time < TIMESTAMP);
		}
	}

	if(!empty($groupterms['main'])) {
		$expirylist[$_G['groupid']] = array('time' => dgmdate($groupterms['main']['time'], 'd'), 'type' => 'main');
	}

	$groupids = array();
	foreach($_G['cache']['usergroups'] as $groupid => $usergroup) {
		if(!empty($usergroup['pubtype'])) {
			$groupids[] = $groupid;
		}
	}
	$expiryids = array_keys($expirylist);
	if(!$expiryids && $_G['member']['groupexpiry']) {
		C::t('common_member')->update($_G['uid'], array('groupexpiry' => 0));
	}
	$groupids = array_merge($extgroupids, $expiryids, $groupids);
// 	$usermoney = $space['extcredits'.$_G['setting']['creditstrans']];
    if($groupids) {
		foreach(C::t('common_usergroup')->fetch_all($groupids) as $group) {
			$isexp = in_array($group['groupid'], $expgrouparray);
// 			if($_G['cache']['usergroups'][$group['groupid']]['pubtype'] == 'buy') {
// 				list($dailyprice) = explode("\t", $group['system']);
// 				$expirylist[$group['groupid']]['dailyprice'] = $dailyprice;
// 				$expirylist[$group['groupid']]['usermaxdays'] = $dailyprice > 0 ? round($usermoney / $dailyprice) : 0;
// 			} else {
// 				$expirylist[$group['groupid']]['usermaxdays'] = 0;
// 			}
			$expirylist[$group['groupid']]['maingroup'] = $group['type'] != 'special' || $group['system'] == 'private' || $group['radminid'] > 0;
			$expirylist[$group['groupid']]['grouptitle'] = $isexp ? '<s>'.$group['grouptitle'].'</s>' : $group['grouptitle'];
		}
	}
	return $expirylist;
}
function getnowgrouptime(){
	global $_G;
	$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
    $groupterms = dunserialize($memberfieldforum['groupterms']);
    unset($memberfieldforum);
    $expgrouparray = $expirylist = $termsarray = array();
    $grptime = lang('plugin/hax_rechargedmf', 'slang33');
    if(!empty($groupterms['ext'])) {
    	foreach($groupterms['ext'] as $extgroupid => $time) {
    		$expirylist[$extgroupid] = array('time' => dgmdate($time, 'd'), 'type' => 'ext', 'noswitch' => $time < TIMESTAMP);
    	}
    }
    // $groupid = DB::result(DB::query("SELECT groupid FROM ".DB::table('common_member')." WHERE uid = $_G[uid]"));
    if(!empty($groupterms['main'])) {
    	$expirylist[$_G['groupid']] = array('time' => dgmdate($groupterms['main']['time'], 'd'), 'type' => 'main');
    }
    if($expirylist){
        $group = C::t('common_usergroup')->fetch($_G['groupid']);
        if($group['type']=='special'){
            $grptime = $expirylist[$_G['groupid']]['time'];
        }elseif($group['type']=='system'){
            foreach ($expirylist as $grpid => $grpinfo){
                if($_G['groupid'] == $grpid){
                    $grptime = $grpinfo['time'];
                }
            }
        }
    }
	return $grptime;
}
function acountmax(){
    global $_G;
	$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
	$today = strtotime(date("Y-m-d"),time());
    $jfmax = array();
    $query = DB::query("SELECT money FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE state = 1 AND type = 1 AND time > $today");
    while ($value = DB::fetch($query)) {
            $jfmax[] = $value['money'];
    }
    $hymax = array();
    $query = DB::query("SELECT money FROM ".DB::table('hax_rechargedmf_grporder')." WHERE state = 1 AND type = 1 AND time > $today");
    while ($value = DB::fetch($query)) {
            $hymax[] = $value['money'];
    }
    $acmax = array_sum(array_merge($jfmax,$hymax));
    return $acmax;
}
function str_to_utf8($str){
	$str1 = diconv($str, 'utf-8', 'gbk');
	$str0 = diconv($str1, 'gbk', 'utf-8');
	if ($str0 == $str) {
		$tostr = $str1;
	}
	else {
		$tostr = $str;
	}
	return diconv($tostr, 'gbk', 'utf-8');
}
function checkcrdorder($crdid,$money,$zdycrdnum){
    global $_G;
    $creditdata = $_G['cache']['hax_rechargedmf_credit'] ? $_G['cache']['hax_rechargedmf_credit'] : C::t('#hax_rechargedmf#hax_rechargedmf_credit')->fetch_all();
    if($zdycrdnum){
        foreach ($creditdata as $k => $v){
            if($v['creditid'] == $crdid && $v['custom']){
                $v['money'] = explode("|",$v['money']);
                $ischeck = array();
                $ischeck['crdnum'] = intval($zdycrdnum);
                $ischeck['money'] = $ischeck['crdnum']/$v['bili'];
                $v['send'] = explode("\n",$v['send']);
                foreach($v['send'] as $kk => $vv){
                    $vv = explode("|",$vv);
                    if($vv[0]==count($v['money'])+1 && $_G['setting']['extcredits'][$vv[1]]['title']){
                        $ischeck['send']=$vv[1]."|".$vv[2];
                    }
                }
                return $ischeck;
            }
        }
    }else{
        foreach ($creditdata as $k => $v){
            $v['money'] = explode("|",$v['money']);
            if($v['creditid'] == $crdid && in_array($money, $v['money'])){
                $ischeck = array();
                $ischeck['money'] = $money;
                $ischeck['crdnum'] = intval($money*$v['bili']);
                $v['send'] = explode("\n",$v['send']);
                foreach($v['send'] as $kk => $vv){
                    $vv = explode("|",$vv);
                    if($vv[0]==array_search($money, $v['money'])+1 && $_G['setting']['extcredits'][$vv[1]]['title']){
                        $ischeck['send']=$vv[1]."|".$vv[2];
                    }
                }
                return $ischeck;
            }
        }
    }
}
function checkgrp($grpid,$grptime,$money){
    global $_G;
    $groupdata = $_G['cache']['hax_rechargedmf_group'] ? $_G['cache']['hax_rechargedmf_group'] : C::t('#hax_rechargedmf#hax_rechargedmf_group')->fetch_all();
    foreach($groupdata as $k=>$v){
        if($k==$grpid){
            $groupdata = $v;
            $checkid = 1;
            break;
        }
    }
    if($checkid&&$groupdata['state']){
        unset($groupdata['descr']);
        unset($groupdata['width']);
        unset($groupdata['shunxu']);
        $groupdata['name'] = explode("|",$groupdata['name']);
        $groupdata['moneyop'] = explode("\n",$groupdata['moneyop']);
        foreach($groupdata['moneyop'] as $k => $v){
            $groupdata['moneyop'][$k] = explode("|",$v);
            foreach($groupdata['moneyop'][$k] as $kb => $va){
                if(strpos($va,',') !== false){
                    $groupdata['moneyop'][$k][$kb] = explode(",",$va);
                }
            }
            if($groupdata['moneyop'][$k][0][0]==$grptime&&(number_format($groupdata['moneyop'][$k][1][0],2,'.','')==$money||number_format($groupdata['moneyop'][$k][1],2,'.','')==$money)){
                $checkmoney = $k+1;
                break;
            }
        }
        $groupdata['scredit'] = explode("\n",$groupdata['scredit']);
        foreach($groupdata['scredit'] as $k => $v){
            $groupdata['scredit'][$k] = explode("|",$v);
            if($groupdata['scredit'][$k][0]==$checkmoney){
                $checkcrd = $groupdata['scredit'][$k];
                $checkcrd = $_G['setting']['extcredits'][$checkcrd[1]]['title'] ? $checkcrd : '';
                break;
            }
        }
        if($checkmoney){
            if($checkcrd){
                $ret['check'] = 1;
                $ret['name'] = $groupdata['name'][0];
                $ret['crd'] = $checkcrd;
                return $ret;
            }else {
                $ret['check'] = 1;
                $ret['name'] = $groupdata['name'][0];
                return $ret;
            }
        }
    }
}
function plus_minus_conversion($number = 0){
    return $number > 0 ? -1 * $number : abs($number);
}
function acountorderlog(){
    $today = strtotime(date("Y-m-d"),time());
    $yesterday = $today - 86400;
    $w=date('w')? date('w'):7;
    $beginWeek = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y'));
    $beforebeginWeek = $beginWeek - 604800;
    $beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
    $begin_monthtime = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
    $end_monthtime = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
    
    $acountarr = array(
    'jrcjcount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $today")),
    'zrcjcount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $yesterday AND time < $today")),
    'bzcjcount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $beginWeek")),
    'szcjcount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $beforebeginWeek AND time < $beginWeek")),
    'bycjcount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $beginThismonth")),
    'sycjcount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $begin_monthtime AND time < $end_monthtime")),
    
    'jrcjmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $today")),
    'zrcjmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $yesterday AND time < $today")),
    'bzcjmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $beginWeek")),
    'szcjmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $beforebeginWeek AND time < $beginWeek")),
    'bycjmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $beginThismonth")),
    'sycjmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1 AND time > $begin_monthtime AND time < $end_monthtime")),
    
    'cgzscount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1")),
    'wzfzscount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 0")),
    'allzscount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null")),
    
    'cgzsmoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_orderlog')." WHERE orderid is not null AND state = 1")),
    );
    return $acountarr;
}
function acountgrporder(){
    $today = strtotime(date("Y-m-d"),time());
    $yesterday = $today - 86400;
    $w=date('w')? date('w'):7;
    $beginWeek = mktime(0,0,0,date('m'),date('d')-$w+1,date('Y'));
    $beforebeginWeek = $beginWeek - 604800;
    $beginThismonth = mktime(0,0,0,date('m'),1,date('Y'));
    $begin_monthtime = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
    $end_monthtime = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
    
    $acountarr = array(
    'jrhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1 AND time > $today")),
    'zrhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1 AND time > $yesterday AND time < $today")),
    'bzhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1 AND time > $beginWeek")),
    'szhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1 AND time > $beforebeginWeek AND time < $beginWeek")),
    'byhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1 AND time > $beginThismonth")),
    'syhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1 AND time > $begin_monthtime AND time < $end_monthtime")),
    
    'jrhymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1 AND time > $today")),
    'zrhymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1 AND time > $yesterday AND time < $today")),
    'bzhymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1 AND time > $beginWeek")),
    'szhymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1 AND time > $beforebeginWeek AND time < $beginWeek")),
    'byhymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1 AND time > $beginThismonth")),
    'syhymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1 AND time > $begin_monthtime AND time < $end_monthtime")),
    
    'cghycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 1")),
    'wzfhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND state = 0")),
    'allhycount' => DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null")),
    
    'cghymoney' => DB::result(DB::query("SELECT SUM(money) FROM ".DB::table('hax_rechargedmf_grporder')." WHERE orderid is not null AND type != 4 AND state = 1")),
    );
    return $acountarr;
}
?>