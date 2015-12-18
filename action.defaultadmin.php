<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright(C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright(C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Setup and display admin page, after processing any action-request

//require ('method.setmeta.php');

if (!$this->CheckAccess()) {
	return $this->DisplayErrorPage($this->Lang('accessdenied'));
}

if (isset($_GET['tab'])) {
	$params['tab'] = $_GET['tab'];
//	unset($_GET['tab']);
}

$pre = cms_db_prefix();

// Do the action, if any
if (isset($_GET['what'])) {
	$params['tab'] = 'pagedescriptions';
	$cid = (int)$_GET['content_id'];
	switch($_GET['what']) {
	case 'toggle_index':
		//upsert, sort-of
		$query = 'UPDATE '.$pre.'module_seotools SET indexable=!indexable WHERE content_id=?';
		$query2 = 'INSERT INTO '.$pre.
'module_seotools (content_id, indexable) SELECT ?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS (SELECT 1 FROM '.
		$pre.'module_seotools T WHERE T.content_id=?)';
		$db->Execute($query, array($cid));
		$db->Execute($query2, array($cid, 1, $cid));
/* only manual updates
		if ($this->GetPreference('create_robots',0)) {
			$funcs = new SEO_robot();
			$funcs->createRobotsTXT($this);
		}
		if ($this->GetPreference('create_sitemap',0)) {
			$funcs = new SEO_sitemap();
			$funcs->createSitemap($this);
		}
*/
		break;
	case 'toggle_ignore':
		$pages = explode('@',$_GET['content_data']);
		unset($pages[0]);
		foreach($pages as $sig) {
			list($id,$ignored) = explode('-', $sig);
			$id = (int)$id;
			$query = 'SELECT content_id,ignored FROM '.$pre.'module_seotools WHERE content_id=?';
			$res = $db->GetRow($query,array($id));
			$parms = array();
			if ($res) {
				if ($res['ignored']) {
					$codes = explode(',',$res['ignored']);
					if (in_array($ignored, $codes)) {
						foreach($codes as $i => $name) {
						  if ($name == $ignored) unset($codes[$i]);
						}
					}
					else {
						$codes[] = $ignored;
					}
					if ($codes) {
						$query = 'UPDATE '.$pre.'module_seotools SET ignored=? WHERE content_id=?';
						$parms[] = implode(',',$codes);
					}
					else {
						$query = 'UPDATE '.$pre.'module_seotools SET ignored=NULL WHERE content_id=?';
					}
				}
				else {
					$query = 'UPDATE '.$pre.'module_seotools SET ignored=? WHERE content_id=?';
					$parms[] = $ignored;
				}
			}
			else {
				$query = 'INSERT INTO '.$pre.'module_seotools(ignored,content_id) VALUES(?,?)';
				$parms[] = $ignored;
			}
			$parms[] = $id;
			$db->Execute($query,$parms);
			unset($parms);
		}
		break;
	case 'set_priority':
		//upsert, sort-of
		$query = 'UPDATE '.$pre.'module_seotools SET priority=? WHERE content_id=?';
		$query2 = 'INSERT INTO '.$pre.
'module_seotools (content_id, priority) SELECT ?,? FROM (SELECT 1 AS dmy) Z WHERE NOT EXISTS (SELECT 1 FROM '.
		$pre.'module_seotools T WHERE T.content_id=?)';
		$db->Execute($query, array($_GET['priority'], $cid));
		$db->Execute($query2, array($cid, $_GET['priority'], $cid));
/* only manual updates
		if ($this->GetPreference('create_sitemap',0)) {
			$funcs = new SEO_sitemap();
			$funcs->createSitemap($this);
		}
*/
		break;
	case 'reset_priority':
		$query = 'UPDATE '.$pre.'module_seotools SET priority=NULL WHERE content_id=?';
		$db->Execute($query,array($cid));
/* only manual updates
		if ($this->GetPreference('create_sitemap',0)) {
			$funcs = new SEO_sitemap();
			$funcs->createSitemap($this);
		}
*/
		break;
	case 'reset_ogtype':
		$query = 'UPDATE '.$pre.'module_seotools SET ogtype=NULL WHERE content_id=?';
		$db->Execute($query,array($cid));
		break;
	case 'reset_keywords':
		$query = 'UPDATE '.$pre.'module_seotools SET keywords=NULL WHERE content_id=?';
		$db->Execute($query,array($cid));
		break;
	case 'edit_ogtype':
		$this->Redirect($id, 'edit_ogtype', '', array('content_id'=>$cid));
		break;
	case 'edit_keywords':
		$this->Redirect($id, 'edit_keywords', '', array('content_id'=>$cid));
		break;
	}
//	unset($_GET['what']);
}

if (isset($params['message'])) {
	if (isset($params['warning'])) {
		$smarty->assign('message',$this->ShowErrors($this->Lang($params['message'])));
	}
	else {
		$smarty->assign('message',$this->ShowMessage($this->Lang($params['message'])));
	}
}

$indx = 0;
if (isset($params['tab'])) {
	switch($params['tab']) {
	case 'urgentfixes':
		$indx = 1;
		break;
	case 'importantfixes':
		$indx = 2;
		break;
	case 'pagedescriptions':
		$indx = 3;
		break;
	case 'metasettings':
		$indx = 4;
		break;
	case 'keywordsettings':
		$indx = 5;
		break;
	case 'sitemapsettings':
		$indx = 6;
		break;
	}
}

$smarty->assign('tab_headers',$this->StartTabHeaders().
	$this->SetTabHeader('alerts',$this->Lang('title_alerts'),$indx==0).
	$this->SetTabHeader('urgentfixes',$this->Lang('title_urgent'),$indx==1).
	$this->SetTabHeader('importantfixes',$this->Lang('title_important'),$indx==2).
	$this->SetTabHeader('pagedescriptions',$this->Lang('title_descriptions'),$indx==3).
	$this->SetTabHeader('metasettings',$this->Lang('title_metasettings'),$indx==4).
	$this->SetTabHeader('keywordsettings',$this->Lang('title_keywordsettings'),$indx==5).
	$this->SetTabHeader('sitemapsettings',$this->Lang('title_sitemapsettings'),$indx==6).
	$this->EndTabHeaders().$this->StartTabContent());
$smarty->assign('tab_footers',$this->EndTabContent());

$smarty->assign('start_alerts_tab',$this->StartTab('alerts'));
$smarty->assign('start_urgent_tab',$this->StartTab('urgentfixes'));
$smarty->assign('start_important_tab',$this->StartTab('importantfixes'));
$smarty->assign('start_description_tab',$this->StartTab('pagedescriptions'));
$smarty->assign('start_meta_tab',$this->StartTab('metasettings'));
$smarty->assign('start_keyword_tab',$this->StartTab('keywordsettings'));
$smarty->assign('start_sitemap_tab',$this->StartTab('sitemapsettings'));
$smarty->assign('end_tab',$this->EndTab());

/* Alerts and Fixes Tabs */

$smarty->assign('startform_problems',$this->CreateFormStart($id, 'allignore')); //several uses
$smarty->assign('end_form',$this->CreateFormEnd()); //several uses
$smarty->assign('start_urgent_set',$this->CreateFieldsetStart($id, 'alerts_urgent', $this->Lang('title_alerts_urgent')));
$smarty->assign('start_important_set',$this->CreateFieldsetStart($id, 'alerts_important', $this->Lang('title_alerts_important')));
$smarty->assign('start_notice_set',$this->CreateFieldsetStart($id, 'alerts_notices', $this->Lang('title_alerts_notices')));
$smarty->assign('end_set',$this->CreateFieldsetEnd());

if (isset($config['admin_url'])) {
	$adminurl = $config['admin_url'];
}
else {
	$rooturl = (empty($_SERVER['HTTPS'])) ? $config['root_url'] : $config['ssl_url'];
	$adminurl = $rooturl.'/'.$config['admin_dir'];
}
$theme = ($this->before20) ? cmsms()->get_variable('admintheme'): //CMSMS 1.9+
	cms_utils::get_theme_object();
$theme_url = $adminurl.'/themes/'.$theme->themeName.'/images/icons';
$icontrue = '<img src="'.$theme_url.'/system/true.gif" class="systemicon" />';

$funcs = new SEO_populator();
$urgent = array();
$urgent_alerts = $funcs->getUrgentAlerts($this);
if ($urgent_alerts) {
		$count = 0;
		$more = false;
		$groups = array();
		//count non-ignored urgents
		foreach($urgent_alerts as $alert) {
			if (!array_key_exists('active', $alert) || $alert['active'] == true) {
				if (empty($alert['ignored'])) {
					$count++;
				}
				else {
					$more = true;
				}
			}
			$groups[$alert['group']][] = $alert;
		}
		$icon = '<img src="'.$theme_url.'/Notifications/1.gif" class="systemicon" />';
		if ($count) {
			$smarty->assign('urgent_icon',$icon);
			$smarty->assign('urgent_text',$this->Lang('summary_urgent',$count));
			$smarty->assign('urgent_link','['.$funcs->getTabLink(1,$this->Lang('view_all')).']');
		}
		else {
			$smarty->assign('urgent_icon',$icontrue);
			$key = ($more) ? 'nothing_but' : 'nothing_tofix';
			$smarty->assign('urgent_text',$this->Lang($key));
		}
		$j = 0;
		foreach($groups as $group => $galerts) {
		foreach($galerts as $alert) {
			$oneset = new stdClass;
			$oneset->rowclass = 'row'.($j % 2 + 1);
			if (isset($alert['pages']))
				$oneset->pages = implode('<br />',$alert['pages']);
			else
				$oneset->pages = '';
			$oneset->problem = $alert['message'];
			if (array_key_exists('links_data', $alert)) {
				$links = $alert['links_data'];
				if (count($links) == 1) {
					foreach($links as $id => $data) {
						$oneset->action = $funcs->getFixLink($this, $_GET[$this->secstr], $id);
						$sig = '@'.$id.'-'.$data[1];
					}
				}
				else {
					$s = array();
					$sig = '';
					foreach($links as $id => $data) {
						$s[] = $funcs->getFixLink($this, $_GET[$this->secstr], $id, $data[0]);
						$sig .= '@'.$id.'-'.$data[1];
					}
					$oneset->action = implode('<br />', $s);
					unset($s);
				}
			}
			elseif (array_key_exists('links', $alert)) {
				$links = 'TODO'; //QQQ
				$oneset->action = implode('<br />',$alert['links']);
				$sig = '';
			}
			else {
				$links = 'NONE'; //TODO CHECKME
				$oneset->action = '';
				$sig = '';
			}
			if (array_key_exists('ignored', $alert)) {
				$iname = ($alert['ignored']) ? 'true':'false';
				$oneset->ignored = $this->CreateTooltipLink(null, 'defaultadmin', '',
				'<img src="'.$theme_url.'/system/'.$iname.'.gif" class="systemicon" />',
				$this->Lang('toggle'), array('what'=>'toggle_ignore','content_data'=>$sig,'tab'=>'urgentfixes'));
				$oneset->checkval = $sig;
				$oneset->sel = ''; //TODO
			}
			else {
				$oneset->checkval = '';
				$oneset->sel = '';
			}
			if (array_key_exists('active', $alert)) {
				if (strpos($alert['active'],',') === false) {
					$act1 = $alert['active'];
					$act2 = false;
				}
				else {
					list($act1, $act2) = explode(',',$alert['active']);
					$act1 = (int)$act1;
					$act2 = (int)$act2;
				}
				$cb = '<input type="checkbox" disabled="disabled"';
				if ($act1) $cb .= ' checked="checked"';
				$cb .= ' />';
				if ($act2 !== false) {
					$cb .= '<br /><input type="checkbox" disabled="disabled"';
					if ($act2) $cb .= ' checked="checked"';
					$cb .= ' />';
				}
				$oneset->active = $cb;
			}
			else {
				$oneset->active = '';
			}

			$urgent[] = $oneset;
			$j++;
		}
	}
}
else {
	$smarty->assign('urgent_icon',$icontrue);
	$smarty->assign('urgent_text',$this->Lang('nothing_tofix'));
}
$smarty->assign('urgents',$urgent);

$important = array();
$important_alerts = $funcs->getImportantAlerts($this);
if ($important_alerts) {
	$count = 0;
	$more = false;
	$groups = array();
	//count non-ignored importants
	foreach($important_alerts as $alert) {
		if (empty($alert['ignored']))
			$count++;
		else
			$more = true;
		$groups[$alert['group']][] = $alert;
	}
	$icon = '<img src="'.$theme_url.'/Notifications/2.gif" class="systemicon" />';
	if ($count) {
		$smarty->assign('important_icon',$icon);
		$smarty->assign('important_text',$this->Lang('summary_important', $count));
		$smarty->assign('important_link','['.$funcs->getTabLink(2,$this->Lang('view_all')).']');
	}
	else {
		$smarty->assign('important_icon',$icontrue);
		$key = ($more) ? 'nothing_but' : 'nothing_tofix';
		$smarty->assign('important_text',$this->Lang($key));
	}
	$j = 0;
	foreach($groups as $group => $galerts) {
		foreach($galerts as $alert) {
			$oneset = new stdClass;
			$oneset->rowclass = 'row'.($j % 2 + 1);
			if (isset($alert['pages']))
				$oneset->pages = implode('<br />',$alert['pages']);
			else
				$oneset->pages = '';
			$oneset->problem = $alert['message'];
			if (array_key_exists('links_data', $alert)) {
				$links = $alert['links_data'];
				if (count($links) == 1) {
					foreach($links as $id => $data) {
						$oneset->action = $funcs->getFixLink($this, $_GET[$this->secstr], $id);
						$sig = '@'.$id.'-'.$data[1];
					}
				}
				else {
					$s = array();
					$sig = '';
					foreach($links as $id => $data) {
						$s[] = $funcs->getFixLink($this, $_GET[$this->secstr], $id, $data[0]);
						$sig .= '@'.$id.'-'.$data[1];
					}
					$oneset->action = implode('<br />', $s);
					$oneset->checkval = $sig;
					unset($s);
				}
			}
			elseif (array_key_exists('links', $alert)) {
				$links = 'TODO'; //QQQ
				$oneset->action = implode('<br />',$alert['links']);
				$sig = '';
			}
			else {
				$links = 'NONE';
				$sig = '';
			}

			if (array_key_exists('ignored', $alert)) {
				$iname = ($alert['ignored']) ? 'true':'false';
				$oneset->ignored = $this->CreateTooltipLink(null, 'defaultadmin', '',
				 '<img src="'.$theme_url.'/system/'.$iname.'.gif" class="systemicon" />',
				 $this->Lang('toggle'), array('what'=>'toggle_ignore','content_data'=>$sig,'tab'=>'importantfixes'));
				$oneset->checkval = $sig;
			}
			else {
				$oneset->ignored = '';
				$oneset->checkval = '';
			}
			if (array_key_exists('active', $alert)) {
				if (strpos($alert['active'],',') === false) {
					$act1 = $alert['active'];
					$act2 = false;
				}
				else {
					list($act1, $act2) = explode(',',$alert['active']);
					$act1 = (int)$act1;
					$act2 = (int)$act2;
				}
				$cb = '<input type="checkbox" disabled="disabled"';
				if ($act1) $cb .= ' checked="checked"';
				$cb .= ' />';
				if ($act2 !== false) {
					$cb .= '<br /><input type="checkbox" disabled="disabled"';
					if ($act2) $cb .= ' checked="checked"';
					$cb .= ' />';
				}
				$oneset->active = $cb;
				$oneset->sel = ''; //TODO
			}
			else {
				$oneset->active = '';
				$oneset->sel = ''; //TODO
			}
			$important[] = $oneset;
			$j++;
		}
	}
}
else {
	$smarty->assign('important_icon',$icontrue);
	$smarty->assign('important_text',$this->Lang('nothing_tofix'));
}
$smarty->assign('importants',$important);

$notice = array();
$notice_alerts = $funcs->getNoticeAlerts($this);
if ($notice_alerts) {
	$icon = '<img src="'.$theme_url.'/Notifications/3.gif" class="systemicon" />';
	foreach($notice_alerts as $alert) {
		$oneset = new stdClass;
		$oneset->icon = $icon;
		$oneset->text = $alert['message'];
		if (isset($alert['links'])) $oneset->link = '['.implode(' | ',$alert['links']).']';
		$notice[] = $oneset;
	}
}
else {
	$oneset = new stdClass;
	$oneset->icon = $icontrue;
	$oneset->text = $this->Lang('nothing_tofix');
	$notice[] = $oneset;
}
$smarty->assign('notices',$notice);

$smarty->assign('start_resources_set',$this->CreateFieldsetStart(null, 'resources',$this->Lang('title_resources')));
$smarty->assign('resource_links',array(
 '<a href="http://validator.w3.org">W3C validator</a>',
 '<a href="http://brokenlinkcheck.com">Link checker</a>',
 '<a href="http://www.feedthebot.com/tools">FeedtheBot</a>',
 '<a href="http://www.siteliner.com">Siteliner</a>'
));

$smarty->assign('title_pages',$this->Lang('title_pages'));
$smarty->assign('title_active',$this->Lang('title_active'));
$smarty->assign('title_problem',$this->Lang('title_problem'));
$smarty->assign('title_ignored',$this->Lang('title_ignored'));
$smarty->assign('title_action',$this->Lang('title_action'));
$smarty->assign('ignore1',$this->CreateInputSubmit(null, 'ignore_selected',
	$this->Lang('ignore'),'title="'.$this->Lang('help_ignore').'" onclick="return confirm_click(\'urgent\');"'));
$smarty->assign('unignore1',$this->CreateInputSubmit(null, 'unignore_selected',
	$this->Lang('unignore'),'title="'.$this->Lang('help_unignore').'" onclick="return confirm_click(\'urgent\');"'));
$smarty->assign('ignore2',$this->CreateInputSubmit(null, 'ignore_selected',
	$this->Lang('ignore'),'title="'.$this->Lang('help_ignore').'" onclick="return confirm_click(\'important\');"'));
$smarty->assign('unignore2',$this->CreateInputSubmit(null, 'unignore_selected',
	$this->Lang('unignore'),'title="'.$this->Lang('help_unignore').'" onclick="return confirm_click(\'important\');"'));

$meta = $db->GetAssoc('SELECT mname,value,output,smarty,active FROM '.$pre.'module_seotools_meta ORDER BY mname');

/* Page settings Tab */

$smarty->assign('startform_pages',$this->CreateFormStart($id, 'allindex'));
//$smarty->assign('title_id',$this->Lang('page_id'));
$smarty->assign('title_name',$this->Lang('page_name'));
$smarty->assign('title_priority',$this->Lang('priority'));
$smarty->assign('title_ogtype',$this->Lang('og_type'));
//TODO other groups' titles
$smarty->assign('title_keywords',$this->Lang('keywords'));
$smarty->assign('title_desc',$this->Lang('description'));
$smarty->assign('title_index',$this->Lang('title_index'));

$iconreset = '<img src="'.$this->GetModuleURLPath().'/images/reset.png" class="systemicon" />';
$iconedit = '<img src="'.$theme_url.'/system/edit.gif" class="systemicon" />';
$icondown = '<img src="'.$theme_url.'/system/arrow-d.gif" class="systemicon" />';
$iconup = '<img src="'.$theme_url.'/system/arrow-u.gif" class="systemicon" />';
$default_ogtype = (!empty($meta['meta_og_type'])) ? $meta['meta_og_type']['value']:'';
$sep = $this->GetPreference('keyword_separator',',');

$items = array();

$query = 'SELECT * FROM '.$pre.'content ORDER BY hierarchy ASC';
$rst = $db->Execute($query);
if ($rst) {
	$j = 0;
	while ($row = $rst->fetchRow()) {
		$prefix = '';
		$auto_priority = 80;
		$n = substr_count($row['hierarchy'],'.');
		for($i = 0; $i < $n; $i++) {
			$prefix .= '&raquo; ';
			$auto_priority  = $auto_priority / 2;
		}
		if ($row['default_content'] == 1) {
			$auto_priority = 100;
		}

		$oneset = new stdClass;
		$oneset->rowclass = 'row'.($j % 2 + 1);
		$oneset->name = $prefix.' '.$row['content_name'];

		if (strpos($row['type'],'content') === 0) { //any content type
			$query = 'SELECT content FROM '.$pre.'content_props WHERE content_id = ? AND prop_name = ?';
			$parms = array($row['content_id']);
			$parms[] = str_replace(' ','_',$this->GetPreference('description_block',''));
			$description = $db->GetOne($query,$parms);
			$description_auto = false;
			$funcs = new SEO_keyword();
			$kw = $funcs->getKeywords($this,$row['content_id']);
			if ($kw && $description == false && $this->GetPreference('description_auto_generate',false)) {
				if (count($kw) > 1) {
					$last_keyword = array_pop($kw);
					$keywords = $this->Lang('and',implode(',',$kw),$last_keyword);
				}
				else {
					$keywords = reset($kw);
				}
				$description = $this->Lang('auto_generated').": ".str_replace('{keywords}',$keywords,$this->GetPreference('description_auto',''));
				$description = str_replace('{title}',$row['content_name'],$description);
				$description_auto = true;
			}

			$updown = '';
			if ($auto_priority > 10) {
				$updown .= $this->CreateTooltipLink(null, 'defaultadmin', '', $icondown, $this->Lang('decrease_priority'), array('what'=>'set_priority','priority'=>$auto_priority-10,'content_id'=>$row['content_id']));
			}
			if ($auto_priority <= 90) {
				$updown .= $this->CreateTooltipLink(null, 'defaultadmin', '', $iconup, $this->Lang('increase_priority'), array('what'=>'set_priority','priority'=>$auto_priority+10,'content_id'=>$row['content_id']));
			}
			$priority = '('.$this->Lang('auto').') '.$auto_priority.'%';
			$ogtype = '('.$this->Lang('default').') '.$default_ogtype.' '.$this->CreateTooltipLink(null, 'defaultadmin', '', $iconedit, $this->Lang('edit_value'), array('what'=>'edit_ogtype','content_id'=>$row['content_id']));
			$keywords = '('.$this->Lang('auto').') '.count($kw).' '.$this->CreateTooltipLink(null, 'defaultadmin', '', $iconedit, implode(', ',$kw).'; '.$this->Lang('edit_value'), array('what'=>'edit_keywords','content_id'=>$row['content_id']));
			$iname = 'true';

			$query = 'SELECT * FROM '.$pre.'module_seotools WHERE content_id = ?';
			$info = $db->GetRow($query,array($row['content_id']));
			if ($info && $info['content_id'] != '') {
				if ($info['priority'] != 0) {
				  $priority = '<strong>'.$info['priority'] . '% '.$this->CreateTooltipLink(null, 'defaultadmin', '', $iconreset, $this->Lang('reset_to_default'), array('what'=>'reset_priority','content_id'=>$row['content_id'])) . '</strong>';
				  $auto_priority = $info['priority'];
				}
				if ($info['ogtype'] != '') {
				  $ogtype = '<strong>'.$info['ogtype'] . ' '
				  . $this->CreateTooltipLink(null, 'defaultadmin', '', $iconreset, $this->Lang('reset_to_default'), array('what'=>'reset_ogtype','content_id'=>$row['content_id']))
				  . $this->CreateTooltipLink(null, 'defaultadmin', '', $iconedit, $this->Lang('edit_value'), array('what'=>'edit_ogtype','content_id'=>$row['content_id'])).'</strong>';
				}
				if ($info['keywords'] != '') {
					$keywords = '<strong>'.count(explode($sep,$info['keywords']))
					. $this->CreateTooltipLink(null, 'defaultadmin', '', $iconreset, $this->Lang('reset_to_default'), array('what'=>'reset_keywords','content_id'=>$row['content_id']))
					. $this->CreateTooltipLink(null, 'defaultadmin', '', $iconedit, $this->Lang('edit_value'), array('what'=>'edit_keywords','content_id'=>$row['content_id'])).'</strong>';
				}
				if (!$info['indexable']) {
				  $iname = 'false';
				}
			}
			unset($info);

			$oneset->priority = $updown.' '.$priority;
			$oneset->ogtype = $ogtype;
			$oneset->keywords = $keywords;
			if ($description != '') {
				$inm2 = ($description_auto) ? 'warning' : 'true';
				$oneset->desc ='<img src="'.$theme_url.'/system/'.$inm2.'.gif" title="'.
				strip_tags($description).'" class="systemicon" />';
			}
			else {
				$oneset->desc = '<a href="editcontent.php?'.$this->secstr.'='.$_GET[$this->secstr].
				'&content_id='.$row['content_id'].'"><img src="'.$theme_url.'/system/false.gif" title="'.
				$this->Lang('click_to_add_description').'" class="systemicon" /></a>';
			}
			$oneset->index = $this->CreateTooltipLink(null, 'defaultadmin', '',
				'<img src="'.$theme_url.'/system/'.$iname.'.gif" class="systemicon" />',
				$this->Lang('toggle'), array('what'=>'toggle_index','content_id'=>$row['content_id']));
			$oneset->checkval = $row['content_id'];
			$oneset->sel = ''; //TODO
		}
		else {
			$oneset->priority = '---';
			$oneset->ogtype = '';
			$oneset->keywords = '';
			$oneset->desc = '';
			$oneset->index = '';
			$oneset->checkval = '';
			$oneset->sel = '';
		}
		$items[] = $oneset;
		$j++;
	}
	$rst->Close();
}

$smarty->assign('items',$items);
$smarty->assign('index',$this->CreateInputSubmit(null, 'index_selected',
	$this->Lang('index'),'title="'.$this->Lang('help_index').'" onclick="return confirm_click(\'indx\');"'));
$smarty->assign('unindex',$this->CreateInputSubmit(null, 'unindex_selected',
	$this->Lang('unindex'),'title="'.$this->Lang('help_unindex').'" onclick="return confirm_click(\'indx\');"'));

$smarty->assign('cancel',$this->CreateInputSubmit(null, 'cancel', $this->Lang('cancel')));

/* SEO Settings Tab */

// Get lang array, for dynamic checking
$var = key($this->langhash);
$trans = $this->langhash[$var];

$smarty->assign('startform_settings',$this->CreateFormStart($id, 'changesettings'));

$ungrouped = array();

$oneset = new stdClass();
$name = 'content_type';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'html';
$oneset->input = $this->CreateInputText(null, $name, $val, 10);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$ungrouped[$name] = $oneset;

$smarty->assign('ungrouped', $ungrouped);

$smarty->assign('start_page_set',$this->CreateFieldsetStart(null, 'title_description', $this->Lang('title_title_description')));
$pagevals = array();

$oneset = new stdClass();
$name = 'title';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'{title} | {$sitename} - {$title_keywords}';
$oneset->input = $this->CreateInputText(null, 'title', $val, 60);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$pagevals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_std_title';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'{title} | {$sitename}';
$oneset->input = $this->CreateInputText(null, $name, $val, 60);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$pagevals[$name] = $oneset;

$oneset = new stdClass();
$name = 'description_block';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->input = $this->CreateInputText(null, $name, $this->GetPreference($name,''), 60);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$pagevals[$name] = $oneset;

$oneset = new stdClass();
$name = 'description_auto_generate';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->inline = 1;
$oneset->input = $this->CreateInputCheckbox(null, $name, 1, $this->GetPreference($name,0));
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$pagevals[$name] = $oneset;

$oneset = new stdClass();
$name = 'description_auto';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->input = $this->CreateInputText(null, $name, $this->GetPreference($name,'This page covers the topics {keywords}'), 40);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$pagevals[$name] = $oneset;

$smarty->assign('pageset', $pagevals);

/* META Types */

$smarty->assign('start_meta_set',$this->CreateFieldsetStart(null, 'meta_type', $this->Lang('title_meta_type')));
$metatypes = array();
$groups = $db->GetAssoc('SELECT gname,active FROM '.$pre.
	'module_seotools_group WHERE gname != \'before\' AND gname != \'after\' ORDER BY vieworder');
foreach ($groups as $name=>$act) {
	$oneset = new stdClass();
	$k = $name.'_title';
	$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
	$oneset->input = $this->CreateInputCheckbox(null, $name, 1, $act);
	$oneset->inline = 1;
	$k = $name.'_help';
	$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
	$metatypes[] = $oneset;
}
$smarty->assign('metatypes', $metatypes);

$img_files = array('('.$this->Lang('none').')'=>'');
// Get image-files in uploads dir (wherever that actually is)
if(isset($config['image_uploads_path'])) {
	$offs = strlen($config['root_path']);
	$rest = substr($config['image_uploads_path'],$offs+1); //no leading separator
}
else {
	$rest = 'uploads'.DIRECTORY_SEPARATOR.'images';
}
$offs = strpos(__FILE__,'modules'.DIRECTORY_SEPARATOR.$this->GetName());
$img_dir = substr(__FILE__, 0, $offs).$rest;
$dp = opendir($img_dir);
if ($dp) {
	while ($file = readdir($dp)) {
		if (strlen($file) >= 5) {
			foreach (array('.gif','.png','.jpg','.jpeg','.webp','.svg') as $type) {
				if (strripos($file, $type, -5) !== false) {
					$img_files[$file] = $file;
					break;
				}
			}
		}
	}
	closedir($dp);
}

/* META Values */

$smarty->assign('start_deflt_set',$this->CreateFieldsetStart(null, 'meta_defaults', $this->Lang('title_meta_defaults')));
$metavals = array();

$oneset = new stdClass();
$name = 'meta_std_publisher';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_std_contributor';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_std_copyright';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'(C) '.date('Y').'. All rights reserved.';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$oneset->head = $this->Lang('meta_std_location_description');
$name = 'meta_std_location';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_std_region';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 5);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_std_latitude';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 15);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_std_longitude';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 15);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$oneset->head = $this->Lang('meta_og_description');
$name = 'meta_og_title';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'{title}';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_og_type';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_og_sitename';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 25);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_og_image';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:-1;
$oneset->input = $this->CreateInputDropdown(null, $name, $img_files, null, $val);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_og_admins';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 48);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_og_application';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

