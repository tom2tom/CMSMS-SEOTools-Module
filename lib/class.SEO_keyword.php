<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_keyword
{
	private function _get_keywords($source, $sep = ' ', $minlength = 6) {
		if (!$source) {
			return array();
		}

		$source = preg_replace('/\{[^\}]+\}/sU', '', $source); //smarty tags gone
		$source = str_replace(PHP_EOL, ' ', strip_tags($source)); //html tags gone, newlines susbstituted
		foreach (array('-', '.', ',', '!', '?', ':', ';') as $ch) { //some non-word chars susbstituted
			if($ch != $sep)
				$source = str_replace($ch, ' ', $source);
		}
		$keywords = explode($sep, $source);
		foreach ($keywords as &$value) {
			$value = trim($value);
			if (strlen($value) < $minlength) { //TODO mb_strlen($value,$encoding)
				$value = '';
			}
			else {
				$value = htmlentities($value);
			}
		}
		unset($value);

		return array_filter($keywords,'strlen');
	}

	public function getKeywords($mod, $content_id = false, $content = null, $savedwords = false) {
		$gCms = cmsms(); //CMSMS 1.8+
		if ($content == null) {
			$content = $gCms->GetContentOperations()->LoadContentFromId ($content_id);
			if (!$content) {
				return array();
			}
		}
		elseif ($content_id == false) {
			$content_id = (int)$content->Id();
		}

		$db = $gCms->GetDb();
		$pre = cms_db_prefix();
		if ($savedwords === false) {
			$savedwords = $db->GetOne(
			 'SELECT keywords FROM '.$pre.'module_seotools WHERE content_id=?',
			 array($content_id));
		}

		// Hack to avoid lots of expensive roundtrips to server
		$intro = $mod->GetName().'_mapi_pref_';
		$query = 'SELECT sitepref_name,sitepref_value FROM '.$pre.
			'siteprefs WHERE sitepref_name LIKE \''.$intro.'%\'';
		$prefs = $db->GetAssoc($query);

		$wlen = (int)$prefs[$intro.'keyword_minlength']; if (!$wlen) $wlen = 6;
		$minwt = (int)$prefs[$intro.'keyword_minimum_weight']; if (!$minwt) $minwt = 7;
		$sep = $prefs[$intro.'keyword_separator']; if (!$sep) $sep = ',';

		$got_keywords = array();
		// Try for explicit stored keywords
		if ($savedwords) {
			$stored_keywords = $savedwords.$sep;
		}
		else {
			$stored_keywords = '';
		}

		$query = 'SELECT content FROM '.$pre.'content_props WHERE content_id=? AND prop_name=?';
		$block = $prefs[$intro.'keyword_block'];
		if ($block) {
			$propname = str_replace(' ','_',$block);
			$stored_keywords .= $db->GetOne($query,array($content_id,$propname));
		}
		if ($stored_keywords) {
			$stored_keywords = self::_get_keywords($stored_keywords,$sep,$wlen);
			if ($stored_keywords) {
				foreach($stored_keywords as $keyword) {
					$got_keywords[$keyword] = $minwt; //allow all except dup's
				}
			}
		}
		if (!$savedwords) {
			// Get keywords derived from page title, description and content
			$page_name = $content->Name();
			if ($page_name) {
				$wt = (int)$prefs[$intro.'keyword_title_weight']; if (!$wt) $wt = 6;
				$title_keywords = self::_get_keywords($page_name,' ',$wlen);
				foreach($title_keywords as $keyword) {
					if (!isset($got_keywords[$keyword])) {
						$got_keywords[$keyword] = 0;
					}
					$got_keywords[$keyword] += $wt;
				}
			}
			$block = $prefs[$intro.'description_block'];
			if ($block) {
				$propname = str_replace(' ','_', $block);
				$description = $db->GetOne($query,array($content_id,$propname));
				if ($description) {
					$wt = (int)$prefs[$intro.'keyword_description_weight']; if (!$wt) $wt = 4;
					$description_keywords = self::_get_keywords($description,' ',$wlen);
					foreach($description_keywords as $keyword) {
						if (!isset($got_keywords[$keyword])) {
							$got_keywords[$keyword] = 0;
						}
						$got_keywords[$keyword] += $wt;
					}
				}
			}
			$props = $content->Properties();
			$html = $props['content_en']; // Main content block
			if ($html) {
				$heads = '';
				for ($i = 1; $i < 7; $i++) {
					if (preg_match_all("/(<h{$i}.*>)(\w.*)(<\/h{$i}>)/isxmU",$html,$patterns)) {
						if ($heads) $heads .= ' ';
						$heads .= implode(' ',$patterns[2]);
					}
				}
				if ($heads) {
					$wt = (int)$prefs[$intro.'keyword_headline_weight']; if (!$wt) $wt = 2;
					$headline_keywords = self::_get_keywords($heads,' ',$wlen);
					foreach($headline_keywords as $keyword) {
						if (!isset($got_keywords[$keyword])) {
							$got_keywords[$keyword] = 0;
						}
						$got_keywords[$keyword] += $wt;
					}
				}
				$wt = (int)$prefs[$intro.'keyword_content_weight']; if (!$wt) $wt = 1;
				$content_keywords = self::_get_keywords($html,' ',$wlen);
				foreach($content_keywords as $keyword) {
					if (!isset($got_keywords[$keyword])) {
						$got_keywords[$keyword] = 0;
					}
					$got_keywords[$keyword] += $wt;
				}
			}

			$exclude_list = $prefs[$intro.'keyword_exclude'];
			foreach ($got_keywords as $key=>&$value) {
				if ($value < $minwt || ($exclude_list && strpos($key,$exclude_list) !== false)) { //TODO caseless mb scan
					unset($got_keywords[$key]);
				}
			}
			unset($value);
			arsort($got_keywords, SORT_NUMERIC);
			// Cache derived keywords (until next page-content-change)
			$query = 'UPDATE '.$pre.'module_seotools SET keywords=? WHERE content_id=?';
			$merged = implode($sep, array_keys($got_keywords));
			$db->Execute($query,array($merged,$content_id));
			$query = 'INSERT INTO '.$pre.
'module_seotools (content_id,keywords) SELECT ?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS (SELECT 1 FROM '.
			$pre.'module_seotools T WHERE T.content_id=?)';
			$db->Execute($query,array($content_id,$merged,$content_id));
		}
		else {
			$exclude_list = $prefs[$intro.'keyword_exclude'];
			foreach ($got_keywords as $key=>&$value) {
				if ($value < $minwt || ($exclude_list && strpos($key,$exclude_list) !== false)) { //TODO caseless mb scan
					$value = '';
				}
			}
			unset($value);
		}

		return array_keys(array_filter($got_keywords,'is_numeric'));
	}
}

?>
