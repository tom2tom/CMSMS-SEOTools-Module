<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2012-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

// mysql-specific, but ignored by other database
$taboptarray = array('mysql' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci',
	'mysqli' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci');
$dict = NewDataDictionary($db);
$pre = cms_db_prefix();

// main table schema
$flds = "
content_id I KEY,
indexable I(1) NOTNULL DEFAULT 1,
keywords C(255),
priority I(4),
ogtype C(32),
ignored X
";
$sqlarray = $dict->CreateTableSQL($pre.'module_seotools',$flds,$taboptarray);
$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
if (!$result)
	return $this->Lang('install_database_error');

// meta-groups table schema
$flds = "
group_id I(2) AUTO KEY,
gname C(64),
vieworder I(2),
active I(1) NOTNULL DEFAULT 1
";
$sqlarray = $dict->CreateTableSQL($pre.'module_seotools_group',$flds,$taboptarray);
$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
if (!$result)
	return $this->Lang('install_database_error');
// add default groups
$sql = 'INSERT INTO '.$pre.'module_seotools_group (gname,vieworder,active) VALUES (?,?,?)';
$i = 1;
foreach (array(
'before'=>1, //1
'meta_std'=>1, //2
'meta_dc'=>0, //3
'meta_og'=>0, //4
'meta_twt'=>0, //5
'meta_gplus'=>0, //6
'after'=>1 //7
) as $name=>$act) {
	$db->Execute($sql,array($name,$i,$act));
	$i++;
}

// meta table schema
$flds = "
meta_id I(2) AUTO KEY,
group_id I(2),
mname C(128),
value C(512),
output C(255),
calc I(1) NOTNULL DEFAULT 0,
smarty I(1) NOTNULL DEFAULT 0,
vieworder I(2),
active I(1) NOTNULL DEFAULT 1
";
$sqlarray = $dict->CreateTableSQL($pre.'module_seotools_meta',$flds,$taboptarray);
$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
if (!$result)
	return $this->Lang('install_database_error');
// as fast as possible
$sqlarray = $dict->CreateIndexSQL('idx_seogrps', $pre.'module_seotools_meta', 'group_id');
$dict->ExecuteSQLArray($sqlarray);	

// table default metadata
require ('method.setmeta.php'); 

// permissions
$this->CreatePermission('Edit SEO Settings',$this->Lang('perm_editsettings'));
$this->CreatePermission('Edit page descriptions',$this->Lang('perm_editdescription'));

// preferences
$this->SetPreference('create_robots',1);
$this->SetPreference('create_sitemap',1);
$this->SetPreference('push_sitemap',0);
$this->SetPreference('description_auto','This page covers the topics {keywords}');
$this->SetPreference('description_auto_generate',0);
$this->SetPreference('description_block','metadescription');
$this->SetPreference('keyword_block','metakeywords');
$this->SetPreference('keyword_content_weight',1);
$this->SetPreference('keyword_default','');
$this->SetPreference('keyword_description_weight',4);
$words = <<< EOS
i,me,my,myself,we,our,ours,ourselves,you,your,yours,yourself,yourselves,
he,him,his,himself,she,her,hers,herself,it,its,itself,they,them,their,theirs,
themselves,what,which,who,whom,this,that,these,those,am,is,are,was,were,be,been,being,
have,has,had,having,do,does,did,doing,a,an,the,and,but,if,or,because,as,until,while,
of,at,by,for,with,about,against,between,into,through,during,before,after,above,below,
to,from,up,down,in,out,on,off,over,under,again,further,then,once,here,there,
when,where,why,how,all,any,both,each,few,more,most,other,some,such,no,nor,not,only,
own,same,so,than,too,very,lorem
EOS;
$this->SetPreference('keyword_exclude',trim($words));
$this->SetPreference('keyword_headline_weight',2);
$this->SetPreference('keyword_minimum_weight',7);
$this->SetPreference('keyword_minlength',6);
$this->SetPreference('keyword_separator',',');
$this->SetPreference('keyword_title_weight',6);
$this->SetPreference('robot_start','');
$this->SetPreference('robot_end','');

?>
