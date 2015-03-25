<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Creates the SEO content for each page

// Get page data

$content = $smarty->get_template_vars('content_obj');
$page_id = (int)$content->Id();
$page_name = $content->Name();
$page_url = $content->GetURL();
$page_mdate = date('Y-m-d\TH:i:sP',$content->GetModifiedDate());
$page_image = $content->GetPropertyValue('image');
if($page_image == -1) $page_image = '';

// Keyword generator

$sep = $this->GetPreference('keyword_separator',' ');
$def = $this->GetPreference('default_keywords','');
$smarty->assign('default_keywords',$def);

$keywords = explode($sep,$def);

$query = "SELECT * FROM ".cms_db_prefix()."module_seotools WHERE content_id=?";
$page_info = $db->GetRow($query,array($page_id));
if (array_key_exists('keywords',$page_info) && $page_info['keywords'] != '')
	$other_keywords = array_flip(explode($sep,$page_info['keywords']));
else
{
	$funcs = new SEO_keyword();
	$other_keywords = $funcs->getKeywordSuggestions($page_id,$this);
}
$smarty->assign('page_keywords', implode($sep, array_flip($other_keywords)));

$merged = array_unique(array_merge($keywords, array_flip($other_keywords)));
foreach ($merged as $i => $val) { if ($val == '') unset ($merged[$i]); }

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
	$kw = array_flip($other_keywords);
	$last_keyword = array_pop($kw);
	$keywords = implode(', ',$kw) . " " . $this->Lang('and') . " " . $last_keyword;
	$description = str_replace('{keywords}',$keywords,$this->GetPreference('description_auto',''));
	$description = $this->ProcessTemplateFromData($description);
}

// Show base?
if (!empty($params['showbase']) && strcasecmp($params['showbase'],'false') != 0)
	echo '<base href="'.$config['root_url'].'/" />'."\n";

// Page title
$title = $this->GetPreference('title','{title} | {$sitename} - {seo_keywords}');
$title = str_replace('{title}',$page_name,$title);
$title = str_replace('{seo_keywords}',$title_keywords,$title);
$title = $this->ProcessTemplateFromData($title);
echo '<title>'.$title.'</title>'."\n";

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

if ($this->GetPreference('verification','') != '')
	echo '<meta name="google-site-verification" content="'.$this->GetPreference('verification','').'" />'."\n";

$meta_title = $this->GetPreference('meta_title','{title} | {$sitename}');
$meta_title = str_replace('{title}',$page_name,$meta_title);
$meta_title = str_replace('{seo_keywords}',$title_keywords,$meta_title);
$meta_title = $this->ProcessTemplateFromData($meta_title);

// Standard META tags
if ($this->GetPreference('meta_standard',FALSE))
{
	echo '<meta name="title" content="'.$meta_title.'" />'."\n";
	echo '<meta name="description" content="'.$description.'" />'."\n";
    echo '<meta name="date" content="'.date('Y-m-d\TH:i:sP',$content->GetCreationDate()).'" />'."\n";
    echo '<meta name="lastupdate" content="'.$page_mdate.'" />'."\n";
    echo '<meta name="revised" content="'.$page_mdate.'" />'."\n";
    echo '<meta name="author" content="'.$this->GetPreference('meta_publisher','').'" />'."\n";
    if ($this->GetPreference('meta_location','') != "") {
      echo '<meta name="geo.placename" content="'.$this->GetPreference('meta_location','').'" />'."\n";
    }
    if ($this->GetPreference('meta_region','') != "") {
      echo '<meta name="geo.region" content="'.$this->GetPreference('meta_region','').'" />'."\n";
    }
    if (($this->GetPreference('meta_latitude','') != "") && ($this->GetPreference('meta_longitude','') != "")) {
      echo '<meta name="geo.position" content="'.$this->GetPreference('meta_latitude','').';'.$this->GetPreference('meta_longitude','').'" />'."\n";
      echo '<meta name="ICBM" content="'.$this->GetPreference('meta_latitude','').', '.$this->GetPreference('meta_longitude','').'" />'."\n";
    }
}

