<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_hax_rechargedmf_home extends plugin_hax_rechargedmf{
    function spacecp_credit_top_output() {
	    global $_G;
		$dzczbuy = $_G['cache']['plugin']['hax_rechargedmf']['dzczbuy'];
		$crd_switch = $_G['cache']['plugin']['hax_rechargedmf']['crd_switch'];
		if($_GET['mod']=='spacecp' && $_GET['ac']=='credit' && $_GET['op']=='buy' && !$_GET['jump'] && $dzczbuy && $crd_switch){
			dheader('location: plugin.php?id=hax_rechargedmf:czhook');
		}
	}
	function spacecp_usergroup_top_output() {
		global $_G;
		$grp_dzbuy = $_G['cache']['plugin']['hax_rechargedmf']['grp_dzbuy'];
		$grp_switch = $_G['cache']['plugin']['hax_rechargedmf']['grp_switch'];
		if($_GET['mod']=='spacecp' && $_GET['ac']=='usergroup' && $_GET['do']=='list' && !$_GET['jump'] && $grp_dzbuy && $grp_switch){
			dheader('location: plugin.php?id=hax_rechargedmf:czhook&mod=vip');
		}
	}
}

class plugin_hax_rechargedmf {
	function global_header(){
		global $_G;
		$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
		if($_G['uid'] && $hax_rechargedmf['grp_overauto'] && $_GET['do']=='expiry'){
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
        	
        	if(!empty($expgrouparray) && in_array($_G['groupid'], $expgrouparray)){
        	    $extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
        	    if($extgroupids){
        	        require_once("alipay/vip_function.func.php");
        	        $ret=switchgroup(min($extgroupids),$_G['uid']);
        	        if($ret['state']){
        	            echo '<script>window.onload=function(){showDialog("'.lang('plugin/hax_rechargedmf', 'slang49').'", "confirm", "'.lang('plugin/hax_rechargedmf', 'slang50').'", "parent.location.href=\"plugin.php?id=hax_rechargedmf:czhook&mod=vip\"",1);};</script>';
        	        }
        	    }
        	}
		}
	}
}

?>