/* twitter metas derived from others
$oneset = new stdClass();
$name = 'meta_twt_title';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_twt_description';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;
*/

$oneset = new stdClass();
$oneset->head = $this->Lang('meta_twt_description');
$name = 'meta_twt_card';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 24);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_twt_image';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputDropdown(null, $name, $img_files, null, $val);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_twt_site';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 18);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_twt_creator';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 18);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

/* google+ metas derived from others
$oneset = new stdClass();
$oneset->head = $this->Lang('meta_gplus_description');
$name = 'meta_gplus_name';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_gplus_description';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 32);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;

$oneset = new stdClass();
$name = 'meta_gplus_image';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputDropdown(null, $name, $img_files, null, $val);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$metavals[$name] = $oneset;
*/
/*
<!-- Schema.org markup for Google+ -->
<meta itemprop="name" content="The Name or Title Here">
<meta itemprop="description" content="This is the page description">
<meta itemprop="image" content="http://www.example.com/image.jpg">

<!-- Twitter Card data -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:title" content="Page Title">
<meta name="twitter:description" content="Page description less than 200 characters">
<meta name="twitter:creator" content="@author_handle">
<!-- Twitter summary card with large image must be at least 280x150px -->
<meta name="twitter:image:src" content="http://www.example.com/image.html">
*/
$j = 0;
foreach ($meta as $name=>&$data) {
	if($name != 'UNUSED' && $data['value'] != 'UNUSED') {
		if (!($name == 'meta_additional' || $name == 'verification' //populated elsewhere
		   || array_key_exists($name,$pagevals)
		   || array_key_exists($name,$metavals)
		   || array_key_exists($name,$ungrouped))) {
			$oneset = new stdClass();
			if ($j == 0) {
				$oneset->head = $this->Lang('meta_new_description');
				$j = 1;
			}
			$k = $name.'_title';
			$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
			$oneset->input = $this->CreateInputText(null, $name, $data['value'], 32);
			$k = $name.'_help';
			$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
			$metavals[$name] = $oneset;
		}
	}
}
unset($data);

