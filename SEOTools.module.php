<?php
#-------------------------------------------------------------------------
# CMS Made Simple module: SEOTools
# Provides tools to help with Search Engine Optimization and check for
# suboptimal SEO-related things.
# Version: 1.6 Tom Phane
#-------------------------------------------------------------------------
# CMS Made Simple (C) 2005-2016 Ted Kulp (wishy@cmsmadesimple.org)
# CMS Made Simple homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
# This module is free software; you can redistribute it and/or modify it
# under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This module is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
# Read it at http://www.gnu.org/licenses/licenses.html#AGPL
#-------------------------------------------------------------------------

class SEOTools extends CMSModule
{
	public $before20;
	public $secstr;

	function __construct()
	{
		global $CMS_VERSION;
		parent::__construct();
		$this->before20 = (version_compare($CMS_VERSION,'2.0') < 0);
		$this->secstr = constant('CMS_SECURE_PARAM_NAME');
		$this->RegisterModulePlugin(true);
	}

	function AllowAutoInstall()
	{
		return false;
	}

	function AllowAutoUpgrade()
	{
		return false;
	}

	//CMSMS 1.11+
	function AllowSmartyCaching()
	{
		return false; //need freshly-assigned meta (keywords etc) & exported vars
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
		return '1.7.1';
	}

	function GetHelp()
	{
		return $this->Lang('help');
	}

	function GetAuthor()
	{
		return 'tomphantoo';
	}

	function GetAuthorEmail()
	{
		return 'tpgww@onepost.net';
	}

	function GetChangeLog()
	{
		$fp = cms_join_path(dirname(__FILE__),'include','changelog.inc');
		return file_get_contents($fp);
	}

	function IsPluginModule()
	{
		return true;
	}

	function HasAdmin()
	{
		return true;
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
		return '1.9';
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
<link rel="stylesheet" type="text/css" href="{$url}/css/module-admin.css" />

EOS;
		return $incs;
	}

	//CMSMS 1.9-
	function SetParameters()
	{
		$this->InitializeAdmin();
		$this->InitializeFrontend();
	}

	//CMSMS 1.10+
	function InitializeAdmin()
	{
		$this->AddEventHandler('Core','ContentEditPost',false);
		$this->AddEventHandler('Core','ContentDeletePost',false);
		$this->CreateParameter('showbase','true',$this->Lang('help_showbase'));
	}

	//CMSMS 1.10+
	function LazyLoadAdmin()
	{
		return false; //capture events ASAP
	}

	//CMSMS 1.10+
	function InitializeFrontend()
	{
		$this->RestrictUnknownParams();
		$this->SetParameterType('showbase',CLEAN_STRING);
	}

	//CMSMS 1.10+
	function LazyLoadFrontend()
	{
		return false;
	}

	function DoEvent($origin,$name,&$params)
	{
		if ($this->GetPreference('create_sitemap',0))
		{
			$funcs = new SEO_sitemap();
			$funcs->createSitemap($this);
		}
		// Clean up
		$db = cmsms()->GetDb(); //CMSMS 1.8+
		$pre = cms_db_prefix();
		if ($name == 'ContentEditPost') {
			// Remove cached keywords, so next page-display will regenerate them
			$query = 'UPDATE '.$pre.'module_seotools SET keywords=NULL WHERE content_id=?';
		}
		else {
			// ContentDeletePost
			$query = 'DELETE FROM '.$pre.'module_seotools WHERE content_id=?';
		}
		$content = $params['content'];
		$id = $content->Id();
		$db->Execute($query,array($id));

		$query = 'DELETE FROM '.$pre.
'module_seotools WHERE (indexable=1 AND keywords IS NULL AND priority IS NULL AND ogtype IS NULL AND ignored IS NULL)';
		$db->Execute($query);
	}

	function DisplayErrorPage($message)
	{
		global $smarty;
		$smarty->assign('title_error', $this->Lang('error'));
		$smarty->assign('message', $message);
		// Display the populated template
		echo $this->ProcessTemplate('error.tpl');
	}

}

?>
