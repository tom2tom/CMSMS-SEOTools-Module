<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

#Setup and display template for editing a page's opengraph type

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin','',array('tab'=>'pagedescriptions'));

$pre = cms_db_prefix();

if (isset($_POST['set_ogtype'])) {
	$cid = (int)$_POST['content_id'];
	if ($_POST['og_type'] != '') {
		//upsert, sort-of
		$query = 'UPDATE '.$pre.'module_seotools SET ogtype=? WHERE content_id=?';
		$query2 = 'INSERT INTO '.$pre.
'module_seotools (content_id, ogtype) SELECT ?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS (SELECT 1 FROM '.
		$pre.'module_seotools T WHERE T.content_id=?)';
		$db->Execute($query, array($_POST['og_type'], $cid));
		$db->Execute($query2, array($cid, $_POST['og_type'], $cid));
	}
	else {
		$query = 'UPDATE '.$pre.'module_seotools SET ogtype=NULL WHERE content_id=?';
		$db->Execute($query,array($cid));
	}
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));
}

$query = 'SELECT content_name FROM '.$pre.'content WHERE content_id=?';
$name = $db->GetOne($query,array($_GET['content_id']));
if ($name == FALSE) {
	$name = '?';
}
$query = 'SELECT ogtype FROM '.$pre.'module_seotools WHERE content_id=?';
$og = $db->GetOne($query,array($_GET['content_id']));
if ($og == FALSE) {
	$og = '';
}

$tplvars = array(
	'startform' => $this->CreateFormStart(null,'edit_ogtype'),
	'endform' => $this->CreateFormEnd(),
	'hidden' => $this->CreateInputHidden(null,'content_id',$_GET['content_id']),
	'prompt_ogtype' => $this->Lang('enter_new_ogtype',$name),
	'input_ogtype' => $this->CreateInputText(null,'og_type',$og,32,32).'<br />'
		.$this->Lang('help_new_ogtype').'<br />'
		.$this->Lang('help_opengraph'),
	'submit' => $this->CreateInputSubmit(null,'set_ogtype',$this->Lang('save')),
	'cancel' => $this->CreateInputSubmit(null,'cancel',$this->Lang('cancel'))
);

SEO_utils::ProcessTemplate($this,'ogedit.tpl',$tplvars);

?>
