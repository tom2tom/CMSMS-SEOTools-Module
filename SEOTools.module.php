<?php
#-------------------------------------------------------------------------
# CMS Made Simple module: SEOTools
# Provides tools to help with Search Engine Optimization and check for
# suboptimal SEO-related things.
# Version: 1.6 Tom Phane
#-------------------------------------------------------------------------
# CMS Made Simple (C) 2005-2015 Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
# This module is free software; you can redistribute it and/or modify it
# under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This module is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
# Read it at http://www.gnu.org/licenses/licenses.html#AGPL
#-------------------------------------------------------------------------

//error_reporting(E_ALL);

class SEOTools extends CMSModule
{
	public $before20;
	public $pathstr;

	function __construct()
	{
		global $CMS_VERSION;
		parent::__construct();
		$this->before20 = (version_compare($CMS_VERSION,'2.0') < 0);
		$this->pathstr = constant('CMS_SECURE_PARAM_NAME');
		$this->RegisterModulePlugin(TRUE);
	}

	function GetName()
	{
		return 'SEOTools';
	}

	function GetFriendlyName()
	{
		return $this->Lang('friendlyname');
	}

	function GetVersion()
	{
		return '1.6';
	}

	function GetHelp()
	{
		return $this->Lang('help');
	}

	function GetAuthor()
	{
		return 'Henning Schaefer';
	}

	function GetAuthorEmail()
	{
		return 'henning.schaefer@gmail.com';
	}

	function GetChangeLog()
	{
		$fp = cms_join_path(dirname(__FILE__),'include','changelog.inc');
		return file_get_contents($fp);
	}

	function IsPluginModule()
	{
		return TRUE;
	}

	function HasAdmin()
	{
		return TRUE;
	}

	function GetAdminSection()
	{
		return 'content';
	}

	function GetAdminDescription()
	{
		return $this->Lang('admindescription');
	}

	function VisibleToAdminUser()
	{
		return $this->CheckPermission('Edit SEO Settings');
	}

	function CheckAccess($perm = 'Edit SEO Settings')
	{
		return $this->CheckPermission($perm);
	}

	function GetDependencies()
	{
		return array();
	}

	function MinimumCMSVersion()
	{
		return "1.6";
	}

/* 	function MaximumCMSVersion()
 	{
 	}
*/
	function InstallPostMessage()
	{
		return $this->Lang('postinstall');
	}

	function UninstallPostMessage()
	{
		return $this->Lang('postuninstall');
	}

	function UninstallPreMessage()
	{
		return $this->Lang('really_uninstall');
	}

	function GetHeaderHTML()
	{
		$url = $this->GetModuleURLPath();
		$incs = <<<EOS
<script type="text/javascript" src="{$url}/include/module-admin.js"></script>
<link rel="stylesheet" type="text/css" href="{$url}/include/module-admin.css" />

EOS;
		return $incs;
	}

	function GetNotificationOutput($priority = 2)
	{
		$alerts = $this->getUrgentAlerts(TRUE,TRUE);
		if($alerts)
		{
			$obj = new StdClass;
			$obj->priority = $priority;
			$obj->html = $this->Lang('problem_alert',$this->CreateLink(null, 'defaultadmin', '', $this->Lang('problem_link_title')));

			return $obj;
		}
		return FALSE;
	}

	//for CMSMS < 1.10
	function SetParameters()
	{
		$this->InitializeAdmin();
		$this->InitializeFrontend();
	}

	//for CMSMS >= 1.10
	function InitializeAdmin()
	{
		$this->AddEventHandler('Core','ContentEditPost',FALSE);
		$this->AddEventHandler('Core','ContentDeletePost',FALSE);
		$this->CreateParameter('showbase','true',$this->Lang('help_showbase'));
	}

	//for CMSMS >= 1.10
	function LazyLoadAdmin()
	{
		return FALSE;
	}

	//for CMSMS >= 1.10
	function InitializeFrontend()
	{
		$this->RestrictUnknownParams();
		$this->SetParameterType('showbase',CLEAN_STRING);
	}

	//for CMSMS >= 1.10
	function LazyLoadFrontend()
	{
		return FALSE;
	}

	function DoEvent($origin, $name, &$params)
	{
		if ($this->GetPreference('create_sitemap',0))
		{
			$funcs = new SEO_file();
			$funcs->createSitemap($this);
		}
		//take this opportunity to clean up
		$db = cmsms()->GetDb();
		$query = "DELETE FROM ".cms_db_prefix()."module_seotools
WHERE (indexable=1 AND keywords IS NULL AND priority IS NULL AND ogtype IS NULL AND ignored IS NULL)";
		$db->Execute($query);
	}

	function DisplayErrorPage($message)
	{
		$smarty = cmsms()->GetSmarty();
		$smarty->assign('title_error', $this->Lang('error'));
		$smarty->assign('message', $message);
		// Display the populated template
		echo $this->ProcessTemplate('error.tpl');
	}

