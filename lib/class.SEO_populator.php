<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_populator
{
	private $adminurl = NULL;
	private $theme = NULL;
	/*
	public function GetNotificationOutput(&$mod, $priority = 2)
	{
		$alerts = getUrgentAlerts($mod,TRUE,TRUE);
		if ($alerts)
		{
			$obj = new StdClass;
			$obj->priority = $priority;
			$obj->html = $mod->Lang('problem_alert',$mod->CreateLink(null, 'defaultadmin', '', $mod->Lang('problem_link_title')));

			return $obj;
		}
		return FALSE;
	}
	*/
	private function getSeeLink(&$mod, $priority, $title = '')
	{
		if ($this->adminurl == NULL) {
			$gCms = cmsms();
			$config = $gCms->GetConfig();
			$this->adminurl = (!empty($config['admin_url'])) ?
				$config['admin_url']:
				$config['root_url'].'/'.$config['admin_dir'];
			$this->theme = ($mod->before20) ?
				$gCms->get_variable('admintheme'):
				cms_utils::get_theme_object();
		}
		$lnk = '<a class=@"'.$priority.'" href="#"><img src="'.$this->adminurl.'/themes/'
		 .$this->theme->themeName.'/images/icons/system/edit.gif"';
		if ($title) {
			$lnk .= ' title = "'.$title.'"';
		}
		$lnk .= ' style="vertical-align: middle;" /></a>';
		return $lnk;
	}

	public function getUrgentAlerts(&$mod, $omit_inactive = FALSE, $omit_ignored = FALSE)
	{
		$gCms = cmsms();
		$alerts = array();
		// No Meta tags are inserted
		if (!($mod->GetPreference('meta_standard',FALSE) || $mod->GetPreference('meta_dublincore',FALSE)))
		{
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $mod->Lang('use_standard_or_dublincore_meta');
			$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		if (!$mod->GetPreference('description_auto_generate',FALSE))
		{
			if ($mod->GetPreference('description_block','') != '') {
				// Content pages without description
				$query = 'SELECT C.content_id, C.content_name, C.type, C.active, S.ignored FROM '
				.$pre.'content C LEFT JOIN '
				.$pre.'content_props P ON C.content_id = P.content_id LEFT JOIN '
				.$pre.'module_seotools S ON C.content_id = S.content_id WHERE ';
				if ($omit_inactive) {
					$query .= 'C.active=1 AND';
				}
				$query .= 'C.type LIKE ? AND P.prop_name=? AND(P.content IS NULL OR P.content=?)';
				$parms = array('content%'); //can't be an injection risk here
				$parms[] = str_replace(' ','_',$mod->GetPreference('description_block',''));
				$parms[] = '';
				$result = $db->Execute($query, $parms);
				if ($result) {
					$code = 'nometa';
					$keep = !$omit_ignored;
					while ($problem = $result->fetchRow()) {
						$ig = $problem['ignored'];
						if (($ig == null && $keep)
						  ||($ig != null && strpos($ig,$code) !== FALSE)) {
							$alert = array();
							$alert['group'] = 'pages';
							$alert['active'] = $problem['active'];
							$alert['pages'] = array($problem['content_name']);
							$alert['message'] = $mod->Lang('meta_description_missing');
							$alert['ignored'] = $problem['ignored'];
							$alert['links_data'][$problem['content_id']] = array($problem['content_name'],$code);
							$alerts[] = $alert;
						}
					}
				}
			}
			else {
				$alert = array();
				$alert['group'] = 'settings';
				$alert['message'] = $mod->Lang('set_up_description_block');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
		}
		elseif (strpos($mod->GetPreference('description_auto',''),'{keywords}') === FALSE) {
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $mod->Lang('set_up_auto_description');
			$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}

		$config = $gCms->GetConfig();
		// sitemap.xml not writeable
		if ($mod->GetPreference('create_sitemap',0)) {
			$path = cms_join_path($config['root_path'],'sitemap.xml');
			if (file_exists($path) && !is_writeable($path)) {
				$alert = array();
				$alert['group'] = 'system';
				$alert['message'] = $mod->Lang('sitemap_not_writeable');
				$alert['links'][] = $mod->Lang('chmod_sitemap');
				$alerts[] = $alert;
			}
		}

		// robots.txt not writeable
		if ($mod->GetPreference('create_robots',0)) {
			$path = cms_join_path($config['root_path'],'robots.txt');
			if (file_exists($path) && !is_writeable($path)) {
				$alert = array();
				$alert['group'] = 'system';
				$alert['message'] = $mod->Lang('robots_not_writeable');
				$alert['links'][] = $mod->Lang('chmod_robots');
				$alerts[] = $alert;
			}
		}

		if ($mod->GetPreference('meta_opengraph',FALSE)) {
			// No OpenGraph admin set
			if (($mod->GetPreference('meta_opengraph_admins','') == '') &&($mod->GetPreference('meta_opengraph_application','') == '')) {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_admins');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			// No OpenGraph page type set
			if ($mod->GetPreference('meta_opengraph_type','') == '') {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_type');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			// No OpenGraph sitename set
			if ($mod->GetPreference('meta_opengraph_sitename','') == '') {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_sitename');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			// No OpenGraph image set
			if ($mod->GetPreference('meta_opengraph_image','') == '') {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_image');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
		}
		return $alerts;
	}

	public function getImportantAlerts(&$mod, $omit_inactive = FALSE, $omit_ignored = FALSE)
	{
		$gCms = cmsms();
		$config = $gCms->GetConfig();
		if ($this->adminurl == NULL) {
			$this->adminurl = (isset($config['admin_url'])) ?
				$config['admin_url']:
				$config['root_url'].'/'.$config['admin_dir'];
		}
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		$alerts = array();
		// Pretty URLs not working
		if (($config['assume_mod_rewrite'] != 1) &&($config['internal_pretty_urls'] != 1)) {
			if($this->theme == NULL) {
				$this->theme = ($mod->before20) ?
					$gCms->get_variable('admintheme'):
					cms_utils::get_theme_object();
			}
			$alert = array();
			$alert['group'] = 'system';
			$alert['message'] = $mod->Lang('activate_pretty_urls');
			$alert['links'][] =
'<a href="http://docs.cmsmadesimple.org/configuration/pretty-url" onclick="window.open(this.href,\'_blank\');return false;"><img src="'
.$this->adminurl.'/themes/'.$this->theme->themeName.'/images/icons/system/info-external.gif" title = "'
.$mod->Lang('get_help').'" style="vertical-align: middle;" /></a>';
			$alerts[] = $alert;
		}
		// Content pages with short description
		$query = 'SELECT C.content_id, C.content_name, C.active, S.ignored FROM '
		 .$pre.'content C INNER JOIN '
		 .$pre.'content_props P ON C.content_id = P.content_id LEFT JOIN '
		 .$pre.'module_seotools S ON C.content_id = S.content_id WHERE ';
		if ($omit_inactive) {
			$query .= 'C.active=1 AND';
		}
		//TODO CHAR_LENGTH function
		$query .= 'C.type LIKE ? AND P.prop_name=? AND P.content<>? AND CHAR_LENGTH(P.content) < 75'; //NOTE not much portable. $db->length dun't work!
		$parms = array('content%'); //can't be an injection risk here
		$parms[] = str_replace(' ','_',$mod->GetPreference('description_block',''));
		$parms[] = '';
		$result = $db->Execute($query, $parms);
		if ($result) {
			$code = 'shortmeta';
			$keep = !$omit_ignored;
			while ($problem = $result->fetchRow()) {
				$ig = $problem['ignored'];
				if (($ig == null && $keep)
				  ||($ig != null && strpos($ig,$code) !== FALSE)) {
					$alert = array();
					$alert['group'] = 'descriptions';
					$alert['active'] = $problem['active'];
					$alert['pages'] = array($problem['content_name']);
					$alert['message'] = $mod->Lang('meta_description_short');
					$alert['ignored'] = $problem['ignored'];
					$alert['links_data'][$problem['content_id']] = array($problem['content_name'],$code);
					$alerts[] = $alert;
				}
			}
		}

		// Any pages with duplicate title
		$query = 'SELECT c1.content_alias AS c1name, c1.content_id AS c1id, c1.active AS c1a,
c2.content_alias AS c2name, c2.content_id AS c2id, c2.active as c2a, S.ignored FROM '
		.$pre.'content c1 INNER JOIN '
		.$pre.'content c2 ON c1.content_name = c2.content_name LEFT JOIN '
		.$pre.'module_seotools ON c1.content_id = S.content_id WHERE ';
		if ($omit_inactive) {
			$query .= 'c1.active=1 AND c2.active=1 AND ';
		}
		$query .= 'c1.content_id<c2.content_id';
		$result = $db->Execute($query);
		if ($result) {
			$code = 'sametitle';
			$keep = !$omit_ignored;
			while ($problem = $result->fetchRow()) {
				$ig = $problem['ignored'];
				if (($ig == null && $keep)
				  ||($ig != null && strpos($ig,$code) !== FALSE)) {
					$alert = array();
					$alert['group'] = 'titles';
					$alert['active'] = $problem['c1a'].','.$problem['c2a'];
					$alert['pages'] = array($problem['c1name'],$problem['c2name']);
					$alert['message'] = $mod->Lang('duplicate_titles');
					$alert['ignored'] = $problem['ignored'];
					$alert['links_data'][$problem['c1id']] = array($problem['c1name'],$code);
					$alert['links_data'][$problem['c2id']] = array($problem['c2name'],$code);
					$alerts[] = $alert;
				}
			}
		}

		// Any pages with duplicate description
		$query = 'SELECT p1.content_id AS p1id, p2.content_id AS p2id, S.ignored FROM '
		.$pre.'content_props p1 INNER JOIN '
		.$pre.'content_props p2 ON p1.prop_name = p2.prop_name LEFT JOIN '
		.$pre.'module_seotools S ON p1.content_id = S.content_id
WHERE(p1.prop_name = ? AND p1.content_id < p2.content_id  AND p1.content <> ? AND p2.content = p1.content)';
		$parms = array();
		$parms[] = str_replace(' ','_',$mod->GetPreference('description_block',''));
		$parms[] = '';
		$result = $db->Execute($query, $parms);
		if ($result) {
			$query = 'SELECT content_id, content_name, active FROM '.$pre.'content WHERE ';
			if ($omit_inactive) {
				$query .= 'active=1 AND ';
			}
			$query .= '(content_id=? OR content_id=?)';
			$code = 'samedesc';
			$keep = !$omit_ignored;
			while ($problem = $result->fetchRow()) {
				$ig = $problem['ignored'];
				if (($ig == null && $keep)
				  ||($ig != null && strpos($ig,$code) !== FALSE)) {
					$result1 = $db->Execute($query,array($problem['p1id'],$problem['p2id']));
					$first = $result1->fetchRow();
					$second = $result1->fetchRow();
					$alert = array();
					$alert['group'] = 'descriptions';
					$alert['active'] = $first['active'].','.$second['active'];
					$alert['pages'] = array($first['content_name'],$second['content_name']);
					$alert['message'] = $mod->Lang('duplicate_descriptions');
					$alert['ignored'] = $problem['ignored']; //CHECKME both?
					$alert['links_data'][$first['content_id']] = array($first['content_name'],$code);
					$alert['links_data'][$second['content_id']] = array($second['content_name'],$code);
					$alerts[] = $alert;
				}
			}
		}
		// No author provided
		if ($mod->GetPreference('meta_publisher','') == '') {
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $mod->Lang('provide_an_author');
			$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		return $alerts;
	}

	public function getTabLink($priority, $label)
	{
		return '<a class="@'.$priority.'" href="#">'.$label.'</a>';
	}

	public function getNoticeAlerts(&$mod)
	{
		$alerts = array();
		// No standard meta
		if (!$mod->GetPreference('meta_standard',FALSE)) {
			$alert = array();
			$alert['message'] = $mod->Lang('use_standard_meta');
			$alert['links'][] = getTabLink(4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// Submit a sitemap
		if (!$mod->GetPreference('create_sitemap',0)) {
			$alert = array();
			$alert['message'] = $mod->Lang('create_a_sitemap');
			$alert['links'][] = getTabLink(6,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		elseif (!$mod->GetPreference('push_sitemap',0)) {
		  // Automatically submit the sitemap
			$alert = array();
			$alert['message'] = $mod->Lang('automatically_upload_sitemap');
			$alert['links'][] = getTabLink(6,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// Create a robots.txt file
		if (!$mod->GetPreference('create_robots',0)) {
			$alert = array();
			$alert['message'] = $mod->Lang('create_robots');
			$alert['links'][] = getTabLink(6,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// Set a default image
		return $alerts;
	}

	public function getFixLink(&$mod, $sp, $id, $pagename = '')
	{
		$gCms = cmsms();
		$config = $gCms->GetConfig();
		if (isset($config['admin_url']))
			$adminurl = $config['admin_url'];
		else
			$adminurl = $config['root_url'].'/'.$config['admin_dir'];
		$theme = ($mod->before20) ? $gCms->get_variable('admintheme'):
			cms_utils::get_theme_object();
		$lnk = '<a href="'.$adminurl.'/editcontent.php?'.$mod->pathstr.'='.$sp.'&content_id='.$id
		 .'"><img src="'.$adminurl.'/themes/'
		 .$theme->themeName.'/images/icons/system/edit.gif" title = "';
		if ($pagename) {
			$lnk .= $mod->Lang('edit_page',$pagename);
		}
		else {
			$lnk .= $mod->Lang('edit_page2');
		}
		$lnk .= '" style="vertical-align: middle;" /></a>';
		return $lnk;
	}
}

?>
