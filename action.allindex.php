<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Set or clear indexable status for all selected items

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin');
elseif (isset($_POST['index_selected']))
	$indx = TRUE;
elseif (isset($_POST['unindex_selected']))
	$indx = FALSE;
else
	$this->Redirect($id, 'defaultadmin'); //should never happen

$pre = cms_db_prefix();
$work = $_POST['indxsel'];
foreach ($work as $value)
{
	$parms = array();
    $query = "SELECT indexable FROM ".$pre."module_seotools WHERE content_id=?";
    if ($db->GetOne($query,array($value) === FALSE)
	{
		if ($indx)
		{
			unset ($parms);
			continue;
		}
		else
			$query = "INSERT INTO ".$pre."module_seotools SET content_id=?, indexable=0";
	}
	else
	{
		$query = "UPDATE ".$pre."module_seotools SET indexable=? WHERE content_id=?";
		if ($indx)
			$parms[] = 1;
		else
			$parms[] = 0;
	}
	$parms[] = $value;
	$db->Execute($query,$parms);
	unset ($parms);
}

$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));

?>