	function getSeeLink($index, $pagename = '')
	{
		$gCms = cmsms();
		$config = $gCms->GetConfig();
		if (isset ($config['admin_url']))
			$adminurl = $config['admin_url'];
		else
			$adminurl = $config['root_url'].'/'.$config['admin_dir'];
		$theme = ($this->before20) ? $gCms->get_variable('admintheme'):
			cms_utils::get_theme_object();
		$lnk = '<a class=@"'.$index.'" href="#"><img src="'.$adminurl.'/themes/'
		 .$theme->themeName.'/images/icons/system/edit.gif"';
		if ($pagename) {
			$lnk .= ' title = "'.$pagename.'"';
		}
		$lnk .= ' style="vertical-align: middle;" /></a>';
		return $lnk;
	}

	function getUrgentAlerts($omit_inactive = FALSE, $omit_ignored = FALSE)
	{
		$gCms = cmsms();
		$alerts = array();
		// No Meta tags are inserted
		if (!($this->GetPreference('meta_standard',FALSE) || $this->GetPreference('meta_dublincore',FALSE)))
		{
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $this->Lang('use_standard_or_dublincore_meta');
			$alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		if (!$this->GetPreference('description_auto_generate',FALSE))
		{
			if ($this->GetPreference('description_block','') != '') {
			  // Content pages without description
			  $query = "SELECT C.content_id, C.content_name, C.type, C.active, S.ignored FROM "
			  .$pre."content C LEFT JOIN "
			  .$pre."content_props P ON C.content_id = P.content_id LEFT JOIN "
			  .$pre."module_seotools S ON C.content_id = S.content_id WHERE ";
  			  if ($omit_inactive) {
			  	$query .= "C.active=1 AND";
			  }
			  $query .= "C.type LIKE ? AND P.prop_name=? AND (P.content IS NULL OR P.content=?)";
			  $parms = array('content%'); //can't be an injection risk here
			  $parms[] = str_replace(' ','_',$this->GetPreference('description_block',''));
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
					$alert['message'] = $this->Lang('meta_description_missing');
					$alert['ignored'] = $problem['ignored'];
					$alert['links_data'][$problem['content_id']] = array($problem['content_name'],$code);
					$alerts[] = $alert;
				  }
				}
			  }
			}else{
			  $alert = array();
			  $alert['group'] = 'settings';
			  $alert['message'] = $this->Lang('set_up_description_block');
			  $alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			  $alerts[] = $alert;
			}
		}elseif(strpos($this->GetPreference('description_auto',''),'{keywords}') === FALSE) {
			$alert = array();
			$alert['group'] = 'settings';
			$alert['message'] = $this->Lang('set_up_auto_description');
			$alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			$alerts[] = $alert;
		}

		$config = $gCms->GetConfig();
		// sitemap.xml not writeable
		if ($this->GetPreference('create_sitemap',0)) {
		  $path = cms_join_path($config['root_path'],'sitemap.xml');
		  if (file_exists($path) && !is_writeable($path)) {
		  	$alert = array();
		  	$alert['group'] = 'system';
			$alert['message'] = $this->Lang('sitemap_not_writeable');
			$alert['links'][] = $this->Lang('chmod_sitemap');
			$alerts[] = $alert;
		  }
		}

		// robots.txt not writeable
	   if ($this->GetPreference('create_robots',0)) {
	   	  $path = cms_join_path($config['root_path'],'robots.txt');
		  if (file_exists($path) && !is_writeable($path)) {
			$alert = array();
			$alert['group'] = 'system';
			$alert['message'] = $this->Lang('robots_not_writeable');
			$alert['links'][] = $this->Lang('chmod_robots');
			$alerts[] = $alert;
		  }
		}

	   if ($this->GetPreference('meta_opengraph',FALSE)) {
		// No OpenGraph admin set
		if (($this->GetPreference('meta_opengraph_admins','') == '') && ($this->GetPreference('meta_opengraph_application','') == '')) {
			$alert = array();
			$alert['group'] = 'opengraph';
			$alert['message'] = $this->Lang('no_opengraph_admins');
			$alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// No OpenGraph page type set
		if ($this->GetPreference('meta_opengraph_type','') == '') {
			$alert = array();
			$alert['group'] = 'opengraph';
			$alert['message'] = $this->Lang('no_opengraph_type');
			$alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// No OpenGraph sitename set
		if ($this->GetPreference('meta_opengraph_sitename','') == '') {
			$alert = array();
			$alert['group'] = 'opengraph';
			$alert['message'] = $this->Lang('no_opengraph_sitename');
			$alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			$alerts[] = $alert;
		}
		// No OpenGraph image set
		if ($this->GetPreference('meta_opengraph_image','') == '') {
			$alert = array();
			$alert['group'] = 'opengraph';
			$alert['message'] = $this->Lang('no_opengraph_image');
			$alert['links'][] = $this->getSeeLink (4,$this->Lang('visit_settings'));
			$alerts[] = $alert;
		}
	   }

		return $alerts;
	}
}

?>
