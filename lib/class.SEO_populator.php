<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_populator
{
	private $adminurl = null;
	private $theme = null;
/*
	public function GetNotificationOutput(&$mod, $priority = 2)
	{
		$alerts = getUrgentAlerts($mod,true,true);
		if ($alerts) {
			$obj = new StdClass;
			$obj->priority = $priority;
			$obj->html = $mod->Lang('problem_alert',$mod->CreateLink(null, 'defaultadmin', '', $mod->Lang('problem_link_title')));

			return $obj;
		}
		return false;
	}
*/
	private function getSeeLink(&$mod, $indx, $title = '')
	{
		$gCms = cmsms(); //CMSMS 1.8+
		if ($this->adminurl == null) {
			$config = $gCms->GetConfig();
			if (isset($config['admin_url'])) {
				$this->adminurl = $config['admin_url'];
			}
			else {
				$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
				$this->adminurl = $rooturl.'/'.$config['admin_dir'];
			}
		}
		if ($this->theme == null) {
			$this->theme = ($mod->before20) ?
				$gCms->get_variable('admintheme'):
				cms_utils::get_theme_object();
		}
		//mimic $mod->GetTooltipLink
		if (!$title) {
			$title = $mod->Lang('showtab');
		}
		$lnk = '<a href="#" class="admin-tooltip" tabindx="'.$indx.'"><img src="'.
		$this->adminurl.'/themes/'.$this->theme->themeName.'/images/icons/system/edit.gif" class="systemicon" /><span>'.
		$title.'</span></a>';
		return $lnk;
	}

	public function getUrgentAlerts(&$mod, $omit_inactive = false, $omit_ignored = false)
	{
		$alerts = array();
		$gCms = cmsms(); //CMSMS 1.8+
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		$groups = $db->GetArray('SELECT * FROM '.$pre.'module_seotools_group WHERE gname != \'before\' AND gname != \'after\' AND active=1');
		$meta = $db->GetAssoc('SELECT mname,value FROM '.$pre.'module_seotools_meta WHERE active=1');

		// No Meta tags are used
		if (!$groups) {
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $mod->Lang('use_standard_or_dublincore_meta'); //TODO generalise
			$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		if (!$mod->GetPreference('description_auto_generate',0)) {
			$pref = $mod->GetPreference('description_block','');
			if ($pref) {
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
				$parms[] = str_replace(' ','_',$pref);
				$parms[] = '';
				$rst = $db->Execute($query, $parms);
				if ($rst) {
					$code = 'nometa';
					$keep = !$omit_ignored;
					while ($problem = $rst->fetchRow()) {
						$ig = $problem['ignored'];
						if (($ig == null && $keep)
						  ||($ig != null && strpos($ig,$code) !== false)) {
							$alert = array();
							$alert['group'] = 'pages';
							$alert['active'] = $problem['active'];
							$alert['pages'] = array($problem['content_name']);
							$alert['message'] = $mod->Lang('description_missing');
							$alert['ignored'] = $problem['ignored'];
							$alert['links_data'][$problem['content_id']] = array($problem['content_name'],$code);
							$alerts[] = $alert;
						}
					}
					$rst->Close();
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
		elseif (strpos($mod->GetPreference('description_auto',''),'{keywords}') === false) {
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $mod->Lang('set_up_auto_description');
			$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}

		// Get root directory (whereever that actually is)
		$offs = strpos(__FILE__,'modules'.DIRECTORY_SEPARATOR.$mod->GetName());
		$base = substr(__FILE__, 0, $offs); //has trailing separator

		// sitemap.xml not writeable
		if ($mod->GetPreference('create_sitemap',0)) {
			$path = $base.'sitemap.xml';
			if (file_exists($path) && !is_writable($path)) {
				$alert = array();
				$alert['group'] = 'system';
				$alert['message'] = $mod->Lang('sitemap_not_writeable');
				$alert['links'][] = $mod->Lang('chmod_sitemap');
				$alerts[] = $alert;
			}
		}

		// robots.txt not writeable
		if ($mod->GetPreference('create_robots',0)) {
			$path = $base.'robots.txt';
			if (file_exists($path) && !is_writable($path)) {
				$alert = array();
				$alert['group'] = 'system';
				$alert['message'] = $mod->Lang('robots_not_writeable');
				$alert['links'][] = $mod->Lang('chmod_robots');
				$alerts[] = $alert;
			}
		}

		if (in_array('meta_og',$groups)) {
			// No OpenGraph admin set
			if (empty($meta['meta_og_admins']) && empty($meta['meta_og_application'])) {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_admins');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			// No OpenGraph page type set
			if (empty($meta['meta_og_type'])) {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_type');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			// No OpenGraph sitename set
			if (empty($meta['meta_og_sitename'])) {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_sitename');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			// No OpenGraph image set
			if (empty($meta['meta_og_image'])) {
				$alert = array();
				$alert['group'] = 'opengraph';
				$alert['message'] = $mod->Lang('no_opengraph_image');
				$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
				$alerts[] = $alert;
			}
			//TODO checks for twitter, google tags
		}
		return $alerts;
	}

	public function getImportantAlerts(&$mod, $omit_inactive = false, $omit_ignored = false)
	{
		$gCms = cmsms(); //CMSMS 1.8+
		$config = $gCms->GetConfig();
		if ($this->adminurl == null) {
			if (isset($config['admin_url'])) {
				$this->adminurl = $config['admin_url'];
			}
			else {
				$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
				$this->adminurl = $rooturl.'/'.$config['admin_dir'];
			}
		}
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		$alerts = array();
		// Pretty URLs not working
		if (($config['assume_mod_rewrite'] != 1) && ($config['internal_pretty_urls'] != 1)) {
			if($this->theme == null) {
				$this->theme = ($mod->before20) ?
					$gCms->get_variable('admintheme'):
					cms_utils::get_theme_object();
			}
			$alert = array();
			$alert['group'] = 'system';
			$alert['message'] = $mod->Lang('activate_pretty_urls');
			$alert['links'][] =
'<a href="http://docs.cmsmadesimple.org/configuration/pretty-url" class="admin-tooltip" onclick="window.open(this.href,\'_blank\');return false;"><img src="'.$this->adminurl.
'/themes/'.$this->theme->themeName.'/images/icons/system/info-external.gif" class="systemicon" /><span>'.$mod->Lang('get_help').'</span></a>';
			$alerts[] = $alert;
		}
		// Content pages with short description
		// Not empty description, that's an urgent alert
		$query = 'SELECT C.content_id, C.content_name, C.active, S.ignored FROM '
		 .$pre.'content C INNER JOIN '
		 .$pre.'content_props P ON C.content_id = P.content_id LEFT JOIN '
		 .$pre.'module_seotools S ON C.content_id = S.content_id WHERE ';
		if ($omit_inactive) {
			$query .= 'C.active=1 AND';
		}
		// MySQL and PostgreSQL support CHAR_LENGTH() $db->length dun't work!
		$query .= 'C.type LIKE ? AND P.prop_name=? AND P.content!="" AND CHAR_LENGTH(P.content) < 75';
		$parms = array('content%'); //can't be an injection risk here
		$parms[] = str_replace(' ','_',$mod->GetPreference('description_block',''));
		$rst = $db->Execute($query, $parms);
		if ($rst) {
			$code = 'shortmeta';
			$keep = !$omit_ignored;
			while ($problem = $rst->fetchRow()) {
				$ig = $problem['ignored'];
				if (($ig == null && $keep)
				  ||($ig != null && strpos($ig,$code) !== false)) {
					$alert = array();
					$alert['group'] = 'descriptions';
					$alert['active'] = $problem['active'];
					$alert['pages'] = array($problem['content_name']);
					$alert['message'] = $mod->Lang('description_short');
					$alert['ignored'] = $problem['ignored'];
					$alert['links_data'][$problem['content_id']] = array($problem['content_name'],$code);
					$alerts[] = $alert;
				}
			}
			$rst->Close();
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
		$rst = $db->Execute($query);
		if ($rst) {
			$code = 'sametitle';
			$keep = !$omit_ignored;
			while ($problem = $rst->fetchRow()) {
				$ig = $problem['ignored'];
				if (($ig == null && $keep)
				  ||($ig != null && strpos($ig,$code) !== false)) {
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
			$rst->Close();
		}

		// Pages with duplicate description
		$query = 'SELECT p1.content_id AS p1id, p2.content_id AS p2id, S.ignored FROM '
		.$pre.'content_props p1 INNER JOIN '
		.$pre.'content_props p2 ON p1.prop_name = p2.prop_name LEFT JOIN '
		.$pre.'module_seotools S ON p1.content_id = S.content_id
WHERE(p1.prop_name = ? AND p1.content_id < p2.content_id  AND p1.content != ? AND p2.content = p1.content)';
		$parms = array();
		$parms[] = str_replace(' ','_',$mod->GetPreference('description_block',''));
		$parms[] = '';
		$rst = $db->Execute($query, $parms);
		if ($rst) {
			$query = 'SELECT content_id, content_name, active FROM '.$pre.'content WHERE ';
			if ($omit_inactive) {
				$query .= 'active=1 AND ';
			}
			$query .= '(content_id=? OR content_id=?)';
			$code = 'samedesc';
			$keep = !$omit_ignored;
			while ($problem = $rst->fetchRow()) {
				$ig = $problem['ignored'];
				if (($ig == null && $keep)
				  ||($ig != null && strpos($ig,$code) !== false)) {
					$rst2 = $db->Execute($query, array($problem['p1id'], $problem['p2id']));
					if ($rst2) {
						$first = $rst2->fetchRow();
						$second = $rst2->fetchRow();
						$rst2->Close();
						if ($first && $second) {
							$alert = array();
							$alert['group'] = 'descriptions';
							$alert['active'] = $first['active'].','.$second['active'];
							$alert['pages'] = array($first['content_name'], $second['content_name']);
							$alert['message'] = $mod->Lang('duplicate_descriptions');
							$alert['ignored'] = $problem['ignored']; //CHECKME both?
							$alert['links_data'][$first['content_id']] = array($first['content_name'], $code);
							$alert['links_data'][$second['content_id']] = array($second['content_name'], $code);
							$alerts[] = $alert;
						}
					}
				}
			}
			$rst->Close();
		}
		// No author provided
		$meta = $db->GetAssoc('SELECT mname,value FROM '.$pre.'module_seotools_meta WHERE active=1');
		if (array_key_exists('meta_std_publisher',$meta) && !$meta['meta_std_publisher']) {
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $mod->Lang('provide_an_author');
			$alert['links'][] = self::getSeeLink($mod,4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		return $alerts;
	}

	public function getTabLink($indx, $label)
	{
		return '<a href="#" tabindx="'.$indx.'">'.$label.'</a>';
	}

	public function getNoticeAlerts(&$mod)
	{
		$alerts = array();
		// No standard metadata
		$db = cmsms()->GetDb();//CMSMS 1.8+
		if (!$db->GetOne('SELECT 1 FROM '.cms_db_prefix().'module_seotools_group WHERE gname=\'meta_std\' AND active=1')) {
			$alert = array();
			$alert['message'] = $mod->Lang('use_standard_meta');
			$alert['links'][] = self::getTabLink(4,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// Create a sitemap
		if (!$mod->GetPreference('create_sitemap',0)) {
			$alert = array();
			$alert['message'] = $mod->Lang('create_a_sitemap');
			$alert['links'][] = self::getTabLink(6,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		elseif (!$mod->GetPreference('push_sitemap',0)) {
		  // Automatically submit the sitemap
			$alert = array();
			$alert['message'] = $mod->Lang('automatically_upload_sitemap');
			$alert['links'][] = self::getTabLink(6,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// Create a robots.txt file
		if (!$mod->GetPreference('create_robots',0)) {
			$alert = array();
			$alert['message'] = $mod->Lang('create_robots');
			$alert['links'][] = self::getTabLink(6,$mod->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// Set a default image
		//TODO
		return $alerts;
	}

	public function getFixLink(&$mod, $sp, $id, $pagename = '')
	{
		$gCms = cmsms(); //CMSMS 1.8+
		if ($this->adminurl == null) {
			$config = $gCms->GetConfig();
			if (isset($config['admin_url'])) {
				$this->adminurl = $config['admin_url'];
			}
			else {
				$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
				$this->adminurl = $rooturl.'/'.$config['admin_dir'];
			}
		}
		if ($this->theme == null) {
			$this->theme = ($mod->before20) ?
				$gCms->get_variable('admintheme'):
				cms_utils::get_theme_object();
		}
		//mimic $mod->CreateTooltipLink
		if ($pagename) {
			$title = $mod->Lang('edit_page',$pagename);
		}
		else {
			$title = $mod->Lang('edit_page2');
		}
		$lnk = '<a href="'.$this->adminurl.'/editcontent.php?'.$mod->secstr.'='.$sp.'&content_id='.$id.
		'" class="admin-tooltip"><img src="'.
		$this->adminurl.'/themes/'.$this->theme->themeName.'/images/icons/system/edit.gif" class="systemicon" /><span>'.
		$title.'</span></a>';
		return $lnk;
	}
}

?>
