<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Setup and display template for editing a page's keywords

if (isset($_POST['cancel'])) {
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));
}

$pre = cms_db_prefix();
if (isset($_POST['set_keywords'])) {
	if ($_POST['keywords'] != '') {
		//non-database-specific 'UPSERT' equivalent (no elegant, atomic way to do this?)
		if (preg_match ('/'.$db->dbtype.'/i', 'mysql')) {
			$query = "INSERT INTO ".$pre."module_seotools (content_id, keywords) VALUES (?,?)
 				ON DUPLICATE KEY UPDATE keywords=?";
			$parms = array((int)$_POST['content_id'],$_POST['keywords'],$_POST['keywords']);
		}
		else {
			$query = "SELECT content_id FROM ".$pre."module_seotools WHERE content_id=?";
			$res = $db->GetOne($query,array($id));
			if ($res) {
				$query = "UPDATE ".$pre."module_seotools SET keywords=? WHERE content_id=?";
				$parms = array($_POST['keywords'],(int)$_POST['content_id']);
			}
			else {
				$query = "INSERT INTO ".$pre."module_seotools (content_id, keywords) VALUES (?,?)";
				$parms = array((int)$_POST['content_id'],$_POST['keywords']);
			}
		}
		$db->Execute($query, $parms);
	}
	else {
		$query = "UPDATE ".$pre."module_seotools SET keywords=NULL WHERE content_id=?";
		$db->Execute($query,array((int)$_POST['content_id']));
	}
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));
}

$query = "SELECT content_name FROM ".$pre."content WHERE content_id=?";
$name = $db->GetOne($query,array($_GET['content_id']));
if ($name == FALSE) {
	$name = '?';
}
$query = "SELECT keywords FROM ".$pre."module_seotools WHERE content_id=?";
$kw = $db->GetOne($query,array($_GET['content_id']));
if ($kw == FALSE) {
	$funcs = new SEO_keyword();
	$kw = strtolower(implode(' ',array_flip($funcs->getKeywordSuggestions($_GET['content_id'],$this))));
}

$smarty->assign('startform',$this->CreateFormStart(null, 'edit_keywords'));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('hidden', $this->CreateInputHidden(null, 'content_id', $_GET['content_id']));
$smarty->assign('prompt_kwords',$this->Lang('enter_new_keywords',$name));
$smarty->assign('input_kwords',
	$this->CreateTextArea(FALSE, null, $kw, 'keywords', '', '', '', '', '60', '1','','','style="height:5em;"')
	.'<br />'.$this->Lang('help_new_keywords'));
$smarty->assign('submit',
	$this->CreateInputSubmit(null, 'set_keywords', $this->Lang('save')));
$smarty->assign('cancel',
	$this->CreateInputSubmit(null, 'cancel', $this->Lang('cancel')));

echo $this->ProcessTemplate('keywordedit.tpl');

?>
