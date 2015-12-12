<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

#Setup and display template for editing a page's opengraph type

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));

$pre = cms_db_prefix();

if (isset($_POST['set_ogtype'])) {
	if ($_POST['og_type'] != "") {
		//non-database-specific 'UPSERT' equivalent is needed
		if (preg_match ('/'.$db->dbtype.'/i', 'mysql')) {
			$query = "INSERT INTO ".$pre."module_seotools (content_id, ogtype) VALUES (?,?) ON DUPLICATE KEY UPDATE ogtype=?";
			$parms = array((int)$_POST['content_id'],$_POST['og_type'],$_POST['og_type']);
		}
		else {
			$query = "SELECT content_id FROM ".$pre."module_seotools WHERE content_id=?";
			$res = $db->GetOne($query,array($id));
			if ($res) {
				$query = "UPDATE ".$pre."module_seotools SET ogtype=? WHERE content_id=?";
				$parms = array($_POST['og_type'],(int)$_POST['content_id']);
			}
			else {
				$query = "INSERT INTO ".$pre."module_seotools (content_id, ogtype) VALUES (?,?)";
				$parms = array((int)$_POST['content_id'],$_POST['og_type']);
			}
		}
		$db->Execute($query, $parms);
	}
	else {
		$query = "UPDATE ".$pre."module_seotools SET ogtype=NULL WHERE content_id=?";
		$db->Execute($query,array((int)$_POST['content_id']));
	}
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));
}

$query = "SELECT content_name FROM ".$pre."content WHERE content_id=?";
$name = $db->GetOne($query,array($_GET['content_id']));
if ($name == FALSE) {
	$name = '?';
}
$query = "SELECT ogtype FROM ".$pre."module_seotools WHERE content_id=?";
$og = $db->GetOne($query,array($_GET['content_id']));
if ($og == FALSE) {
	$og = '';
}

$smarty->assign('startform',$this->CreateFormStart(null, 'edit_ogtype'));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('hidden', $this->CreateInputHidden(null, 'content_id', $_GET['content_id']));
$smarty->assign('prompt_ogtype',$this->Lang('enter_new_ogtype',$name));
$smarty->assign('input_ogtype',
	$this->CreateInputText(null, 'og_type', $og, 32, 32).'<br />'
	 .$this->Lang('help_new_ogtype').'<br />'
	 .$this->Lang('help_opengraph'));
$smarty->assign('submit',
	$this->CreateInputSubmit(null, 'set_ogtype', $this->Lang('save')));
$smarty->assign('cancel',
	$this->CreateInputSubmit(null, 'cancel', $this->Lang('cancel')));

echo $this->ProcessTemplate('ogedit.tpl');

?>
