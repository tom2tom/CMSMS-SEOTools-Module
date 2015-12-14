<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_keyword
{
	private function _get_keywords($source, $sep = ' ', $minlength = 6) {
		//TODO not utf8_decode() >> iconv()?
		$source = preg_replace('/\{[^\}]+\}/isU', '', utf8_decode($source));
		$source = str_replace("\n",' ',strip_tags($source));
		foreach (array('-','.',',','!','?',':',';') as $ch) {
			if($ch != $sep)
				$source = str_replace($ch,' ',$source);
		}
		$keywords = explode($sep,$source);
		foreach ($keywords as &$value) {
			$value = trim($value);
			if (strlen($value) < $minlength) {
				$value = '';
			}
			else {
				$value = htmlentities($value);
			}
		}
		unset($value);

		return array_filter($keywords,'strlen');
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

	public function getKeywordSuggestions($mod,$content_id = FALSE,$content = NULL) {
		$gCms = cmsms();
		if ($content == NULL) {
			$contentops = $gCms->GetContentOperations();
			if (!$content = $contentops->LoadContentFromId ($content_id)) {
				return '';
			}
		}
		elseif ($content_id == FALSE)
			$content_id = (int)$content->Id();

		$word_len = $mod->GetPreference('keyword_minlength',6);
		$word_minwt = $mod->GetPreference('keyword_minimum_weight',7);
		$propname = str_replace(' ','_',$mod->GetPreference('keyword_block',''));
		$db = $gCms->GetDb();
		$query = 'SELECT content FROM '.cms_db_prefix().'content_props WHERE content_id=? AND prop_name=?';

		$other_keywords = array();
		/* Try for explicit stored keywords */
		$stored_keywords = $db->GetOne($query,array($content_id,$propname));
		if ($stored_keywords) {
			$sep = $mod->GetPreference('keyword_separator',' ');
			$stored_keywords = self::_get_keywords($stored_keywords,$sep,$word_len);
			if ($stored_keywords) {
				foreach($stored_keywords as $keyword) {
					$other_keywords[$keyword] = $word_minwt;
				}
			}
		}
		if (!$other_keywords) {
			/* Generate keywords from page title, description and content */
			$page_name = $content->Name();
			if ($page_name) {
				$wt = $mod->GetPreference('keyword_title_weight',6);
				$title_keywords = self::_get_keywords($page_name,' ',$word_len);
				foreach($title_keywords as $keyword) {
					if (!isset($other_keywords[$keyword])) {
						$other_keywords[$keyword] = 0;
					}
					$other_keywords[$keyword] += $wt;
				}
			}
			$propname = str_replace(' ','_',$mod->GetPreference('description_block',''));
			$description = $db->GetOne($query,array($content_id,$propname));
			if ($description) {
				$wt = $mod->GetPreference('keyword_description_weight',4);
				$description_keywords = self::_get_keywords($description,' ',$word_len);
				foreach($description_keywords as $keyword) {
					if (!isset($other_keywords[$keyword])) {
						$other_keywords[$keyword] = 0;
					}
					$other_keywords[$keyword] += $wt;
				}
			}
			$content = $db->GetOne($query,array($content_id,'content_en'));
			if ($content) {
				$wt = $mod->GetPreference('keyword_headline_weight',2);
				$headline_keywords = self::_get_keywords(self::_get_headlines($content),' ',$word_len);
				foreach($headline_keywords as $keyword) {
					if (!isset($other_keywords[$keyword])) {
						$other_keywords[$keyword] = 0;
					}
					$other_keywords[$keyword] += $wt;
				}
				$wt = $mod->GetPreference('keyword_content_weight',1);
				$content_keywords = self::_get_keywords($content,' ',$word_len);
				foreach($content_keywords as $keyword) {
					if (!isset($other_keywords[$keyword])) {
						$other_keywords[$keyword] = 0;
					}
					$other_keywords[$keyword] += $wt;
				}
			}
			arsort($other_keywords);
		}

		//TODO not utf8_decode() >> iconv()?
		$exclude_list = utf8_decode($mod->GetPreference('keyword_exclude',''));
		foreach ($other_keywords as $key=>&$value) {
			if ($value < $word_minwt || ($exclude_list && strpos($key,$exclude_list) !== FALSE)) {
				$value = '';
			}
		}
		unset($value);

		return array_keys(array_filter($other_keywords,'is_numeric'));
	}
}

?>