$smarty->assign('metadeflts', $metavals);

/* Additional Meta Tags */

$smarty->assign('start_extra_set',$this->CreateFieldsetStart(null, 'meta_additional', $this->Lang('title_meta_additional')));

//extra
$extraset = array();
$oneset = new stdClass();
$name = 'meta_additional';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateTextArea(false, null, $val, $name,
	'', '', '', '', 60, 5, '', '', 'style="height:10em;"');
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;

$extraset[] = $oneset;

$smarty->assign('extraset', $extraset);

$smarty->assign('submit1',$this->CreateInputSubmit(null, 'save_meta_settings', $this->Lang('save')));

/* KEYWORD Settings */

$smarty->assign('start_ksettings_set',$this->CreateFieldsetStart(null, 'keyword_weight_description', $this->Lang('title_keyword_weight')));
$keyset = array();
//wordsblock_name
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_block_title');
$oneset->input = $this->CreateInputText(null, 'keyword_block', $this->GetPreference('keyword_block',''), 60);
$oneset->help = htmlentities($this->Lang('keyword_block_help'));
$keyset[] = $oneset;
//kw_sep
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_separator_title');
$oneset->input = $this->CreateInputText(null, 'keyword_separator', $sep, 1);
$oneset->help = htmlentities($this->Lang('keyword_separator_help'));
$keyset[] = $oneset;
//min_length
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_minlength_title');
$oneset->input = $this->CreateInputText(null, 'keyword_minlength', $this->GetPreference('keyword_minlength',6), 2);
$oneset->help = htmlentities($this->Lang('keyword_minlength_help'));
$keyset[] = $oneset;
//title_weight
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_title_weight_title');
$oneset->input = $this->CreateInputText(null, 'keyword_title_weight', $this->GetPreference('keyword_title_weight',6), 2);
$oneset->help = htmlentities($this->Lang('keyword_title_weight_help'));
$keyset[] = $oneset;
//desc_weight
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_description_weight_title');
$oneset->input = $this->CreateInputText(null, 'keyword_description_weight', $this->GetPreference('keyword_description_weight',4), 2);
$oneset->help = htmlentities($this->Lang('keyword_description_weight_help'));
$keyset[] = $oneset;
//head_weight
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_headline_weight_title');
$oneset->input = $this->CreateInputText(null, 'keyword_headline_weight', $this->GetPreference('keyword_headline_weight',2), 2);
$oneset->help = htmlentities($this->Lang('keyword_headline_weight_help'));
$keyset[] = $oneset;
//cont_weight
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_content_weight_title');
$oneset->input = $this->CreateInputText(null, 'keyword_content_weight', $this->GetPreference('keyword_content_weight',1), 2);
$oneset->help = htmlentities($this->Lang('keyword_content_weight_help'));
$keyset[] = $oneset;
//min_weight
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_minimum_weight_title');
$oneset->input = $this->CreateInputText(null, 'keyword_minimum_weight', $this->GetPreference('keyword_minimum_weight',7), 2);
$oneset->help = htmlentities($this->Lang('keyword_minimum_weight_help'));
$keyset[] = $oneset;

