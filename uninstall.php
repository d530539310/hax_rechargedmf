<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$hax_rechargedmf_orderlog= DB::table("hax_rechargedmf_orderlog");
$hax_rechargedmf_credit= DB::table("hax_rechargedmf_credit");
$hax_rechargedmf_grporder= DB::table("hax_rechargedmf_grporder");
$hax_rechargedmf_group= DB::table("hax_rechargedmf_group");
$hax_rechargedmf_paysetup= DB::table("hax_rechargedmf_paysetup");
$sql = <<<EOF
DROP TABLE IF EXISTS `$hax_rechargedmf_orderlog`;
DROP TABLE IF EXISTS `$hax_rechargedmf_credit`;
DROP TABLE IF EXISTS `$hax_rechargedmf_grporder`;
DROP TABLE IF EXISTS `$hax_rechargedmf_group`;
DROP TABLE IF EXISTS `$hax_rechargedmf_paysetup`;
EOF;

runquery($sql);
C::t('common_syscache')->delete('hax_rechargedmf_credit');
C::t('common_syscache')->delete('hax_rechargedmf_group');
C::t('common_syscache')->delete('hax_rechargedmf_paysetup');
$finish = true;
?>