<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

switch($oldversion)
{
	$dict = NewDataDictionary($db);
	$pre = cms_db_prefix();

	case "1.0":
	case "1.1":
	case "1.2":
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
	case "1.5":
		//redundant files
		$fp = cms_join_path(dirname(__FILE__),'changelog.inc');
		if (is_file($fp))
			unlink($fp);
		$fp = cms_join_path(dirname(__FILE__),'admin-header.html');
		if (is_file($fp))
			unlink($fp);
		$fp = cms_join_path(dirname(__FILE__),'include','module-admin.css');
		if (is_file($fp))
			unlink($fp);
		//extra preferences
		$this->SetPreference('keyword_block','metakeywords');
		$this->SetPreference('robot_start','');
		$this->SetPreference('robot_end','');
		//modify preference
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
		$sep = $this->GetPreference('keyword_separator',' ');
		$now = trim($this->GetPreference('keyword_exclude'));
		if ($now)
		{
			$old = explode($sep,$now);
			$new = explode(',',trim($words));
			$new = array_unique($old+$new,SORT_STRING);
			$words = implode($sep,$new);
		}
		elseif ($sep != ',')
			$words = str_replace(',',$sep,$words);
		$this->SetPreference('keyword_exclude',$words);

	case "1.6":
		// meta-groups table schema
		$flds = "
group_id I(2) AUTO KEY,
gname C(64),
vieworder I(2),
active I(1) NOTNULL DEFAULT 1
";
		$sqlarray = $dict->CreateTableSQL($pre.'module_seotools_group',$flds,$taboptarray);
		$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
		if (!$result) return false;
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
value C(255),
output C(128),
calc I(1) NOTNULL DEFAULT 0,
smarty I(1) NOTNULL DEFAULT 0,
vieworder I(2),
active I(1) NOTNULL DEFAULT 1
";
		$sqlarray = $dict->CreateTableSQL($pre.'module_seotools_meta',$flds,$taboptarray);
		$result = ($sqlarray) ? ($dict->ExecuteSQLArray($sqlarray,false) == 2) : false;
		if (!$result) return false;
		// extra index
		$sqlarray = $dict->CreateIndexSQL('idx_seogrps', $pre.'module_seotools_meta', 'group_id');
		$dict->ExecuteSQLArray($sqlarray);	
		
		// add default meta, and delete redundant prefs
		require ('method.setmeta.php'); 

		break;
}

?>
