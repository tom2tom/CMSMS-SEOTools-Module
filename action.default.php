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

// Keyword generator

$sep = $this->GetPreference('keyword_separator',' ');
$pref = $this->GetPreference('default_keywords','');
$smarty->assign('default_keywords',$pref);

$keywords = explode($sep,$pref);

$query = 'SELECT * FROM '.cms_db_prefix().'module_seotools WHERE content_id=?';
$page_row = $db->GetRow($query,array($page_id));

$funcs = new SEO_keyword();
$kw = (!empty($page_row['keywords'])) ? $page_row['keywords'] : ''; //NOT FALSE
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
if ($description == FALSE)
{
	$description_id = str_replace(' ','_',$this->GetPreference('description_block',''));
	$description = strip_tags($content->GetPropertyValue($description_id));
}
if ($description == FALSE && $this->GetPreference('description_auto_generate',FALSE))
{
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

// Page title
$pref = $this->GetPreference('title','{title} | {$sitename} - {seo_keywords}');
$title = str_replace('{title}',$page_name,$pref);
$title = str_replace('{seo_keywords}',$title_keywords,$title);
$title = $this->ProcessTemplateFromData($title);
if ($title) $out[] = '<title>'.$title.'</title>';

// META tags for content type etc.
$type = $this->GetPreference('content_type','html');
if ($type == '')
	$type = strtolower ($content->Markup());
if ($type)
	$out[] = '<meta http-equiv="Content-Type" content="text/'.$type.'; charset='.$config['default_encoding'].'" />';
else
	$out[] = '<meta charset="'.$config['default_encoding'].'" />';

if (!array_key_exists('indexable',$page_row) || $page_row['indexable'] == "1")
	$out[] = '<meta name="robots" content="index, follow" />';
else
	$out[] = '<meta name="robots" content="noindex" />';

$pref = $this->GetPreference('verification','');
if ($pref) $out[] = '<meta name="google-site-verification" content="'.$pref.'" />';

$pref = $this->GetPreference('meta_title','{title} | {$sitename}');
$meta_title = str_replace('{title}',$page_name,$pref);
$meta_title = str_replace('{seo_keywords}',$title_keywords,$meta_title);
$meta_title = $this->ProcessTemplateFromData($meta_title);

$lat = $this->GetPreference('meta_latitude','');
if($lat && strpos($lat,',') !== FALSE) {
	$lat = str_replace(',','.',$lat);
}
$long = $this->GetPreference('meta_longitude','');
if($long && strpos($long,',') !== FALSE) {
	$long = str_replace(',','.',$long);
}

// Standard META tags
if ($this->GetPreference('meta_standard',FALSE))
{
	if ($meta_title) $out[] = '<meta name="title" content="'.$meta_title.'" />';
	if ($description) $out[] = '<meta name="description" content="'.$description.'" />';
	if ($merged) $out[] = '<meta name="keywords" content="'.implode($sep, $merged).'" />';
	$out[] = '<meta name="date" content="'.date('Y-m-d\TH:i:sP',$content->GetCreationDate()).'" />';
	$out[] = '<meta name="lastupdate" content="'.$page_mdate.'" />';
	$out[] = '<meta name="revised" content="'.$page_mdate.'" />';
	$pref = $this->GetPreference('meta_publisher','');
	if ($pref) $out[] = '<meta name="author" content="'.$pref.'" />';
	$pref = $this->GetPreference('meta_location','');
	if ($pref) $out[] = '<meta name="geo.placename" content="'.$pref.'" />';
	$pref = $this->GetPreference('meta_region','');
	if ($pref) $out[] = '<meta name="geo.region" content="'.$pref.'" />';
	if (is_numeric($lat) && is_numeric($long)) {
		$out[] = '<meta name="geo.position" content="'.$lat.';'.$long.'" />';
		$out[] = '<meta name="ICBM" content="'.$lat.', '.$long.'" />';
	}
}

// DublinCore META tags
if ($this->GetPreference('meta_dublincore',FALSE))
{
	$out[] = '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />';
	$out[] = '<link rel="schema.DCTERMS" href="http://purl.org/dc/terms/" />';
	$out[] = '<meta name="DC.type" content="Text" scheme="DCTERMS.DCMIType" />';
	$out[] = '<meta name="DC.format" content="text/html" scheme="DCTERMS.IMT" />';
	$out[] = '<meta name="DC.relation" content="http://dublincore.org/" scheme="DCTERMS.URI" />';
	$pref = $this->GetPreference('meta_publisher','');
	if ($pref) $out[] = '<meta name="DC.publisher" content="'.$pref.'" />';
	$pref = $this->GetPreference('meta_contributor','');
	if ($pref) $out[] = '<meta name="DC.contributor" content="'.$pref.'" />';
	$lang = str_replace('_','-',get_site_preference('frontendlang','en'));
	$out[] = '<meta name="DC.language" content="'.$lang.'" scheme="DCTERMS.RFC3066" />';
	$pref = $this->GetPreference('meta_copyright','');
	if ($pref) $out[] = '<meta name="DC.rights" content="'.$pref.'" />';
	if ($meta_title) $out[] = '<meta name="DC.title" content="'.$meta_title.'" />';
	if ($description) $out[] = '<meta name="DC.description" content="'.$description.'" />';
	if ($merged) $out[] = '<meta name="DC.subject" content="'.implode($sep, $merged).'" />';
	$out[] = '<meta name="DC.date" content="'.$page_mdate.'" scheme="DCTERMS.W3CDTF" />';
	$out[] = '<meta name="DC.identifier" content="'.$page_url.'" scheme="DCTERMS.URI" />';
}

// OpenGraph META tags
if ($this->GetPreference('meta_opengraph',FALSE))
{
	$pref = $this->GetPreference('meta_opengraph_title','{title}');
	$opengraph_title = str_replace('{title}',$page_name,$pref);
	$opengraph_title = $this->ProcessTemplateFromData($opengraph_title);
	if ($opengraph_title) $out[] = '<meta property="og:title" content="'.$opengraph_title.'" />';
	if (!empty($page_row['ogtype'])) {
		$out[] = '<meta property="og:type" content="'.$page_row['ogtype'].'" />';
	}
	else {
		$pref = $this->GetPreference('meta_opengraph_type','');
		if ($pref) $out[] = '<meta property="og:type" content="'.$pref.'" />';
	}
	$out[] = '<meta property="og:url" content="'.$page_url.'" />';

	if ($page_image) {
		$image = $page_image;
	}
	else {
		$image = $this->GetPreference('meta_opengraph_image','');
	}
	if ($image) {
		$out[] = '<meta property="og:image" content="'.$root_url.'/uploads/images/'.$image.'" />';
	}
	$defname = get_site_preference('sitename','CMSMS Site');
	$out[] = '<meta property="og:site_name" content="'.$this->GetPreference('meta_opengraph_sitename',$defname).'" />';
	if ($description) $out[] = '<meta property="og:description" content="'.$description.'" />';
	$pref = $this->GetPreference('meta_opengraph_application',''); 
	if ($pref) {
		$out[] = '<meta property="fb:app_id" content="'.$pref.'" />';
	}
	else {
		$pref = $this->GetPreference('meta_opengraph_admins','');
		if ($pref) $out[] = '<meta property="fb:admins" content="'.$pref.'" />';
	}
	$pref = $this->GetPreference('meta_location','');
	if ($pref) $out[] = '<meta property="og:locality" content="'.$pref.'" />';
	$pref = $this->GetPreference('meta_region','');
	if ($pref) $out[] = '<meta property="og:region" content="'.$pref.'" />';
	$pref = $this->GetPreference('meta_latitude','');
	$long = $this->GetPreference('meta_longitude','');
	if (is_numeric($lat) && is_numeric($long)) {
		$out[] = '<meta property="og:latitude" content="'.$lat.'" />';
		$out[] = '<meta property="og:longitude" content="'.$long.'" />';
	}
}

$pref = $this->GetPreference('additional_meta_tags',''); 
if ($pref) {
	$out[] = $this->ProcessTemplateFromData($pref);
}

// Image-Link
if ($page_image) {
	$out[] = '<link rel="image_src" href="'.$root_url.'/uploads/images/'.$page_image.'" />';
}

if ($out) {
	echo implode(PHP_EOL,$out);
	echo PHP_EOL;
}

?>
