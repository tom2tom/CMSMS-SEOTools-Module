<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php
/*
Schema.org markup for Google+
<meta itemprop="name" content="The Name or Title Here">
<meta itemprop="description" content="This is the page description">
<meta itemprop="image" content="http://www.example.com/image.jpg">
Twitter Card data
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:title" content="Page Title">
<meta name="twitter:description" content="Page description less than 200 characters">
<meta name="twitter:creator" content="@author_handle">
Twitter summary card with large image must be at least 280x150px
<meta name="twitter:image:src" content="http://www.example.com/image.html">
*/
$sitename = get_site_preference('sitename','CMSMS Site');

$defs = array(
'content_type'    =>array('gid'=>1,'value'=>'html','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),
'indexable'       =>array('gid'=>1,'value'=>'UNUSED','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1), //fake, just for calc
'verification'    =>array('gid'=>1,'value'=>'','output'=>'<meta name="google-site-verification" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'title'           =>array('gid'=>1,'value'=>'{title} | '.$sitename.' - {$title_keywords}','output'=>"<title>%s</title>",'calc'=>1,'smarty'=>1,'active'=>1),

'meta_title'      =>array('gid'=>2,'value'=>'{title} | '.$sitename,'output'=>'<meta name="title" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_description'=>array('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="description" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_keywords'   =>array('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="keywords" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_publisher'  =>array('gid'=>2,'value'=>'','output'=>'<meta name="author" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_contributor'=>array('gid'=>2,'value'=>'','output'=>'<meta name="contributor" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_copyright'  =>array('gid'=>2,'value'=>'(C). All rights reserved.','output'=>'UNUSED','calc'=>0,'smarty'=>1,'active'=>1),
'meta_region'     =>array('gid'=>2,'value'=>'','output'=>'<meta name="geo.region" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_date'       =>array('gid'=>2,'value'=>'UNUSED','output'=>'meta name="date" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1), //auto only
'meta_lastdate'   =>array('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="lastupdate" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1), //auto only
'meta_revised'    =>array('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="revised" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1), //auto only
'meta_location'   =>array('gid'=>2,'value'=>'','output'=>'<meta name="geo.placename" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_latitude'   =>array('gid'=>2,'value'=>'','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),
'meta_longitude'  =>array('gid'=>2,'value'=>'','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),

'meta_dublin'     =>array('gid'=>3,'value'=>'UNUSED','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),

'meta_opengraph_url'        =>array('gid'=>4,'value'=>'UNUSED','output'=>'<meta property="og:url" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_opengraph_sitename'   =>array('gid'=>4,'value'=>$sitename,'output'=>'<meta property="og:sitemame" content="%s" />','calc'=>0,'smarty'=>1,'active'=>1),
'meta_opengraph_title'      =>array('gid'=>4,'value'=>'{title}','output'=>'<meta property="og:title" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_opengraph_type'       =>array('gid'=>4,'value'=>'','output'=>'<meta property="og:type" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_opengraph_image'      =>array('gid'=>4,'value'=>'','output'=>'<meta property="og:image" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_opengraph_application'=>array('gid'=>4,'value'=>'','output'=>'<meta property="fb:app_id" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_opengraph_admins'     =>array('gid'=>4,'value'=>'','output'=>'<meta property="fb:admins" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),

'meta_twt_card'       =>array('gid'=>5,'value'=>'','output'=>'<meta name="twitter:card" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_twt_site'       =>array('gid'=>5,'value'=>'','output'=>'<meta name="twitter:site" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_twt_title'      =>array('gid'=>5,'value'=>'{title}','output'=>'<meta name="twitter:title" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_twt_description'=>array('gid'=>5,'value'=>'','output'=>'<meta name="twitter:description" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_twt_creator    '=>array('gid'=>5,'value'=>'','output'=>'<meta name="twitter:creator" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_twt_image'      =>array('gid'=>5,'value'=>'','output'=>'<meta name="twitter:image:src" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),

'meta_gplus_name'       =>array('gid'=>6,'value'=>'{title}','output'=>'<meta itemprop="name" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1), //name or title
'meta_gplus_description'=>array('gid'=>6,'value'=>'','output'=>'<meta itemprop="description" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_gplus_image'      =>array('gid'=>6,'value'=>'','output'=>'<meta itemprop="image" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),

'meta_additional' =>array('gid'=>7,'value'=>'','output'=>'UNUSED','calc'=>1,'smarty'=>1,'active'=>1),
);

$gid = -1; //unmatched

$sql = 'INSERT INTO '.$pre.'module_seotools_meta
(group_id,name,value,output,calc,smarty,vieworder,active)
VALUES (?,?,?,?,?,?,?,?)';

foreach ($defs as $name=>$data) {
	if ($gid != $data['gid']) {
		$i = 1;
	}
	else {
		$i++;
	}
	$db->Execute($sql,array(
		$data['gid'],
		$name,
		$data['value'],
		$data['output'],
		$data['calc'],
		$data['smarty'],
		$i,
		$data['active']));
}

?>