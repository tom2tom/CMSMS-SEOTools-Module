<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_robot
{
	/**
	createRobotsTXT:
	@mod: reference to SEOTools module object
	Returns: boolean T/F indicating success
	*/
	public function createRobotsTXT(&$mod)
	{
		// Get robots file in root directory (whereever that actually is)
		$offs = strpos(__FILE__,'modules'.DIRECTORY_SEPARATOR.$mod->GetName());
		$fn = substr(__FILE__, 0, $offs).'robots.txt';
		$fp = @fopen($fn,'wb');
		if ($fp == false)
			return false;

		$gCms = cmsms(); //CMSMS 1.8+
		$config = $gCms->GetConfig();
		$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];

		$outs = array();
		if ($mod->GetPreference('create_sitemap',0))
			$outs[] = 'Sitemap: '.$rooturl.'/sitemap.xml';

		$xtra = $mod->GetPreference('robot_start','');
		if ($xtra) {
			$outs[] = $xtra;
		}

		$outs[] = 'User-agent: *';
		foreach (array('contrib','doc','lib','modules','plugins','scripts','tmp') as $dir) {
			$outs[] = 'Disallow: '.$rooturl.'/'.$dir.'/';
		}

		$db = $gCms->GetDb();
		$query = 'SELECT content_id FROM '.cms_db_prefix().'module_seotools WHERE indexable=0 ORDER BY content_id';
		$result = $db->GetCol($query);
		if ($result) {
			$co = $gCms->GetContentOperations();
			foreach ($result as $cid) {
				$content = $co->LoadContentFromId($cid);
				if ($content) {
					$url = $content->GetURL();
					if ($url) {
						$outs[] = 'Disallow: '.$url;
					}
				}
			}
		}

		$xtra = $mod->GetPreference('robot_end','');
		if ($xtra) {
			$outs[] = $xtra;
		}

		@fwrite($fp,implode(PHP_EOL,$outs));
		@fwrite($fp,PHP_EOL);
		@fclose($fp);
		return true;
	}
}

?>
