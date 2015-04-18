<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

// remove the database table
$dict = NewDataDictionary($db);
$sqlarray = $dict->DropTableSQL(cms_db_prefix()."module_seotools");
$dict->ExecuteSQLArray($sqlarray);

// remove the permissions
$this->RemovePermission('Edit SEO Settings');
$this->RemovePermission('Edit page descriptions');

// remove all preferences for this module
$this->RemovePreference();

$this->RemoveEventHandler('Core','ContentEditPost');
$this->RemoveEventHandler('Core','ContentDeletePost');

?>
