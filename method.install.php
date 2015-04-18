<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

// mysql-specific, but ignored by other database
$taboptarray = array('mysql' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci',
	'mysqli' => 'ENGINE MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci');
$dict = NewDataDictionary($db);

// table schema description
$flds = "
content_id I KEY,
indexable I(1) NOTNULL DEFAULT 1,
keywords C(255),
priority I(4),
ogtype C(32),
ignored X
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix().'module_seotools',$flds,$taboptarray);
$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,FALSE) == 2) : FALSE;
if(!$result)
	return $this->Lang('install_database_error');

// permissions
$this->CreatePermission('Edit SEO Settings',$this->Lang('perm_editsettings'));
$this->CreatePermission('Edit page descriptions',$this->Lang('perm_editdescription'));

// preferences
$this->SetPreference('content_type','html');
$this->SetPreference('create_robots',1);
$this->SetPreference('create_sitemap',1);
$this->SetPreference('push_sitemap',0);
$this->SetPreference('default_keywords','');
$this->SetPreference('description_auto','This page covers the topics {keywords}');
$this->SetPreference('description_auto_generate',0);
$this->SetPreference('description_block','metadescription');
$this->SetPreference('keyword_block','metakeywords');
$this->SetPreference('keyword_content_weight',1);
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
$this->SetPreference('keyword_separator',' ');
$this->SetPreference('keyword_title_weight',6);
$this->SetPreference('additional_meta_tags','');
$this->SetPreference('meta_contributor','');
$this->SetPreference('meta_copyright','(C). All rights reserved.');
$this->SetPreference('meta_dublincore',0);
$this->SetPreference('meta_latitude','');
$this->SetPreference('meta_location','');
$this->SetPreference('meta_longitude','');
$this->SetPreference('meta_opengraph_admins','');
$this->SetPreference('meta_opengraph_application','');
$this->SetPreference('meta_opengraph',0);
$this->SetPreference('meta_opengraph_image','');
$this->SetPreference('meta_opengraph_sitename',cmsms()->siteprefs['sitename']);
$this->SetPreference('meta_opengraph_title','{title}');
$this->SetPreference('meta_opengraph_type','');
$this->SetPreference('meta_publisher','');
$this->SetPreference('meta_region','');
$this->SetPreference('meta_standard',1);
$this->SetPreference('meta_title','{title} | {$sitename}');
$this->SetPreference('title','{title} | {$sitename} - {$title_keywords}');
$this->SetPreference('verification','');

?>