$smarty->assign('keyset', $keyset);

$smarty->assign('start_klist_set',$this->CreateFieldsetStart(null, 'keyword_exclude_description', $this->Lang('title_keyword_exclude')));
$listset = array();
//incl_words
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_default_title');
$oneset->input = $this->CreateTextArea(false, null, $this->GetPreference('keyword_default',''),
	'keyword_default', '', '', '', '', 60, 5, '' ,'', 'style="height:5em;"');
$oneset->help = htmlentities($this->Lang('keyword_default_help'));
$listset[] = $oneset;
//excl_words
$oneset = new stdClass();
$oneset->title = $this->Lang('keyword_exclude_title');
$oneset->input = $this->CreateTextArea(false, null, $this->GetPreference('keyword_exclude',''),
	'keyword_exclude', '', '', '', '', 60, 5,'','','style="height:5em;"');
$oneset->help = htmlentities($this->Lang('keyword_exclude_help'));
$listset[] = $oneset;

$smarty->assign('listset', $listset);

$smarty->assign('keyword_help',$this->Lang('help_keyword_generator'));

$smarty->assign('submit2',$this->CreateInputSubmit(null, 'save_keyword_settings', $this->Lang('save')));

/* SITEMAP Settings */

$smarty->assign('start_map_set',$this->CreateFieldsetStart(null, 'sitemap_description', $this->Lang('title_sitemap_description')));
$fileset = array();

