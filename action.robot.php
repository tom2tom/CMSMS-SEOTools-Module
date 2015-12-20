<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Display and optionally regenerate robots.txt file

if (!$this->CheckAccess('Edit SEO Settings'))
	return $this->DisplayErrorPage($this->Lang('accessdenied'));

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'sitemapsettings'));

// Get robots file in root directory (whereever that actually is)
$offs = strpos(__FILE__, 'modules'.DIRECTORY_SEPARATOR.$this->GetName());
$fn = substr(__FILE__, 0, $offs).'robots.txt';
if (!is_readable($fn)) {
	$this->Redirect ($id, 'defaultadmin', '',
	array('warning'=>1,'message'=>'TODO','tab'=>'sitemapsettings'));
}

if (isset($_POST['newrobotfile'])) {
	if (!(is_file($fn) && is_writable($fn))) {
		$this->Redirect ($id, 'defaultadmin', '',
		array('warning'=>1,'message'=>'robots_not_writeable','tab'=>'sitemapsettings'));
	}
	// Recreate
	$funcs = new SEO_robot();
	if (!$funcs->createRobotsTXT($this)) {
		$this->Redirect($id, 'defaultadmin', '',
		array('warning'=>1,'message'=>'error','tab'=>'sitemapsettings'));
	}
	$back = false;
}
else {
	$back = true;
}

$smarty->assign('startform', $this->CreateFormStart(null, 'robot'));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('title', $this->Lang('robots_title'));
$fp = @fopen($fn, 'rb');
$contents = @fread($fp, filesize($fn));
@fclose($fp);
$smarty->assign('content', nl2br($contents));
if ($back) {
	$smarty->assign('submitbtn',$this->CreateInputSubmit(null, 'newrobotfile', $this->lang('regenerate'),
		'onclick="return confirm(\''.$this->Lang('confirm').'\');"'));
}
$smarty->assign('cancelbtn', $this->CreateInputSubmit(null, 'cancel', $this->lang('close')));

echo $this->ProcessTemplate('display.tpl');

?>
