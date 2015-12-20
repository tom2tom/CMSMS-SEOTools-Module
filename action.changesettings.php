<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

if (!$this->CheckAccess('Edit SEO Settings'))
	return $this->DisplayErrorPage($this->Lang('accessdenied'));

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin');

if (isset($_POST['display_metadata']))
	$this->Redirect($id, 'meta');

if (isset($_POST['display_robots_file']))
	$this->Redirect($id, 'robot');

if (isset($_POST['do_regenerate'])) {
	// Regenerate sitemap.xml and/or robots.txt, according to preferences
	if ($this->GetPreference('create_robots',0)) {
		$funcs = new SEO_robot();
		$botok = $funcs->createRobotsTXT($this);
	}
	else {
		$botok = false;
	}
	if ($this->GetPreference('create_sitemap',0)) {
		$funcs = new SEO_sitemap();
		$mapok = $funcs->createSitemap($this);
	}
	else {
		$mapok = false;
	}
	if ($botok || $mapok) {
		if ($botok && $mapok) {
			$audit = 'Manually regenerated sitemap.xml and robots.txt';
			$msgkey = 'both_regenerated';
		}
		elseif ($botok) {
			$audit = 'Manually regenerated robots.txt';
			$msgkey = 'robot_regenerated';
		}
		else {
			$audit = 'Manually regenerated sitemap.xml';
			$msgkey = 'sitemap_regenerated';
		}
		$this->Audit(0, $this->Lang('friendlyname'), $audit);
		$this->Redirect($id, 'defaultadmin', '', array('message'=>$msgkey,'tab'=>'sitemapsettings'));
	}
	else {
		$this->Redirect($id, 'defaultadmin', '', array('warning'=>1,'message'=>'none_regenerated','tab'=>'sitemapsettings'));
	}
}

$pre = cms_db_prefix();

if (isset($_POST['save_meta_settings'])) {
	// Update active groups
	$pre = cms_db_prefix();
	$query = 'SELECT gname FROM '.$pre.'module_seotools_group WHERE gname != \'before\' AND gname != \'after\'';
	$groups = $db->GetCol($query);
	$query = 'UPDATE '.$pre.'module_seotools_group SET active=? WHERE gname=?';
	foreach ($groups as $name) {
		$db->Execute($query, array($val,(int)$_POST[$name]));
		unset($_POST[$name]);
	}

	$args = array('message'=>'settings_updated','tab'=>'metasettings');

	// These are irrelevant here
	unset($_POST['save_meta_settings']);
	unset($_POST['mact']);
	unset($_POST[$this->secstr]);
	// Update metadata
	$query = 'UPDATE '.$pre.'module_seotools_meta SET value=?,active=? WHERE mname=?';
	foreach ($_POST as $name=>$val) {
		switch ($name) {
		 case 'description_block':
			$old = $this->GetPreference('description_block','');
			if ($val && $val != $old) {
				$this->SetPreference('description_block',$val);
				$val = str_replace(' ','_',$val);
				$rst = $db->Execute('SELECT content_id FROM '.$pre.'content_props WHERE prop_name=?',
					array($val));
				if ($rst && !$rst->EOF) {
					$args['message'] = 'content_block_exists';
				}
				else {
					$old = str_replace(' ','_',$old);
					// conform tabled properties
					$db->Execute('UPDATE '.$pre.'content_props SET prop_name=? WHERE prop_name=?',
						array($val,$old));
					$args['message'] = 'content_block_check';
				}
				if ($rst) $rst->Close();
			}
			break;
		 default:
		 	$db->Execute($query,array($val,1,$name));
			break;
		}
	}

	$this->Audit(0, $this->Lang('friendlyname'), 'Updated META settings');
	$this->Redirect($id, 'defaultadmin', '', $args);
}

if (isset($_POST['save_keyword_settings'])) {

	$args = array('message'=>'settings_updated','tab'=>'keywordsettings');

	$val = $this->GetPreference('keyword_block','');
	$new = $_POST['keyword_block'];
	if ($new && $new != $val) {
		$this->SetPreference('keyword_block',$new);
		$new = str_replace(' ','_',$new);
		$rst = $db->Execute('SELECT content_id FROM '.$pre.'content_props WHERE prop_name=?',
			array($new));
		if ($rst && !$rst->EOF) {
			$args['message'] = 'content_block_exists';
		}
		else {
			$old = str_replace(' ','_',$val);
			// conform tabled properties
			$db->Execute('UPDATE '.$pre.'content_props SET prop_name=? WHERE prop_name=?',
				array($new,$old));
			$args['message'] = 'content_block_check';
			$old = $new; //maybe needed for separator-updates
		}
		if ($rst) $rst->Close();
	}
	else {
		$old = str_replace(' ','_',$val);
	}

	$val = $this->GetPreference('keyword_separator',',');
	$new = $_POST['keyword_separator'];
	if ($new && $new != $val) {
		$this->SetPreference('keyword_default',str_replace($val,$new,$_POST['keyword_default']));
		$this->SetPreference('keyword_exclude',str_replace($val,$new,$_POST['keyword_exclude']));
		// Replace sep in all tabled keyword fields
		$rst = $db->Execute('SELECT content_id,keywords FROM '.$pre.
			'module_seotools WHERE keywords IS NOT null AND keywords!=""');
		if ($rst && !$rst->EOF) {
			$rows = $rst->GetArray();
			$rst->Close();
			$query = 'UPDATE '.$pre.'module_seotools SET keywords=? WHERE content_id=?';
			foreach ($rows as $one) {
				$merge = str_replace($val, $new, $one[1]);
				$db->Execute($query, array($merge, $one[0]));
			}
		}
		$rst = $db->Execute('SELECT content_id,content FROM '.$pre.
			'content_props WHERE prop_name=? AND content IS NOT null AND content!=""',
			array($old));
		if ($rst && !$rst->EOF) {
			$rows = $rst->GetArray();
			$rst->Close();
			$query = 'UPDATE '.$pre.'content_props SET content=? WHERE content_id=?';
			foreach ($rows as $one) {
				$merge = str_replace($val, $new, $one[1]);
				$db->Execute($query, array($merge, $one[0]));
			}
		}
		$this->SetPreference('keyword_separator',$_POST['keyword_separator']);
	}
	else {
		$this->SetPreference('keyword_default',$_POST['keyword_default']);
		$this->SetPreference('keyword_exclude',$_POST['keyword_exclude']);
	}

	// These are irrelevant here
	unset($_POST['save_keyword_settings']);
	unset($_POST['mact']);
	unset($_POST[$this->secstr]);
	unset($_POST['keyword_block']);
	unset($_POST['keyword_separator']);

	foreach ($_POST as $name=>$val) {
		$this->SetPreference($name,$val);
	}

	$this->Audit(0, $this->Lang('friendlyname'), 'Updated keyword settings');
	$this->Redirect($id, 'defaultadmin', '', $args);
}

if (isset($_POST['save_sitemap_settings'])) {

	$db->Execute('UPDATE '.$pre.'module_seotools_meta SET value=? WHERE mname=\'verification\'',
 		array($_POST['verification']));

	// These are irrelevant here
	unset($_POST['save_sitemap_settings']);
	unset($_POST['mact']);
	unset($_POST[$this->secstr]);
	unset($_POST['verification']);

	foreach ($_POST as $name=>$val) {
		$this->SetPreference($name, $val);
	}

	$this->Audit(0, $this->Lang('friendlyname'), 'Updated sitemap settings');
	$this->Redirect($id, 'defaultadmin', '', array('message'=>'settings_updated','tab'=>'sitemapsettings'));
}

?>