$oneset = new stdClass();
$name = 'create_sitemap';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->title .= ' *';
$oneset->inline = 1;
$oneset->input = $this->CreateInputCheckbox(null, 'create_sitemap', 1, $this->GetPreference('create_sitemap',0));
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$fileset[] = $oneset;
//push_map
$oneset = new stdClass();
$name = 'push_sitemap';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
if (ini_get('allow_url_fopen') || function_exists('curl_version')) {
	$oneset->inline = 1;
	$i = $this->CreateInputCheckbox(null, $name, 1, $this->GetPreference($name,0));
}
else {
	$i = $this->Lang('no_pusher');
}
$oneset->input = $i;
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$fileset[] = $oneset;

$oneset = new stdClass();
$name = 'verification';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$val = (!empty($meta[$name])) ? $meta[$name]['value']:'';
$oneset->input = $this->CreateInputText(null, $name, $val, 40);
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$fileset[] = $oneset;

$oneset = new stdClass();
$name = 'create_robots';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->title .= ' *';
$oneset->inline = 1;
$oneset->input = $this->CreateInputCheckbox(null, $name, 1, $this->GetPreference($name,0));
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$fileset[] = $oneset;

$oneset = new stdClass();
$name = 'robot_start';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->input = $this->CreateTextArea(false, null, $this->GetPreference($name,''),
 'robot_start', '', '', '', '', 50, 5, '', '', 'style="height:5em;width:50em;"'); //needs inline styling
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$fileset[] = $oneset;

