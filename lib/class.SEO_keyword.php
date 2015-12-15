<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
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
		
		$wlen = $mod->GetPreference('keyword_minlength',6);
		$minwt = $mod->GetPreference('keyword_minimum_weight',7);
		$sep = $mod->GetPreference('keyword_separator',' ');
		$propname = str_replace(' ','_',$mod->GetPreference('keyword_block',''));
		$query = 'SELECT content FROM '.$pre.'content_props WHERE content_id=? AND prop_name=?';

		$got_keywords = array();
		/* Try for explicit stored keywords */
		if ($savedwords) {
			$stored_keywords = $savedwords.$sep;
		}
		else {
			$stored_keywords = '';
		}
		$stored_keywords .= $db->GetOne($query,array($content_id,$propname));
		if ($stored_keywords) {
			$stored_keywords = self::_get_keywords($stored_keywords,$sep,$wlen);
			if ($stored_keywords) {
				foreach($stored_keywords as $keyword) {
					$got_keywords[$keyword] = $minwt; //allow all except dup's
				}
			}
		}
		if (!$got_keywords) {
			/* Revert to keywords derived from page title, description and content */
			$page_name = $content->Name();
			if ($page_name) {
				$wt = $mod->GetPreference('keyword_title_weight',6);
				$title_keywords = self::_get_keywords($page_name,' ',$wlen);
				foreach($title_keywords as $keyword) {
					if (!isset($got_keywords[$keyword])) {
						$got_keywords[$keyword] = 0;
					}
					$got_keywords[$keyword] += $wt;
				}
			}
			$propname = str_replace(' ','_',$mod->GetPreference('description_block',''));
			$description = $db->GetOne($query,array($content_id,$propname));
			if ($description) {
				$wt = $mod->GetPreference('keyword_description_weight',4);
				$description_keywords = self::_get_keywords($description,' ',$wlen);
				foreach($description_keywords as $keyword) {
					if (!isset($got_keywords[$keyword])) {
						$got_keywords[$keyword] = 0;
					}
					$got_keywords[$keyword] += $wt;
				}
			}
			$content = $db->GetOne($query,array($content_id,'content_en'));
			if ($content) {
				$heads = '';
				for ($i = 1; $i < 7; $i++) {
					if (preg_match_all("/(<h{$i}.*>)(\w.*)(<\/h{$i}>)/isxmU",$content,$patterns)) {
						if ($heads) $heads .= ' ';
						$heads .= implode(' ',$patterns[2]);
					}
				}
				if ($heads) {
					$wt = $mod->GetPreference('keyword_headline_weight',2);
					$headline_keywords = self::_get_keywords($heads,' ',$wlen);
					foreach($headline_keywords as $keyword) {
						if (!isset($got_keywords[$keyword])) {
							$got_keywords[$keyword] = 0;
						}
						$got_keywords[$keyword] += $wt;
					}
				}
				$wt = $mod->GetPreference('keyword_content_weight',1);
				$content_keywords = self::_get_keywords($content,' ',$wlen);
				foreach($content_keywords as $keyword) {
					if (!isset($got_keywords[$keyword])) {
						$got_keywords[$keyword] = 0;
					}
					$got_keywords[$keyword] += $wt;
				}
			}
			arsort($got_keywords);
			//cache derived keywords (until next page-content-change)
			$query = 'UPDATE '.$pre.'module_seotools SET keywords=? WHERE content_id=?';
			$merged = implode($sep,array_keys($got_keywords));
			$db->Execute($query,array($merged,$content_id));
		}

		$exclude_list = $mod->GetPreference('keyword_exclude','');
		foreach ($got_keywords as $key=>&$value) {
			if ($value < $minwt || ($exclude_list && strpos($key,$exclude_list) !== false)) { //TODO caseless mb scan
				$value = '';
			}
		}
		unset($value);

		return array_keys(array_filter($got_keywords,'is_numeric'));
	}
}

?>
