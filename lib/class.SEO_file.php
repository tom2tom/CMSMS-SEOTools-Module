<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_file
{
	public function createRobotsTXT($mod)
	{
		$gCms = cmsms(); //CMSMS 1.8+
		$config = $gCms->GetConfig();
		$fp = @fopen(cms_join_path($config['root_path'],'robots.txt'),'wb');
		if ($fp == FALSE)
			return FALSE;

		$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];

		$outs = array();
		if ($mod->GetPreference('create_sitemap',0))
			$outs[] = 'Sitemap: '.$rooturl.'/sitemap.xml';

		$xtra = $mod->GetPreference('r_before','');
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

		$xtra = $mod->GetPreference('r_after','');
		if ($xtra) {
			$outs[] = $xtra;
		}

		@fwrite($fp,implode("\n",$outs));
		@fwrite($fp,"\n");
		@fclose($fp);
		return TRUE;
	}

	public function createSitemap($mod)
	{
		$gCms = cmsms(); //CMSMS 1.8+
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		$query = 'SELECT content_id,hierarchy,default_content,modified_date FROM '.$pre.'content WHERE active=1 AND type!="errorpage" ORDER BY hierarchy';
		$result = $db->Execute($query);
		if ($result == FALSE)
			return FALSE;

		$config = $gCms->GetConfig();
		$fp = @fopen(cms_join_path($config['root_path'],'sitemap.xml'),'wb');
		if ($fp == FALSE)
			return FALSE;

		$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
		$addslash = ($config['url_rewriting'] != 'none' && empty($config['page_extension']));
		if ($addslash)//appending / to most urls, so google's walker won't ignore them
			$siteroot = $rooturl.'/'; //this page will already be slashed, so don't duplicate
		$query = 'SELECT indexable,priority FROM '.$pre.'module_seotools WHERE content_id=?';

		// Create sitemap
		fwrite($fp,<<<EOS
<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>

EOS		);
		$co = $gCms->GetContentOperations();
		while ($page = $result->fetchRow())
		{
			$content = $co->LoadContentFromId ($page['content_id']);
			if ($content) {
				$url = $content->GetURL();
				if (strpos($url, $rooturl) !== FALSE)
				{
					$info = $db->GetRow($query,array($page['content_id']));
					if (empty($info['indexable']) || $info['indexable'] == 1) {
						if ($addslash && $url != $siteroot) {
							$url .= '/';
						}
						$mdate = date('Y-m-d', strtotime($page['modified_date']));

						if (!empty($info['priority'])) {
							$priority = (int)$info['priority'];
						}
						elseif ($page['default_content'] == 1) {
							$priority = 100;
						}
						else {
							$priority = 80;
							$c = substr_count($page['hierarchy'],'.');
							for ($i = 0; $i < $c; $i++) {
								$priority /= 2;
							}
						}
						$priority = number_format($priority / 100, 1);

						@fwrite($fp,<<<EOS
<url>
<loc>$url</loc>
<lastmod>$mdate</lastmod>
<changefreq>always</changefreq>
<priority>$priority</priority>
</url>

EOS						);
					}
				}
			}
		}
		@fwrite($fp, "</urlset>\n");
		@fclose($fp);

		if ($mod->GetPreference('push_sitemap',0)) {
			$url = urlencode($rooturl.'/sitemap.xml');
			// Push to google
			$fp = @fopen('http://www.google.com/webmasters/tools/ping?sitemap='.$url,'rb');
			if ($fp) @fclose($fp);
			$ret = ($fp !== FALSE);
			// Push to bing/yahoo
			$fp = @fopen('http://www.bing.com/webmaster/ping.aspx?siteMap='.$url,'rb');
			if ($fp) @fclose($fp);
			$ret = $ret && ($fp !== FALSE);
			// Push to ask
			$fp = @fopen('http://submissions.ask.com/ping?sitemap='.$url,'rb');
			if ($fp) @fclose($fp);
			$ret = $ret && ($fp !== FALSE);
			return $ret;
		}
		else {
			return TRUE;
		}
	}
}

?>
