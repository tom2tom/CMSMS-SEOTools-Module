<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Creates the SEO content for each page that has a {SEOTools} tag

// Get page data
//{CMSMS 1.6,1.7,1.8}::index.php if(...) $smarty->assign('content_obj',$contentobj);
/*$content = $smarty->get_template_vars('content_obj');
if (!$content) {
*/
	$content = cms_utils::get_current_content(); //CMSMS 1.9+
//}
if (!$content) {
	return;
}
$page_id = (int)$content->Id();
$page_name = $content->Name();
$page_url = $content->GetURL();
$page_mdate = date('Y-m-d\TH:i:sP',$content->GetModifiedDate());
$page_image = $content->GetPropertyValue('image');
if ($page_image == -1) {
	$page_image = '';
}

$out = array();

$pre = cms_db_prefix();

// Keyword generator

$sep = $this->GetPreference('keyword_separator',' ');
$pref = $this->GetPreference('keyword_default','');
$smarty->assign('default_keywords',$pref);

$keywords = explode($sep,$pref);

$query = 'SELECT * FROM '.$pre.'module_seotools WHERE content_id=?';
$page_row = $db->GetRow($query,array($page_id));

$funcs = new SEO_keyword();
$kw = (!empty($page_row['keywords'])) ? $page_row['keywords'] : ''; //NOT false
$other_keywords = $funcs->getKeywords($this, $page_id, $content, $kw);
$smarty->assign('page_keywords', implode($sep, $other_keywords));

$merged = array_unique(array_merge($keywords, $other_keywords));
foreach ($merged as $i => $val) {
	if ($val == '') unset ($merged[$i]);
}
$smarty->assign('seo_keywords', implode($sep, $merged)); //CHECKME was always comma-separator
$title_keywords = implode(' ',$merged);
$smarty->assign('title_keywords', $title_keywords); //never a comma-separator

// Page description

$description = $smarty->get_template_vars('page_description'); //dynamic content
if (!$description) {
	$description_id = str_replace(' ','_',$this->GetPreference('description_block',''));
	$description = strip_tags($content->GetPropertyValue($description_id));
}
if (!$description && $this->GetPreference('description_auto_generate',false)) {
	$description = str_replace('{title}',$page_name,$description);
	if (count($other_keywords) > 1) {
		$kw = $other_keywords;
		$last_keyword = array_pop($kw);
		$keywords = $this->Lang('and',implode(',',$kw),$last_keyword);
	}
	else {
		$keywords = reset($other_keywords);
	}
	$description = str_replace('{keywords}',$keywords,$this->GetPreference('description_auto',''));
	$description = $this->ProcessTemplateFromData($description);
}

$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];

// Show base?
if (empty($params['showbase']) || strcasecmp($params['showbase'],'false') != 0)
	$out[] = '<base href="'.$rooturl.'/" />';

$coord = FALSE; //cache for first of lat. or long.

$query = 'SELECT mname,value,output,calc,smarty FROM '.$pre.'module_seotools_meta M
LEFT JOIN '.$pre.'module_seotools_group G ON M.group_id = G.group_id
WHERE G.active=1 AND M.active=1
ORDER BY G.vieworder,M.vieworder';
$rows = $db->GetAssoc($query);