$oneset = new stdClass();
$name = 'robot_end';
$k = $name.'_title';
$oneset->title = (array_key_exists($k,$trans)) ? $trans[$k] : $name;
$oneset->input = $this->CreateTextArea(false, null, $this->GetPreference($name,''),
 'robot_end', '', '', '', '', 50, 5, '', '', 'style="height:5em;width:50em;"');
$k = $name.'_help';
$oneset->help = (array_key_exists($k,$trans)) ? htmlentities($trans[$k]) : null;
$fileset[] = $oneset;

$smarty->assign('fileset', $fileset);

$smarty->assign('submit3',$this->CreateInputSubmit(null, 'save_sitemap_settings', $this->Lang('save')));
$smarty->assign('display',$this->CreateInputSubmit(null, 'display_robots_file', $this->Lang('display'),
 'title="'.$this->Lang('robots_display').'"'));

if ($this->GetPreference('create_sitemap',0))
{
	if ($this->GetPreference('create_robots',0))
		$title = $this->Lang('button_regenerate_both');
	else
		$title = $this->Lang('button_regenerate_sitemap');
}
elseif ($this->GetPreference('create_robots',0))
	$title = $this->Lang('button_regenerate_robot');
else
	$title = null;

if ($title != null) {
	$smarty->assign('start_regen_set',$this->CreateFieldsetStart(null, 'regenerate_sitemap', $this->Lang('title_regenerate_both')));
	$smarty->assign('help_regenerate',$this->Lang('text_regenerate_sitemap'));
	$smarty->assign('regenerate',$this->CreateInputSubmit(null, 'do_regenerate', $title));
	$smarty->assign('sitemap_help',$this->Lang('help_sitemap_robots'));
}

echo $this->ProcessTemplate('adminpanel.tpl');

?>
