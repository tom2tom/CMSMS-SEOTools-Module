<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2015-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Display and optionally revert metadata settings

if (!$this->CheckAccess('Edit SEO Settings'))
	return SEO_utils::DisplayErrorPage($this->Lang('accessdenied'));

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'metasettings'));

if (isset($_POST['revertmeta'])) {
	// get default metadata
	require ('method.setmeta.php');

	$pre = cms_db_prefix();
	$db->Execute('DELETE FROM '.$pre.'module_seotools_meta');

	$gid = -1; //unmatched
	$query = 'INSERT INTO '.$pre.'module_seotools_meta
(group_id,mname,value,output,calc,smarty,vieworder,active)
VALUES (?,?,?,?,?,?,?,?)';
	foreach ($defs as $name=>$data) {
		if ($gid != $data['gid']) {
			$gid = $data['gid'];
			$i = 1;
		}
		else {
			++$i;
		}
		$db->Execute($query,array(
			$data['gid'],
			$name,
			$data['value'],
			$data['output'],
			$data['calc'],
			$data['smarty'],
			$i,
			$data['active']));
	}

	$db->Execute('UPDATE '.$pre.'module_seotools_group SET active=0');
	$db->Execute('UPDATE '.$pre.'module_seotools_group SET active=1 WHERE gname IN (\'before\',\'after\',\'meta_std\')');
	$back = false;

	$this->Audit(0, $this->Lang('friendlyname'), 'Applied default META settings');
}
else {
	$back = true;
}

$tplvars = array(
	'startform' => $this->CreateFormStart(null,'meta'),
	'endform' => $this->CreateFormEnd(),
	'title' => $this->Lang('meta_title')
);
//generate current metadata
ob_start();
include 'action.default.php';
$contents = ob_get_clean();
$tplvars['content'] = nl2br(htmlentities($contents));
if ($back) {
	$tplvars['submitbtn'] = $this->CreateInputSubmit(null,'revertmeta',$this->lang('revert'),
		'onclick="return confirm(\''.$this->Lang('confirm').'\');"');
}
$tplvars['cancelbtn'] = $this->CreateInputSubmit(null,'cancel',$this->lang('close'));

echo SEO_utils::ProcessTemplate($this,'display.tpl',$tplvars);

?>
