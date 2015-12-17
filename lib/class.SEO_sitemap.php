<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_sitemap
{
	/**
	createSitemap:
	@mod: reference to SEOTools module object
	@push: optional boolean, whether to force a push to searchers, default false
	If @push is false, the module's 'push_sitemap' preference will
	 determine whether to push.
	If robots.txt file exists already, it must be writable.
	Returns: boolean T/F indicating success
	*/
	public function createSitemap($mod, $push = false) {
		$gCms = cmsms(); //CMSMS 1.8+
		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		$query = 'SELECT content_id,hierarchy,default_content,modified_date FROM '.
			$pre.'content WHERE active=1 AND type!="errorpage" ORDER BY hierarchy';
		$rst = $db->Execute($query);
		if ($rst == false)
			return false;

		// Get sitemap file in root directory (whereever that actually is)
		$offs = strpos(__FILE__,'modules'.DIRECTORY_SEPARATOR.$mod->GetName());
		$fn = substr(__FILE__, 0, $offs).'sitemap.xml';
		$fp = @fopen($fn,'wb');
		if ($fp == false)
			return false;

		$config = $gCms->GetConfig();
		$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
		$addslash = ($config['url_rewriting'] != 'none' && empty($config['page_extension']));
		if ($addslash)//appending / to most urls, so google's walker won't ignore them
			$siteroot = $rooturl.'/'; //this page will already be slashed, so don't duplicate
		$query = 'SELECT indexable,priority FROM '.$pre.'module_seotools WHERE content_id=?';

		// Create sitemap
		fwrite($fp,<<<EOS
<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>

EOS
		);
		$co = $gCms->GetContentOperations();
		while ($page = $rst->fetchRow())
		{
			$content = $co->LoadContentFromId ($page['content_id']);
			if ($content) {
				$url = $content->GetURL();
				if (strpos($url, $rooturl) !== false)
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

EOS
						);
					}
				}
			}
		}
		$rst->Close();
		@fwrite($fp, '</urlset>'.PHP_EOL);
		@fclose($fp);

		if ($push || $mod->GetPreference('push_sitemap',0)) {
			return self::pushSitemap($rooturl);
		}
		else {
			return true;
		}
	}

	/**
	pushSitemap:
	Ping searchers google, bing/msn/yahoo, ask
	@rooturl: optional url of website root (where the robots file resides), default false
	Returns: boolean T/F indicating success
	*/
	public function pushSitemap($rooturl = false) {
		if (ini_get('allow_url_fopen')) {
			$pusher = 1;
		}
		elseif (function_exists('curl_version')) {
			$pusher = 2;
		}
		else {
			return false;
		}

		if(!$rooturl) {
			$config = cmsms()->GetConfig();
			$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
		}
		$url = urlencode($rooturl).'/sitemap.xml';

		$to = array(
			'http://www.google.com/webmasters/tools/ping?sitemap='.$url, // Google
			'http://www.bing.com/webmaster/ping.aspx?siteMap='.$url, // Bing/Yahoo
			'http://submissions.ask.com/ping?sitemap='.$url	// ASK
		);
		$ret = true;
		foreach ($to as $url) {
			if ($pusher == 1) {
				$ret = $ret && self::doOpen($url);
			}
			else {
				$ret = $ret && self::doCurl($url);
			}
		}
		return $ret;
	}

	private function doOpen($url) {
		$fp = @fopen($url,'rb');
		if ($fp) {
			@fclose($fp);
			return true;
		}
		return false;
	}

	private function doCurl($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return ($httpCode == "200");
	}
}

?>
