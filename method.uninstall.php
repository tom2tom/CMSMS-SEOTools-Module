<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2012-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

// remove database tables
$dict = NewDataDictionary($db);
$pre = cms_db_prefix();
$sqlarray = $dict->DropTableSQL($pre.'module_seotools');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropTableSQL($pre.'module_seotools_group');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropIndexSQL('idx_seogrps', $pre.'module_seotools_meta');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->DropTableSQL($pre.'module_seotools_meta');
$dict->ExecuteSQLArray($sqlarray);

// remove permissions
$this->RemovePermission('Edit SEO Settings');
$this->RemovePermission('Edit page descriptions');

// remove all preferences for this module
$this->RemovePreference();

// remove event handlers
$this->RemoveEventHandler('Core','ContentEditPost');
$this->RemoveEventHandler('Core','ContentDeletePost');

?>
