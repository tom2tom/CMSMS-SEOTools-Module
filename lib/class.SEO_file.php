<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_file
{

	public function createRobotsTXT($mod)
	{
		$gCms = cmsms();
		$db = $gCms->GetDb();
		$query = "SELECT content_id FROM ".cms_db_prefix()."module_seotools WHERE indexable = 0";
		$result = $db->Execute($query);
		if ($result == FALSE)
			return FALSE;

		$config = $gCms->GetConfig();
		$fp = @fopen(cms_join_path($config['root_path'],'robots.txt'),'wb');
		if ($fp == FALSE)
			return FALSE;

		if ($mod->GetPreference('create_sitemap',0))
			@fwrite($fp, "Sitemap: {$config['root_url']}/sitemap.xml\n");

		@fwrite($fp, "User-agent: *\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/contrib/\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/doc/\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/lib/\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/modules/\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/plugins/\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/scripts/\n");
		@fwrite($fp, "Disallow: {$config['root_url']}/tmp/\n");

		$co = $gCms->GetContentOperations();
		while ($page = $result->fetchRow()) {
			$curcontent = $co->LoadContentFromId ($page['content_id']);
			if ($curcontent) {
				$url = $curcontent->GetURL();
				if ($url) {
				  @fwrite($fp, "Disallow: $url\n");
				}
			}
		}

		@fclose($fp);
		return TRUE;
	}

	public function createSitemap($mod)
	{
		$gCms = cmsms();
		$db = $gCms->GetDb();
		$query = "SELECT * FROM ".cms_db_prefix()."content WHERE active=1 ORDER BY hierarchy ASC";
		$result = $db->Execute($query);
		if ($result == FALSE)
			return FALSE;

		$config = $gCms->GetConfig();
		$fp = @fopen(cms_join_path($config['root_path'],'sitemap.xml'),'wb');
		if ($fp == FALSE)
			return FALSE;

		$addslash = ($config['url_rewriting'] != 'none'
		 && (!isset ($config['page_extension']) || $config['page_extension'] == ''));
		if ($addslash)//appending / to most urls, so google's walker won't ignore them
			$root = $config['root_url'].'/'; //this page will already be slashed, so don't duplicate
		$query = "SELECT * FROM ".cms_db_prefix()."module_seotools WHERE content_id=?";

		//Create sitemap
		fwrite($fp, "<?xml version='1.0' encoding='UTF-8'?>\n");
		fwrite($fp, "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n\n");

		$co = $gCms->GetContentOperations();
		while ($page = $result->fetchRow())
		{
			$curcontent = $co->LoadContentFromId ($page['content_id']);
			if ($curcontent) {
				$url = $curcontent->GetURL();
				if (strpos($url, $config['root_url']) !== FALSE)
				{
					$info = $db->GetRow($query,array($page['content_id']));
					if (!isset($info['indexable']) || ($info['indexable'] == "") || ($info['indexable'] == 1)) {

						if (isset($info['priority']) && ($info['priority'])) {
							$priority = (int)$info['priority'];
						}
						elseif ($page['default_content'] == 1) {
							$priority = 100;
						}
						else {
							$priority = 80;
							for ($i = 0; $i < substr_count($page['hierarchy'],'.'); $i++) {
								$priority  = $priority / 2;
							}
						}

						if ($addslash && $url != $root) $url .= '/';
						fwrite($fp, "<url>\n");
						fwrite($fp, "<loc>$url</loc>\n");
						fwrite($fp, "<lastmod>".date("Y-m-d", strtotime($page['modified_date']))."</lastmod>\n");
						fwrite($fp, "<changefreq>always</changefreq>\n");
						fwrite($fp, "<priority>".number_format($priority / 100, 1)."</priority>\n");
						fwrite($fp, "</url>\n");
					}
					unset ($info);
				}
			}
		}
		fwrite($fp, "\n</urlset>");
		fclose($fp);
		if ($mod->GetPreference('push_sitemap',0))
		{
			// Push sitemap to google
			$fp = @fopen("http://www.google.com/webmasters/tools/ping?sitemap=".urlencode($config['root_url']."/sitemap.xml"),"rb");
			if ($fp) fclose($fp);
		}
		return TRUE;
	}

}

?>
