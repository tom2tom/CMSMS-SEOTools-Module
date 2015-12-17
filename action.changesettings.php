<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Regenerate sitemap.xml and/or robots.txt, according to preferences

if (!$this->CheckAccess('Edit SEO Settings'))
	return $this->DisplayErrorPage($this->Lang('accessdenied'));

if (isset($_POST['cancel']))
	$this->Redirect($id, 'defaultadmin');

if (isset($_POST['display_robots_file']))
	$this->Redirect($id, 'robot');

if (isset($_POST['do_regenerate'])) {
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
	// These are irrelevant here
	unset($_POST['save_meta_settings']);
	unset($_POST['mact']);
	unset($_POST[$this->secstr]);

	// Update active groups
	$pre = cms_db_prefix();
	$query = 'SELECT gname FROM '.$pre.'module_seotools_group WHERE gname != \'before\' AND gname != \'after\'';
	$groups = $db->GetCol($query);
	$query = 'UPDATE '.$pre.'module_seotools_group SET active=? WHERE gname=?';
	foreach ($groups as $name) {
		$val = !empty($_POST[$name]) ? 1 : 0;
		$db->Execute($query, array($val,$name));
		unset($_POST[$name]);
	}

	$args = array('message'=>'settings_updated','tab'=>'metasettings');

	// Update metadata
	$query = 'UPDATE '.$pre.'module_seotools_meta SET value=?,active=? WHERE mname=?';
	$foreach($_POST as $name=>$val) {
		switch ($name) {
		 case 'description_block':
			$old = $this->GetPreference('description_block','');
			if ($val && $val != $old) {
				$val = str_replace(' ','_',$val);
				$rst = $db->Execute('SELECT content_id FROM '.$pre.'content_props WHERE prop_name=?',
					array($val));
				if ($rst && !$rst->EOF) {
					$args['message'] = 'TODO'; //lang key to warn user about no change
					$args['warning'] = 1;
				}
				else {
					$old = str_replace(' ','_',$old);
					// conform tabled properties
					$db->Execute('UPDATE '.$pre.'content_props SET prop_name=? WHERE prop_name=?',
						array($val,$old));
					$this->SetPreference('description_block',$_POST['description_block']);
					$args['message'] = 'TODO'; //TODO lang key to tell user about conforming block-name in all templates and pages
				}
				if ($rst) $rst->Close(); 
			}
			break;
		 default:
			//TODO manage injection-risk here
			//TODO handle runtime booleans for [in]active
		 	$db->Execute($query,array($val,1,$mname));
			break;
		}
	}

	$this->Audit(0, $this->Lang('friendlyname'), 'Edited META settings');
	$this->Redirect($id, 'defaultadmin', '', $args);
}

if (isset($_POST['save_sitemap_settings'])) {
	$val = (isset($_POST['create_sitemap'])) ? 1 : 0;
	$this->SetPreference('create_sitemap', $val);
	$val = (isset($_POST['push_sitemap'])) ? 1 : 0;
	$this->SetPreference('push_sitemap', $val);
	$val = (isset($_POST['create_robots'])) ? 1 : 0;
	$this->SetPreference('create_robots', $val);
	$val = (isset($_POST['robot_start'])) ? $_POST['robot_start'] : '';
	$this->SetPreference('robot_start',$val);
	$val = (isset($_POST['robot_end'])) ? $_POST['robot_end'] : '';
	$this->SetPreference('robot_end',$val);
	$this->SetPreference('verification', $_POST['verification']);

	$this->Audit(0, $this->Lang('friendlyname'), 'Edited sitemap settings');
	$this->Redirect($id, 'defaultadmin', '', array('message'=>'settings_updated','tab'=>'sitemapsettings'));
}

if (isset($_POST['save_keyword_settings'])) {

	$args = array('message'=>'settings_updated','tab'=>'keywordsettings');

	$val = $this->GetPreference('keyword_block','');
	$new = $_POST['keyword_block'];
	if ($new && $new != $val) {
		$new = str_replace(' ','_',$new);
		$rst = $db->Execute('SELECT content_id FROM '.$pre.'content_props WHERE prop_name=?',
			array($new));
		if ($rst && !$rst->EOF) {
			$args['message'] = 'TODO'; //lang key to warn user about no change
			$args['warning'] = 1;
		}
		else {
			$old = str_replace(' ','_',$val);
			// conform tabled properties
			$db->Execute('UPDATE '.$pre.'content_props SET prop_name=? WHERE prop_name=?',
				array($new,$old));
			$this->SetPreference('keyword_block',$_POST['keyword_block']);
			$args['message'] = 'TODO'; //lang key to tell user about conforming block-name in all templates and pages
			$old = $new; //maybe needed for separator-updates
		}
		if ($rst) $rst->Close(); 
	}
	else {
		$old = str_replace(' ','_',$val);
	}
	$this->SetPreference('keyword_minlength',$_POST['keyword_minlength']);
	$this->SetPreference('keyword_title_weight',$_POST['keyword_title_weight']);
	$this->SetPreference('keyword_description_weight',$_POST['keyword_description_weight']);
	$this->SetPreference('keyword_headline_weight',$_POST['keyword_headline_weight']);
	$this->SetPreference('keyword_content_weight',$_POST['keyword_content_weight']);
	$this->SetPreference('keyword_minimum_weight',$_POST['keyword_minimum_weight']);

	$val = $this->GetPreference('keyword_separator',' ');
	$new = $_POST['keyword_separator'];
	if ($new && $new != $val) {
		$words = explode($val,$_POST['default_keywords']);
		$this->SetPreference('default_keywords',implode($new,$words));
		$words = explode($val,$_POST['keyword_exclude']);
		$this->SetPreference('keyword_exclude',implode($new,$words));
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
		$this->SetPreference('default_keywords',$_POST['default_keywords']);
		$this->SetPreference('keyword_exclude',$_POST['keyword_exclude']);
	}

	$this->Audit(0, $this->Lang('friendlyname'), 'Edited keyword settings');
	$this->Redirect($id, 'defaultadmin', '', $args);
}

?>
