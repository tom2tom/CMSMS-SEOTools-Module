<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Setup and display template for editing a page's keywords

if (isset($_POST['cancel'])) {
	$this->Redirect($id,'defaultadmin','',array('tab'=>'pagedescriptions'));
}

$pre = cms_db_prefix();
if (isset($_POST['set_keywords'])) {
	$kw = trim($_POST['keywords']);
	$cid = (int)$_POST['content_id'];
	if ($kw) {
		//upsert, sort-of
		$query = 'UPDATE '.$pre.'module_seotools SET keywords=? WHERE content_id=?';
		$query2 = 'INSERT INTO '.$pre.
'module_seotools (content_id, keywords) SELECT ?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS (SELECT 1 FROM '.
		$pre.'module_seotools T WHERE T.content_id=?)';
		$db->Execute($query, array($kw, $cid));
		$db->Execute($query2, array($cid, $kw, $cid));
	}
	else {
		$query = 'UPDATE '.$pre.'module_seotools SET keywords=NULL WHERE content_id=?';
		$db->Execute($query, array($cid));
	}
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));
}

$query = 'SELECT content_name FROM '.$pre.'content WHERE content_id=?';
$name = $db->GetOne($query,array($_GET['content_id']));
if ($name == false) {
	$name = '?';
}
$query = 'SELECT keywords FROM '.$pre.'module_seotools WHERE content_id=?';
$kw = $db->GetOne($query,array($_GET['content_id']));
if ($kw == false) {
	$funcs = new SEO_keyword();
	$sep = $this->GetPreference('keyword_separator',' ');
	//TODO don't assume ascii encoded >> iconv() ? OR mb_convert_case( ,MB_CASE_LOWER,$encoding)
	$kw = strtolower(implode($sep,$funcs->getKeywords($this,$_GET['content_id'])));
}

$tplvars = array(
	'startform' => $this->CreateFormStart(null,'edit_keywords'),
	'endform' => $this->CreateFormEnd(),
	'hidden' => $this->CreateInputHidden(null,'content_id',$_GET['content_id']),
	'prompt_kwords' => $this->Lang('enter_new_keywords',$name),
	'input_kwords' =>
		$this->CreateTextArea(false,null,$kw,'keywords','','','','','60','1','','','style="height:5em;"')
		.'<br />'.$this->Lang('help_new_keywords'),
	'submit' => $this->CreateInputSubmit(null,'set_keywords',$this->Lang('save')),
	'cancel' => $this->CreateInputSubmit(null,'cancel',$this->Lang('cancel'))
);

SEO_utils::ProcessTemplate($this,'keywordedit.tpl',$tplvars);

?>
