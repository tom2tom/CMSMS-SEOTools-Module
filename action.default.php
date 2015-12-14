<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Creates the SEO content for each page that has a {SEOTools} tag

// Get page data
//{CMSMS 1.6,1.7,1.8}::index.php if(...) $smarty->assign('content_obj',$contentobj);
//hence $content = $smarty->get_template_vars('content_obj');
$content = cms_utils::get_current_content(); //CMSMS 1.9+
$page_id = (int)$content->Id();
$page_name = $content->Name();
$page_url = $content->GetURL();
$page_mdate = date('Y-m-d\TH:i:sP',$content->GetModifiedDate());
$page_image = $content->GetPropertyValue('image');
if ($page_image == -1) $page_image = '';

// Keyword generator

$sep = $this->GetPreference('keyword_separator',' ');
$pref = $this->GetPreference('default_keywords','');
$smarty->assign('default_keywords',$pref);

$keywords = explode($sep,$pref);

$query = 'SELECT * FROM '.cms_db_prefix().'module_seotools WHERE content_id=?';
$page_info = $db->GetRow($query,array($page_id));
if (!empty($page_info['keywords']))
	$other_keywords = explode($sep,$page_info['keywords']);
else
{
	$funcs = new SEO_keyword();
	$other_keywords = $funcs->getKeywordSuggestions($this,$page_id,$content);
}
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

// Show base?
if (!empty($params['showbase']) && strcasecmp($params['showbase'],'false') != 0)
	echo '<base href="'.$config['root_url'].'/" />'."\n";

// Page title
$pref = $this->GetPreference('title','{title} | {$sitename} - {seo_keywords}');
$title = str_replace('{title}',$page_name,$pref);
$title = str_replace('{seo_keywords}',$title_keywords,$title);
$title = $this->ProcessTemplateFromData($title);
if ($title) echo '<title>'.$title.'</title>'."\n";

// META tags for content type etc.
$type = $this->GetPreference('content_type','html');
if ($type == '')
	$type = strtolower ($content->Markup());
if ($type)
	echo '<meta http-equiv="Content-Type" content="text/'.$type.'; charset='.$config['default_encoding'].'" />'."\n";
else
	echo '<meta charset="'.$config['default_encoding'].'" />'."\n";

if (!array_key_exists('indexable',$page_info) || $page_info['indexable'] == "1")
	echo '<meta name="robots" content="index, follow" />'."\n";
else
	echo '<meta name="robots" content="noindex" />'."\n";

$pref = $this->GetPreference('verification','');
if ($pref) echo '<meta name="google-site-verification" content="'.$pref.'" />'."\n";

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
	if ($meta_title) echo '<meta name="title" content="'.$meta_title.'" />'."\n";
	if ($description) echo '<meta name="description" content="'.$description.'" />'."\n";
	if ($merged) echo '<meta name="keywords" content="'.implode($sep, $merged).'" />'."\n";
	echo '<meta name="date" content="'.date('Y-m-d\TH:i:sP',$content->GetCreationDate()).'" />'."\n";
	echo '<meta name="lastupdate" content="'.$page_mdate.'" />'."\n";
	echo '<meta name="revised" content="'.$page_mdate.'" />'."\n";
	$pref = $this->GetPreference('meta_publisher','');
	if ($pref) echo '<meta name="author" content="'.$pref.'" />'."\n";
	$pref = $this->GetPreference('meta_location','');
	if ($pref) echo '<meta name="geo.placename" content="'.$pref.'" />'."\n";
	$pref = $this->GetPreference('meta_region','');
	if ($pref) echo '<meta name="geo.region" content="'.$pref.'" />'."\n";
	if (is_numeric($lat) && is_numeric($long)) {
		echo '<meta name="geo.position" content="'.$lat.';'.$long.'" />'."\n";
		echo '<meta name="ICBM" content="'.$lat.', '.$long.'" />'."\n";
	}
}

