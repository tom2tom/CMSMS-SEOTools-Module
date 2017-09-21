<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2017 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

function rmdir_recursive($dir)
{
	foreach(scandir($dir) as $file) {
		if (!($file === '.' || $file === '..')) {
			$fp = $dir.DIRECTORY_SEPARATOR.$file;
			if (is_dir($fp)) {
				rmdir_recursive($fp);
			} else {
 				@unlink($fp);
			}
		}
	}
	rmdir($dir);
}

$dict = NewDataDictionary($db);
$pre = cms_db_prefix();

switch ($oldversion) {
 case '1.0':
 case '1.1':
 case '1.2':
	//non-unique index
	$tbl = $pre.'module_seotools';
	//standard field sizes (e.g. for postgresql)
	$sql = $dict->AlterColumnSQL($tbl, 'indexable L NOTNULL DEFAULT 1, priority I(4)');
	$dict->ExecuteSQLArray($sql);
	//support ignored problems
	$sql = $dict->AddColumnSQL($tbl, 'ignored X');
	$dict->ExecuteSQLArray($sql);
	//conform to fixed preference name
	$str = $this->GetPreference('title_meta', '{title} | {$sitename}');
	$this->SetPreference('meta_title', $str);
	$this->DropPreference('title_meta');
	//convert bool preference formats
	$val = ($this->GetPreference('create_robots', 'false') == 'false') ? 0:1;
	$this->SetPreference('create_robots', $val);
	$val = ($this->GetPreference('create_sitemap', 'false') == 'false') ? 0:1;
	$this->SetPreference('create_sitemap', $val);
	$val = ($this->GetPreference('push_sitemap', 'false') == 'false') ? 0:1;
	$this->SetPreference('push_sitemap', $val);
	$val = ($this->GetPreference('description_auto_generate','false') == 'false') ? 0:1;
	$this->SetPreference('description_auto_generate', $val);
	$val = ($this->GetPreference('meta_dublincore', 'false') == 'false') ? 0:1;
	$this->SetPreference('meta_dublincore', $val);
	$val = ($this->GetPreference('meta_opengraph', 'false') == 'false') ? 0:1;
	$this->SetPreference('meta_opengraph', $val);
	$val = ($this->GetPreference('meta_standard', 'false') == 'false') ? 0:1;
	$this->SetPreference('meta_standard', $val);
	//extra preferences
	$this->SetPreference('content_type', 'html');
	$this->SetPreference('keyword_separator' ,' ');
 case '1.5':
	//redundant files
	$fp = cms_join_path(dirname(__FILE__),'changelog.htm');
	if (is_file($fp))
		unlink($fp);
	$fp = cms_join_path(dirname(__FILE__),'admin-header.html');
	if (is_file($fp))
		unlink($fp);
	$fp = cms_join_path(dirname(__FILE__),'lib','doc','module-admin.css');
	if (is_file($fp))
		unlink($fp);
	//extra preferences
	$this->SetPreference('keyword_block','metakeywords');
	$this->SetPreference('robot_start','');
	$this->SetPreference('robot_end','');
	//modify preference
	$words = <<<EOS
i,me,my,myself,we,our,ours,ourselves,you,your,yours,yourself,yourselves,
he,him,his,himself,she,her,hers,herself,it,its,itself,they,them,their,theirs,
themselves,what,which,who,whom,this,that,these,those,am,is,are,was,were,be,been,being,
have,has,had,having,do,does,did,doing,a,an,the,and,but,if,or,because,as,until,while,
of,at,by,for,with,about,against,between,into,through,during,before,after,above,below,
to,from,up,down,in,out,on,off,over,under,again,further,then,once,here,there,
when,where,why,how,all,any,both,each,few,more,most,other,some,such,no,nor,not,only,
own,same,so,than,too,very,lorem
EOS;
	$sep = $this->GetPreference('keyword_separator',' ');
	$now = trim($this->GetPreference('keyword_exclude'));
	if ($now) {
		$old = explode($sep,$now);
		$new = explode(',',trim($words));
		$new = array_unique($old+$new,SORT_STRING);
		$words = implode($sep,$new);
	} elseif ($sep != ',') {
		$words = str_replace(',',$sep,$words);
	}
	$this->SetPreference('keyword_exclude',$words);
 case '1.6':
	// renamed pref
	$val = $this->GetPreference('default_keywords','');
	$this->SetPreference('keyword_default',$val);
	$this->RemovePreference('default_keywords');
	// meta-groups table schema
	$flds = '
group_id I(2) AUTO KEY,
gname C(64),
vieworder I(2),
active I(1) NOTNULL DEFAULT 1
';
	$sqlarray = $dict->CreateTableSQL($pre.'module_seotools_group',$flds,$taboptarray);
	$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
	if (!$result) return false;

	$data = array();
	// conform to current values
	foreach (array(
		'meta_standard'=>'meta_std',
		'meta_dublincore'=>'meta_dc',
		'meta_opengraph'=>'meta_og'
	) as $old=>$new) {
		$val = $this->GetPreference($old, '_~^');
		$data[$new] = ($val && ($val != '_~^')) ? 1:0;
		$this->RemovePreference($old);
	}

	// add default groups
	$sql = 'INSERT INTO '.$pre.'module_seotools_group (gname,vieworder,active) VALUES (?,?,?)';
	$i = 1;
	foreach (array(
		'before'=>1, //1
		'meta_std'=>$data['meta_std'], //2
		'meta_dc'=>$data['meta_dc'], //3
		'meta_og'=>$data['meta_og'], //4
		'meta_twt'=>0, //5
		'meta_gplus'=>0, //6
		'after'=>1 //7
	) as $name=>$act) {
		$db->Execute($sql,array($name,$i,$act));
		++$i;
	}
	// meta table schema
	$flds = '
meta_id I(2) AUTO KEY,
group_id I(2),
mname C(128),
value C(512),
output C(255),
calc I(1) NOTNULL DEFAULT 0,
smarty I(1) NOTNULL DEFAULT 0,
vieworder I(2),
active I(1) NOTNULL DEFAULT 1
';
	$sqlarray = $dict->CreateTableSQL($pre.'module_seotools_meta',$flds,$taboptarray);
	$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
	if (!$result) return false;
	// extra index
	$sqlarray = $dict->CreateIndexSQL('idx_seogrps', $pre.'module_seotools_meta', 'group_id');
	$dict->ExecuteSQLArray($sqlarray);

	// get default metadata
	require ('method.setmeta.php');

	foreach (array(
		'content_type'=>'content_type',
		'additional_meta_tags'=>'meta_additional',
		'meta_contributor'=>'meta_std_contributor',
		'meta_copyright'=>'meta_std_copyright',
		'meta_latitude'=>'meta_std_latitude',
		'meta_location'=>'meta_std_location',
		'meta_longitude'=>'meta_std_longitude',
		'meta_opengraph_image'=>'meta_og_image',
		'meta_opengraph_sitename'=>'meta_og_sitename',
		'meta_opengraph_title'=>'meta_og_title',
		'meta_opengraph_type'=>'meta_og_type',
		'meta_publisher'=>'meta_std_publisher',
		'meta_region'=>'meta_std_region',
		'meta_title'=>'meta_std_title',
		'verification'=>'verification'
	) as $old=>$new) {
		if ($defs[$new]['value'] != 'UNUSED') {
			$val = $this->GetPreference($old, '_~^');
			if ($val != '_~^') {
				$defs[$new]['value'] = $val;
			}
		}
		$this->RemovePreference($old);
	}

	$gid = -1; //unmatched
	$sql = 'INSERT INTO '.$pre.'module_seotools_meta
(group_id,mname,value,output,calc,smarty,vieworder,active)
VALUES (?,?,?,?,?,?,?,?)';

	foreach ($defs as $name=>$data) {
		if ($gid != $data['gid']) {
			$gid = $data['gid'];
			$i = 1;
		} else {
			++$i;
		}
		$db->Execute($sql,array(
			$data['gid'],
			$name,
			$data['value'],
			$data['output'],
			$data['calc'],
			$data['smarty'],
			$i,
			$data['active']));
	}
 case '1.7':
 case '1.7.1':
	//redundant file
	$fp = cms_join_path(dirname(__FILE__), 'templates', 'robot.tpl');
	if (is_file($fp)) {
		@unlink($fp);
	}
	//redundant directory
	$fp = cms_join_path(dirname(__FILE__), 'include');
	if (is_dir($fp)) {
		rmdir_recursive($fp);
	}
}