foreach ($rows as $name=>&$one) {
	$val = $one['value'];
	if ($one['calc']) {
		switch ($name) {
		 case 'content_type':
			if (!$val) {
				$val = strtolower($content->Markup());
			}
			if ($val) {
				$out[] = '<meta http-equiv="Content-Type" content="text/'.$val.'; charset='.$config['default_encoding'].'" />';
			}
			else {
				$out[] = '<meta charset="'.$config['default_encoding'].'" />';
			}
			$val = 'UNUSED';
			break;
		 case 'indexable':
			if (!array_key_exists('indexable',$page_row) || $page_row['indexable'] == "1")
				$out[] = '<meta name="robots" content="index,follow" />';
			else
				$out[] = '<meta name="robots" content="noindex" />';
			$val = 'UNUSED';
			break;
		 case 'title':
		 case 'meta_twt_title':
			if (!$val) {
				$val = '{title} | {$sitename} - {seo_keywords}';
			}
			$val = str_replace(array('{title}','{seo_keywords}'),array($page_name,$title_keywords),$val);
			break;
		 case 'meta_std_title':
			if (!$val) {
				$val = '{title} | {$sitename}';
			}
			$val = str_replace(array('{title}','{seo_keywords}'),array($page_name,$title_keywords),$val);
			break;
		 case 'meta_std_description':
		 case 'meta_twt_description':
		 case 'meta_gplus_description':
			$val = $description;
			break;
		 case 'meta_std_keywords':
			if ($merged) {
				$val = implode($sep, $merged);
			}
			break;
		 case 'meta_std_latitude':
			if ($val && strpos($val,',') !== false) {
				$val = str_replace(',','.',$val);
			}
			if (is_numeric($val) && is_numeric($coord)) {
				$out[] = '<meta name="geo.position" content="'.$val.';'.$coord.'" />';
				$out[] = '<meta name="ICBM" content="'.$val.','.$coord.'" />';
			}
			elseif ($val && ($coord === false)) {
				$coord = $val;
			}
			$val = 'UNUSED';
			break;
		 case 'meta_std_longitude':
			if ($val && strpos($val,',') !== false) {
				$val = str_replace(',','.',$val);
			}
			if (is_numeric($val) && is_numeric($coord)) {
				$out[] = '<meta name="geo.position" content="'.$coord.';'.$val.'" />';
				$out[] = '<meta name="ICBM" content="'.$coord.','.$val.'" />';
			}
			elseif ($val && ($coord === false)) {
				$coord = $val;
			}
			$val = 'UNUSED';
			break;
		 case 'meta_std_date':
			$val = date('Y-m-d\TH:i:sP',$content->GetCreationDate());
			break;
		 case 'meta_std_lastdate':
		 case 'meta_std_revised':
			$val = $page_mdate;
			break;
		 case 'meta_dc':
		 //TODO don't hard-code these, but still suppport shared data ...
			$out[] = '<meta name="DC.identifier" content="'.$page_url.'" scheme="DCTERMS.URI" />';
			$val = (!empty($rows['meta_std_title'])) ? $rows['meta_std_title']['value'] : '';
			if ($val) $out[] = '<meta name="DC.title" content="'.$val.'" />';
			if ($description) $out[] = '<meta name="DC.description" content="'.$description.'" />';
			if ($merged) $out[] = '<meta name="DC.subject" content="'.implode($sep, $merged).'" />';
			$val = (!empty($rows['meta_std_publisher'])) ? $rows['meta_std_publisher']['value'] : '';
			$out[] = '<meta name="DC.date" content="'.$page_mdate.'" scheme="DCTERMS.W3CDTF" />';
			if ($val) $out[] = '<meta name="DC.publisher" content="'.$val.'" />';
			$val = (!empty($rows['meta_std_contributor'])) ? $rows['meta_std_contributor']['value'] : '';
			if ($val) $out[] = '<meta name="DC.contributor" content="'.$val.'" />';
			$val = str_replace('_','-',get_site_preference('frontendlang','en'));
			$out[] = '<meta name="DC.language" content="'.$val.'" scheme="DCTERMS.RFC3066" />';
			$val = (!empty($rows['meta_std_copyright'])) ? $rows['meta_std_copyright']['value'] : '';
			if ($val) $out[] = '<meta name="DC.rights" content="'.$val.'" />';
			$val = 'UNUSED';
			break;
		 case 'meta_og_url':
			 $val = $page_url;
			 break;
		 case 'meta_gplus_name': //TODO name or title
		 case 'meta_og_title':
			if (!$val) {
				$val = '{title}';
			}
			$val = str_replace('{title}',$page_name,$val);
			break;
		 case 'meta_og_type':
			if (!empty($page_row['ogtype'])) {
				$val = $page_row['ogtype']; //override CHECKME
			}
			break;
		 case 'meta_og_image':
		 case 'meta_gplus_image':
 			if (!$val) {
				$val = page_image;
			}
		 case 'meta_twt_image':
 			if ($val) {
				$val = $root_url.'/uploads/images/'.$val;
			}
			break;
		 case 'meta_additional':
		 //nothing here (yet?)
			break;
		}
	}
	if ($val && $val !== 'UNUSED') {
		if ($one['smarty']) {
			$val = $this->ProcessTemplateFromData($val);
		}
		if ($one['output'] && $one['output'] !== 'UNUSED') {
			$out[] = sprintf($one['output'], $val);
		}
		else {
			$out[] = $val;
		}
	}
}
unset($one);

// Image-Link
if ($page_image) {
	$out[] = '<link rel="image_src" href="'.$root_url.'/uploads/images/'.$page_image.'" />';
}

if ($out) {
	echo implode(PHP_EOL,$out);
//	echo PHP_EOL;
}

?>
