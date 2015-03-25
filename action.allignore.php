<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Set or clear ignored status for all selected items

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin');
elseif (isset($_POST['ignore_selected']))
	$intable = TRUE;
elseif (isset($_POST['unignore_selected']))
	$intable = FALSE;
else
	$this->Redirect($id, 'defaultadmin'); //should never happen

if (isset($_POST['urgentsel'])) {
	$work = $_POST['urgentsel'];
	$tab = 'urgentfixes';
} elseif (isset($_POST['importantsel'])) {
	$work = $_POST['importantsel'];
	$tab = 'importantfixes';
} else {
	$this->Redirect($id, 'defaultadmin'); //should never happen
}

$pre = cms_db_prefix();
foreach ($work as $value) {
	//TODO handle non-page-specific problems
	$pages = explode ('@',$value);
	unset ($pages[0]);
	foreach ($pages as $sig) {
		list ($id,$ignored) = explode ('-', $sig);
		$id = (int)$id;
		$query = "SELECT content_id,ignored FROM ".$pre."module_seotools WHERE content_id=?";
		$res = $db->GetRow($query,array($id));
		if ($res) {
			if ($res['ignored']) {
				$parms = array();
				$codes = explode(',',$res['ignored']);
				if ($intable) {
					$codes[] = $ignored;
				} else {
					foreach ($codes as $i => $name) {
					  if($name == $ignored) unset($codes[$i]);
					}
				}
				if ($codes) {
					$query = "UPDATE ".$pre."module_seotools SET ignored=? WHERE content_id=?";
					$parms[] = implode(',',$codes);
				} else {
					$query = "UPDATE ".$pre."module_seotools SET ignored=NULL WHERE content_id=?";
				}
			} elseif ($intable) {
				$query = "UPDATE ".$pre."module_seotools SET ignored=? WHERE content_id=?";
				$parms[] = $ignored;
			}
			$parms[] = $id;
			$db->Execute($query,$parms);
			unset ($parms);
		} else if ($intable) {
			$query = "INSERT INTO ".$pre."module_seotools (content_id,ignored) VALUES (?,?)";
			$db->Execute($query, array($id,$ignored));
		}
	}
}

$this->Redirect($id, 'defaultadmin', '', array('tab'=>$tab));

?>
