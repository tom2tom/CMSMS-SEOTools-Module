<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Set or clear indexable status for all selected items

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin');
elseif (isset($_POST['index_selected']))
	$indx = 1;
elseif (isset($_POST['unindex_selected']))
	$indx = 0;
else
	$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions')); //should never happen

$pre = cms_db_prefix();
$query = 'UPDATE '.$pre.'module_seotools SET indexable='.$indx.' WHERE content_id=?';
$query2 = 'INSERT INTO '.$pre.
'module_seotools (content_id, indexable) SELECT ?,'.$indx.' FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS (SELECT 1 FROM '.
$pre.'module_seotools T WHERE T.content_id=?)';

$work = $_POST['indxsel'];
foreach ($work as $cid) {
	//upsert, sort-of
	$db->Execute($query, array($cid));
	$db->Execute($query2, array($cid, $cid));
}

$this->Redirect($id, 'defaultadmin', '', array('tab'=>'pagedescriptions'));

?>