// DublinCore META tags
if ($this->GetPreference('meta_dublincore',FALSE))
{
	echo '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />'."\n";
	echo '<link rel="schema.DCTERMS" href="http://purl.org/dc/terms/" />'."\n";
	echo '<meta name="DC.type" content="Text" scheme="DCTERMS.DCMIType" />'."\n";
	echo '<meta name="DC.format" content="text/html" scheme="DCTERMS.IMT" />'."\n";
	echo '<meta name="DC.relation" content="http://dublincore.org/" scheme="DCTERMS.URI" />'."\n";
	$pref = $this->GetPreference('meta_publisher','');
	if ($pref) echo '<meta name="DC.publisher" content="'.$pref.'" />'."\n";
	$pref = $this->GetPreference('meta_contributor','');
	if ($pref) echo '<meta name="DC.contributor" content="'.$pref.'" />'."\n";
	$lang = str_replace('_','-',get_site_preference('frontendlang','en'));
	echo '<meta name="DC.language" content="'.$lang.'" scheme="DCTERMS.RFC3066" />'."\n";
	$pref = $this->GetPreference('meta_copyright','');
	if ($pref) echo '<meta name="DC.rights" content="'.$pref.'" />'."\n";
	if ($meta_title) echo '<meta name="DC.title" content="'.$meta_title.'" />'."\n";
	if ($description) echo '<meta name="DC.description" content="'.$description.'" />'."\n";
	echo '<meta name="DC.date" content="'.$page_mdate.'" scheme="DCTERMS.W3CDTF" />'."\n";
	echo '<meta name="DC.identifier" content="'.$page_url.'" scheme="DCTERMS.URI" />'."\n";
}

// OpenGraph META tags
if ($this->GetPreference('meta_opengraph',FALSE))
{
	$pref = $this->GetPreference('meta_opengraph_title','{title}');
	$opengraph_title = str_replace('{title}',$page_name,$pref);
	$opengraph_title = $this->ProcessTemplateFromData($opengraph_title);
	if ($opengraph_title) echo '<meta property="og:title" content="'.$opengraph_title.'" />'."\n";
	if ($page_info['ogtype']) {
		echo '<meta property="og:type" content="'.$page_info['ogtype'].'" />'."\n";
	}
	else {
		$pref = $this->GetPreference('meta_opengraph_type','');
		if ($pref) echo '<meta property="og:type" content="'.$pref.'" />'."\n";
	}
	echo '<meta property="og:url" content="'.$page_url.'" />'."\n";

	if ($page_image) {
		$image = $page_image;
	}
	else {
		$image = $this->GetPreference('meta_opengraph_image','');
	}
	if ($image) {
		echo '<meta property="og:image" content="'.$config['root_url'].'/uploads/images/'.$image.'" />'."\n";
	}
	$defname = get_site_preference('sitename','CMSMS Site');
	echo '<meta property="og:site_name" content="'.$this->GetPreference('meta_opengraph_sitename',$defname).'" />'."\n";
	if ($description) echo '<meta property="og:description" content="'.$description.'" />'."\n";
	$pref = $this->GetPreference('meta_opengraph_application',''); 
	if ($pref) {
		echo '<meta property="fb:app_id" content="'.$pref.'" />'."\n";
	}
	else {
		$pref = $this->GetPreference('meta_opengraph_admins','');
		if ($pref) echo '<meta property="fb:admins" content="'.$pref.'" />'."\n";
	}
	$pref = $this->GetPreference('meta_location','');
	if ($pref) echo '<meta property="og:locality" content="'.$pref.'" />'."\n";
	$pref = $this->GetPreference('meta_region','');
	if ($pref) echo '<meta property="og:region" content="'.$pref.'" />'."\n";
	$pref = $this->GetPreference('meta_latitude','');
	$long = $this->GetPreference('meta_longitude','');
	if (is_numeric($lat) && is_numeric($long)) {
		echo '<meta property="og:latitude" content="'.$lat.'" />'."\n";
		echo '<meta property="og:longitude" content="'.$long.'" />'."\n";
	}
}

$pref = $this->GetPreference('additional_meta_tags',''); 
if ($pref) {
	echo $this->ProcessTemplateFromData($pref);
	echo "\n";
}

// Image-Link
if ($page_image)
	echo '<link rel="image_src" href="'.$config['root_url'].'/uploads/images/'.$page_image.'" />'."\n";

?>
