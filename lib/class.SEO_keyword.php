<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_keyword
{
	private function _get_keywords($source, $minlength = 6) {
		$source = preg_replace('/\{[^\}]+\}/isU', '', utf8_decode($source));
		$source = str_replace("\n"," ",strip_tags($source));
		$source = str_replace('-',' ',$source);
		$source = str_replace('.',' ',$source);
		$source = str_replace(',',' ',$source);
		$source = str_replace('!',' ',$source);
		$source = str_replace('?',' ',$source);
		$source = str_replace(':',' ',$source);
		$source = str_replace('	',' ',$source);
		$keywords = explode(' ',$source);
		foreach ($keywords as $key=>$value) {
			if (strlen($value) < $minlength) {
				unset($keywords[$key]);
			} else {
				$keywords[$key] = htmlentities(trim($value));
			}
		}
		return $keywords;
	}

	private function _get_headlines($file) {
		$h1tags = preg_match_all("/(<h1.*>)(\w.*)(<\/h1>)/isxmU",$file,$patterns);
		$content = "";
		foreach($patterns[2] as $tag) {
			$content .= " ".$tag;
		}
		$h2tags = preg_match_all("/(<h2.*>)(\w.*)(<\/h2>)/isxmU",$file,$patterns);
		foreach($patterns[2] as $tag) {
			$content .= " ".$tag;
		}
		$h3tags = preg_match_all("/(<h3.*>)(\w.*)(<\/h3>)/isxmU",$file,$patterns);
		foreach($patterns[2] as $tag) {
			$content .= " ".$tag;
		}
		$h4tags = preg_match_all("/(<h4.*>)(\w.*)(<\/h4>)/isxmU",$file,$patterns);
		foreach($patterns[2] as $tag) {
			$content .= " ".$tag;
		}
		$h5tags = preg_match_all("/(<h5.*>)(\w.*)(<\/h5>)/isxmU",$file,$patterns);
		foreach($patterns[2] as $tag) {
			$content .= " ".$tag;
		}
		$h6tags = preg_match_all("/(<h6.*>)(\w.*)(<\/h6>)/isxmU",$file,$patterns);
		foreach($patterns[2] as $tag) {
			$content .= " ".$tag;
		}
		return $content;
	}

	public function getKeywordSuggestions($content_id, $mod) {
		$gCms = cmsms();
		$contentops = $gCms->GetContentOperations();
		if (!$content = $contentops->LoadContentFromId ($content_id)) {
			return;
		}
		$page_name = $content->Name();
		$description_id = str_replace(' ','_',$mod->GetPreference('description_block',''));
		$db = $gCms->GetDb();
		$query = 'SELECT content FROM '.cms_db_prefix().'content_props WHERE content_id=? AND prop_name=?';

		/* Generate keywords from page title, description and content */
		$other_keywords = array();
		if($page_name) {
			$title_keywords = self::_get_keywords($page_name, $mod->GetPreference('keyword_minlength',6));
			foreach($title_keywords as $keyword) {
				if (!isset($other_keywords[$keyword])) {
					$other_keywords[$keyword] = 0;
				}
				$other_keywords[$keyword] = $mod->GetPreference('keyword_title_weight',6);
			}
		}

		$description = $db->GetOne($query,array($content_id,$description_id));
		if($description) {
			$description_keywords = self::_get_keywords($description, $mod->GetPreference('keyword_minlength',6));
			foreach($description_keywords as $keyword) {
				if (!isset($other_keywords[$keyword])) {
					$other_keywords[$keyword] = 0;
				}
				$other_keywords[$keyword] += $mod->GetPreference('keyword_description_weight',4);
			}
		}
		$content = $db->GetOne($query,array($content_id,'content_en'));
		if($content) {
			$headline_keywords = self::_get_keywords(self::_get_headlines($content), $mod->GetPreference('keyword_minlength',6));
			foreach($headline_keywords as $keyword) {
				if (!isset($other_keywords[$keyword])) {
					$other_keywords[$keyword] = 0;
				}
				$other_keywords[$keyword] += $mod->GetPreference('keyword_headline_weight',2);
			}
			$content_keywords = self::_get_keywords($content, $mod->GetPreference('keyword_minlength',6));
			foreach($content_keywords as $keyword) {
				if (!isset($other_keywords[$keyword])) {
					$other_keywords[$keyword] = 0;
				}
				$other_keywords[$keyword] += $mod->GetPreference('keyword_content_weight',1);
			}
		}
		arsort($other_keywords);

		$exclude_list = explode(' ',strtoupper(utf8_decode($mod->GetPreference('keyword_exclude',''))));

		foreach ($other_keywords as $key=>$value) {
			if ($value < $mod->GetPreference('keyword_minimum_weight',7)) {
				unset($other_keywords[$key]);
			} elseif (in_array(strtoupper($key),$exclude_list)) {
				unset($other_keywords[$key]);
			}
		}
		return $other_keywords;
	}
}

?>
