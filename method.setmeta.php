<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2012-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

$sitename = get_site_preference('sitename','CMSMS Site');
$yr = date('Y');
//replicated from CMSMS global metadata
$additional = <<<EOS
<meta name="generator" content="CMS Made Simple - Copyright (C) 2004-{$yr} Ted Kulp. All rights reserved." />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
EOS;
/*
content_type: unique identifier, also 'prefix' for lang keys like $content_type.'_title' and $content_type.'_help'
gid: group enum 1..7 representing before, meta_std, meta_dc, meta_og, meta_twt, meta_gplus, after
value: the specific data, or 'UNUSED' in which case this item will not appear in
 the admin UI, but it may still be output after some runtime interpretation
output: usually a 'template' for sprintf() (which is called as the 3rd/last phase
 of preparation for output) so includes a single %s, or is 'UNUSED' if no such
 conversion is needed
calc: boolean whether this item needs custom-processing in action.default as the
 1st phase of preparation for output (any such processing must be hard-coded there)
smarty: boolean whether the item is to be processed via smarty (2nd phase, after
 calc. if any)
active: boolean whether this item is to be output (provided that the group this
 item belongs to is also active - groups 'before' and 'after' are always active)
*/
$defs = array(
//group 'before'
'title'=>array       ('gid'=>1,'value'=>'{title} | '.$sitename.' - {$title_keywords}','output'=>'<title>%s</title>','calc'=>1,'smarty'=>1,'active'=>1),
'content_type'=>array('gid'=>1,'value'=>'xhtml','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),
'verification'=>array('gid'=>1,'value'=>'','output'=>'<meta name="google-site-verification" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'indexable'=>array   ('gid'=>1,'value'=>'UNUSED','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1), //fake, just for runtime calc
//group meta_std Standard (enabled by default) some of these values are 'borrowed' for other groups
'meta_std_title'=>array      ('gid'=>2,'value'=>'{title} | '.$sitename,'output'=>'<meta name="title" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_std_description'=>array('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="description" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_std_keywords'=>array   ('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="keywords" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_std_publisher'=>array  ('gid'=>2,'value'=>'','output'=>'<meta name="author" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_std_contributor'=>array('gid'=>2,'value'=>'','output'=>'<meta name="contributor" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_std_copyright'=>array  ('gid'=>2,'value'=>'(C). All rights reserved.','output'=>'<meta name="copyright" content="%s" />','calc'=>0,'smarty'=>1,'active'=>1),
'meta_std_date'=>array       ('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="date" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1), //auto only
'meta_std_lastdate'=>array   ('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="lastupdate" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1), //auto only
'meta_std_revised'=>array    ('gid'=>2,'value'=>'UNUSED','output'=>'<meta name="revised" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1), //auto only
'meta_std_region'=>array     ('gid'=>2,'value'=>'','output'=>'<meta name="geo.region" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_std_location'=>array   ('gid'=>2,'value'=>'','output'=>'<meta name="geo.placename" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_std_latitude'=>array   ('gid'=>2,'value'=>'','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),
'meta_std_longitude'=>array  ('gid'=>2,'value'=>'','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),
//group meta_dc Dublin Core (just for runtime calc)
'meta_dc'=>array     ('gid'=>3,'value'=>'UNUSED','output'=>'UNUSED','calc'=>1,'smarty'=>0,'active'=>1),
//group meta_og OpenGraph
'meta_og_url'=>array        ('gid'=>4,'value'=>'UNUSED','output'=>'<meta property="og:url" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_og_sitename'=>array   ('gid'=>4,'value'=>$sitename,'output'=>'<meta property="og:sitemame" content="%s" />','calc'=>0,'smarty'=>1,'active'=>1),
'meta_og_title'=>array      ('gid'=>4,'value'=>'{title}','output'=>'<meta property="og:title" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_og_type'=>array       ('gid'=>4,'value'=>'','output'=>'<meta property="og:type" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_og_image'=>array      ('gid'=>4,'value'=>'','output'=>'<meta property="og:image" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
'meta_og_application'=>array('gid'=>4,'value'=>'','output'=>'<meta property="fb:app_id" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_og_admins'=>array     ('gid'=>4,'value'=>'','output'=>'<meta property="fb:admins" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
//group meta_twt Twitter
'meta_twt_title'=>array      ('gid'=>5,'value'=>'UNUSED','output'=>'<meta name="twitter:title" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_twt_description'=>array('gid'=>5,'value'=>'UNUSED','output'=>'<meta name="twitter:description" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_twt_site'=>array       ('gid'=>5,'value'=>'','output'=>'<meta name="twitter:site" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1), //handle, not url
'meta_twt_creator'=>array    ('gid'=>5,'value'=>'','output'=>'<meta name="twitter:creator" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_twt_card'=>array       ('gid'=>5,'value'=>'','output'=>'<meta name="twitter:card" content="%s" />','calc'=>0,'smarty'=>0,'active'=>1),
'meta_twt_image'=>array      ('gid'=>5,'value'=>'','output'=>'<meta name="twitter:image:src" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
//group meta_gplus Google+
'meta_gplus_name'=>array       ('gid'=>6,'value'=>'UNUSED','output'=>'<meta itemprop="name" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1), //name or title
'meta_gplus_description'=>array('gid'=>6,'value'=>'UNUSED','output'=>'<meta itemprop="description" content="%s" />','calc'=>1,'smarty'=>1,'active'=>1),
'meta_gplus_image'=>array      ('gid'=>6,'value'=>'UNUSED','output'=>'<meta itemprop="image" content="%s" />','calc'=>1,'smarty'=>0,'active'=>1),
//group 'after'
'meta_additional'=>array  ('gid'=>7,'value'=>$additional,'output'=>'UNUSED','calc'=>1,'smarty'=>1,'active'=>1),
);

?>
