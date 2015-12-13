<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

switch($oldversion)
{
	case "1.0":
	case "1.1":
	case "1.2":
		$dict = NewDataDictionary($db);
		//non-unique index
		$tbl = cms_db_prefix()."module_seotools";
		//standard field sizes (e.g. for postgresql)
		$sql = $dict->AlterColumnSQL($tbl,
			"indexable L NOTNULL DEFAULT 1, priority I(4)");
		$dict->ExecuteSQLArray($sql);
		//support ignored problems
		$sql = $dict->AddColumnSQL($tbl, "ignored X");
		$dict->ExecuteSQLArray($sql);
		//conform to fixed preference name
		$str = $this->GetPreference('title_meta','{title} | {$sitename}');
		$this->SetPreference('meta_title',$str);
		$this->DropPreference('title_meta');
		//convert bool preference formats
		$val = ($this->GetPreference('create_robots','false') == 'false') ? 0:1;
		$this->SetPreference('create_robots', $val);
		$val = ($this->GetPreference('create_sitemap','false') == 'false') ? 0:1;
		$this->SetPreference('create_sitemap', $val);
		$val = ($this->GetPreference('push_sitemap','false') == 'false') ? 0:1;
		$this->SetPreference('push_sitemap', $val);
		$val = ($this->GetPreference('description_auto_generate','false') == 'false') ? 0:1;
		$this->SetPreference('description_auto_generate', $val);
		$val = ($this->GetPreference('meta_dublincore','false') == 'false') ? 0:1;
		$this->SetPreference('meta_dublincore', $val);
		$val = ($this->GetPreference('meta_opengraph','false') == 'false') ? 0:1;
		$this->SetPreference('meta_opengraph', $val);
		$val = ($this->GetPreference('meta_standard','false') == 'false') ? 0:1;
		$this->SetPreference('meta_standard', $val);
		//extra preferences
		$this->SetPreference('content_type','html');
		$this->SetPreference('keyword_separator',' ');
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
		//extra preference
		$this->SetPreference('keyword_block','metakeywords');
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
		break;
}

?>
