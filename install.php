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
CREATE TABLE `$hax_rechargedmf_orderlog` (
  `orderid` varchar(64) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `usname` varchar(255) NOT NULL,
  `money` decimal(12,2) NOT NULL,
  `credit` int(50) unsigned NOT NULL,
  `credit2`	varchar(68) NOT NULL,
  `credittype` int(10) NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `state` int(10) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `zftime` int(10) unsigned NOT NULL,
  `sn` varchar(80) NOT NULL,
  `payid` varchar(255) NOT NULL,
  PRIMARY KEY  (`orderid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

CREATE TABLE `$hax_rechargedmf_credit` (
  `creditid` int(10) unsigned NOT NULL,
  `descr` varchar(25) NOT NULL,
  `money` varchar(255) NOT NULL,
  `send` varchar(64) NOT NULL,
  `custom` int(10) NOT NULL,
  `bili` varchar(10) NOT NULL,
  `state` int(10) NOT NULL,
  `shunxu` int(10) NOT NULL,
  PRIMARY KEY  (`creditid`)
) ENGINE=MyISAM;

CREATE TABLE `$hax_rechargedmf_grporder` (
  `orderid` varchar(64) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `usname` varchar(255) NOT NULL,
  `money` decimal(12,2) NOT NULL,
  `zfcrd`	varchar(64) NOT NULL,
  `groupid` int(50) unsigned NOT NULL,
  `groupname` varchar(50) NOT NULL,
  `grouptime` int(10) NOT NULL,
  `totime` int(10) NOT NULL,
  `credit`	varchar(64) NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `state` int(10) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `zftime` int(10) unsigned NOT NULL,
  `sn` varchar(80) NOT NULL,
  `payid` varchar(255) NOT NULL,
  PRIMARY KEY  (`orderid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

CREATE TABLE `$hax_rechargedmf_group` (
  `groupid` int(10) unsigned NOT NULL,
  `name` varchar(25) NOT NULL,
  `moneyop` varchar(255) NOT NULL,
  `scredit` varchar(64) NOT NULL,
  `descr` mediumtext,
  `modescr` mediumtext,
  `state` int(10) NOT NULL,
  `shunxu` int(10) NOT NULL,
  `width` int(10) NOT NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=MyISAM;

CREATE TABLE `$hax_rechargedmf_paysetup` (
  `paymethodid` int(10) unsigned NOT NULL,
  `paymentkey01` mediumtext,
  `paymentkey02` mediumtext,
  `paymentkey03` mediumtext,
  `state` int(10) NOT NULL,
  PRIMARY KEY  (`paymethodid`)
) ENGINE=MyISAM;

EOF;
runquery($sql);
$finish = true;
@unlink(DISCUZ_ROOT . './source/plugin/hax_rechargedmf/discuz_plugin_hax_rechargedmf.xml');
@unlink(DISCUZ_ROOT . './source/plugin/hax_rechargedmf/discuz_plugin_hax_rechargedmf_SC_GBK.xml');
@unlink(DISCUZ_ROOT . './source/plugin/hax_rechargedmf/discuz_plugin_hax_rechargedmf_SC_UTF8.xml');
@unlink(DISCUZ_ROOT . './source/plugin/hax_rechargedmf/discuz_plugin_hax_rechargedmf_TC_BIG5.xml');
@unlink(DISCUZ_ROOT . './source/plugin/hax_rechargedmf/discuz_plugin_hax_rechargedmf_TC_UTF8.xml');
@unlink(DISCUZ_ROOT . 'source/plugin/hax_rechargedmf/install.php');
?>