if ($this->GetPreference('additional_meta_tags','') != '')
	echo $this->ProcessTemplateFromData($this->GetPreference('additional_meta_tags',''));

$gCms = cmsms();

// DublinCore META tags
if ($this->GetPreference('meta_dublincore',FALSE))
{
    echo '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />'."\n";
    echo '<link rel="schema.DCTERMS" href="http://purl.org/dc/terms/" />'."\n";
    echo '<meta name="DC.type" content="Text" scheme="DCTERMS.DCMIType" />'."\n";
    echo '<meta name="DC.format" content="text/html" scheme="DCTERMS.IMT" />'."\n";
    echo '<meta name="DC.relation" content="http://dublincore.org/" scheme="DCTERMS.URI" />'."\n";
    echo '<meta name="DC.publisher" content="'.$this->GetPreference('meta_publisher','').'" />'."\n";
    echo '<meta name="DC.contributor" content="'.$this->GetPreference('meta_contributor','').'" />'."\n";
    echo '<meta name="DC.language" content="'.$gCms->siteprefs['frontendlang'].'" scheme="DCTERMS.RFC3066" />'."\n";
    echo '<meta name="DC.rights" content="'.$this->GetPreference('meta_copyright','').'" />'."\n";
    echo '<meta name="DC.title" content="'.$meta_title.'" />'."\n";
    echo '<meta name="DC.description" content="'.$description.'" />'."\n";
    echo '<meta name="DC.date" content="'.$page_mdate.'" scheme="DCTERMS.W3CDTF" />'."\n";
    echo '<meta name="DC.identifier" content="'.$page_url.'" scheme="DCTERMS.URI" />'."\n";
}

// OpenGraph META tags
if ($this->GetPreference('meta_opengraph',FALSE))
{
	$opengraph_title = $this->GetPreference('meta_opengraph_title','{title}');
	$opengraph_title = str_replace('{title}',$page_name,$opengraph_title);
	$opengraph_title = $this->ProcessTemplateFromData($opengraph_title);
    echo '<meta property="og:title" content="'.$opengraph_title.'" />'."\n";

    if ($page_info['ogtype'] == "") {
      echo '<meta property="og:type" content="'.$this->GetPreference('meta_opengraph_type','').'" />'."\n";
    }else{
      echo '<meta property="og:type" content="'.$page_info['ogtype'].'" />'."\n";
    }
    echo '<meta property="og:url" content="'.$page_url.'" />'."\n";

    if ($page_image) {
    	$image = $page_image;
		} else {
    	$image = $this->GetPreference('meta_opengraph_image','');
    }
		if ($image) {
    	echo '<meta property="og:image" content="'.$config['root_url'].'/uploads/images/'.$image.'" />'."\n";
    }
    echo '<meta property="og:site_name" content="'.$this->GetPreference('meta_opengraph_sitename',$gCms->siteprefs['sitename']).'" />'."\n";
    echo '<meta property="og:description" content="'.$description.'" />'."\n";
    if ($this->GetPreference('meta_opengraph_application','') != "") {
      echo '<meta property="fb:app_id" content="'.$this->GetPreference('meta_opengraph_application','').'" />'."\n";
    }else{
      echo '<meta property="fb:admins" content="'.$this->GetPreference('meta_opengraph_admins','').'" />'."\n";
    }
    if ($this->GetPreference('meta_location','') != "") {
      echo '<meta property="og:locality" content="'.$this->GetPreference('meta_location','').'" />'."\n";
    }
    if ($this->GetPreference('meta_region','') != "") {
      echo '<meta property="og:region" content="'.$this->GetPreference('meta_region','').'" />'."\n";
    }
    if (($this->GetPreference('meta_latitude','') != "") && ($this->GetPreference('meta_longitude','') != "")) {
      echo '<meta property="og:latitude" content="'.$this->GetPreference('meta_latitude','').'" />'."\n";
      echo '<meta property="og:longitude" content="'.$this->GetPreference('meta_longitude','').'" />'."\n";
    }
}

// Image-Link

if ($page_image)
	echo '<link rel="image_src" href="'.$config['root_url'].'/uploads/images/'.$page_image.'" />'."\n";

?>
