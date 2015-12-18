<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright(C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright(C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Setup and display admin page, after processing any action-request

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

$meta = $db->GetAssoc('SELECT mname,value,smarty,active FROM '.$pre.'module_setools_meta');

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

$pagesettings = '';

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

$smarty->assign('startform_settings',$this->CreateFormStart($id, 'changesettings'));

$ungrouped = array();
//ctype
$oneset = new stdClass();
$oneset->title = $this->Lang('content_type_title');
$val = (!empty($meta['content_type'])) ? $meta['content_type']['value']:'html';
$oneset->input = $this->CreateInputText(null, 'content_type', $val, 10);
$oneset->help = htmlentities($this->Lang('content_type_help'));
$ungrouped[] = $oneset;

$smarty->assign('ungrouped', $ungrouped);

$smarty->assign('start_page_set',$this->CreateFieldsetStart(null, 'title_description', $this->Lang('title_title_description')));
$pageset = array();
//ptitle
$oneset = new stdClass();
$oneset->title = $this->Lang('title_title');
$val = (!empty($meta['title'])) ? $meta['title']['value']:'{title} | {$sitename} - {$title_keywords}';
$oneset->input = $this->CreateInputText(null, 'title', $val, 60);
$oneset->help = htmlentities($this->Lang('title_help'));
$pageset[] = $oneset;
//mtitle
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_title_title');
$val = (!empty($meta['meta_std_title'])) ? $meta['meta_std_title']['value']:'{title} | {$sitename}';
$oneset->input = $this->CreateInputText(null, 'meta_std_title', $val, 60);
$oneset->help = htmlentities($this->Lang('meta_std_title_help'));
$pageset[] = $oneset;
//blockname
$oneset = new stdClass();
$oneset->title = $this->Lang('description_block_title');
$oneset->input = $this->CreateInputText(null, 'description_block', $this->GetPreference('description_block',''), 60);
$oneset->help = htmlentities($this->Lang('description_block_help'));
$pageset[] = $oneset;
//autodesc
$oneset = new stdClass();
$oneset->title = $this->Lang('description_auto_generate_title');
$oneset->inline = 1;
$oneset->input = $this->CreateInputCheckbox(null, 'description_auto_generate', 1, $this->GetPreference('description_auto_generate',0));
//$oneset->help = ;
$pageset[] = $oneset;
//autotext
$oneset = new stdClass();
$oneset->title = $this->Lang('description_auto_title');
$oneset->input = $this->CreateInputText(null, 'description_auto', $this->GetPreference('description_auto','This page covers the topics {keywords}'), 40);
$oneset->help = htmlentities($this->Lang('description_auto_help'));
$pageset[] = $oneset;

$smarty->assign('pageset', $pageset);

/* META Types */

$smarty->assign('start_meta_set',$this->CreateFieldsetStart(null, 'meta_type', $this->Lang('title_meta_type')));
$metatypes = array();
$groups = $db->GetAssoc('SELECT gname,active FROM '.$pre.
	'module_seotools_group WHERE gname != \'before\' AND gname != \'after\' ORDER BY vieworder');
foreach ($groups as $name=>$act) {
	$oneset = new stdClass();
	$oneset->title = $this->Lang($name.'_title');
	$oneset->input = $this->CreateInputCheckbox(null, $name, 1, $act);
//	$oneset->help = htmlentities($this->Lang($name.'_help'));
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
$metadeflts = array();
//publish
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_publisher_title');
$val = (!empty($meta['meta_std_publisher'])) ? $meta['meta_std_publisher']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_std_publisher', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_std_publisher_help'));
$metadeflts[] = $oneset;
//contrib
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_contributor_title');
$val = (!empty($meta['meta_std_contributor'])) ? $meta['meta_std_contributor']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_std_contributor', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_std_contributor_help'));
$metadeflts[] = $oneset;
//copyr
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_copyright_title');
$val = (!empty($meta['meta_std_copyright'])) ? $meta['meta_std_copyright']['value']:'(C) '.date('Y').'. All rights reserved.';
$oneset->input = $this->CreateInputText(null, 'meta_std_copyright', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_std_copyright_help'));
$metadeflts[] = $oneset;
//location
$oneset = new stdClass();
$oneset->head = $this->Lang('meta_std_location_description');
$oneset->title = $this->Lang('meta_std_location_title');
$val = (!empty($meta['meta_std_location'])) ? $meta['meta_std_location']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_std_location', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_std_location_help'));
$metadeflts[] = $oneset;
//region
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_region_title');
$val = (!empty($meta['meta_std_region'])) ? $meta['meta_std_region']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_std_region', $val, 5);
$oneset->help = htmlentities($this->Lang('meta_std_region_help'));
$metadeflts[] = $oneset;
//lat
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_latitude_title');
$val = (!empty($meta['meta_std_latitude'])) ? $meta['meta_std_latitude']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_std_latitude', $val, 15);
$oneset->help = htmlentities($this->Lang('meta_std_latitude_help'));
$metadeflts[] = $oneset;
//long
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_std_longitude_title');
$val = (!empty($meta['meta_std_longitude'])) ? $meta['meta_std_longitude']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_std_longitude', $val, 15);
$oneset->help = htmlentities($this->Lang('meta_std_longitude_help'));
$metadeflts[] = $oneset;
//ogtitle
$oneset = new stdClass();
$oneset->head = $this->Lang('meta_og_description');
$oneset->title = $this->Lang('meta_og_title_title');
$val = (!empty($meta['meta_og_title'])) ? $meta['meta_og_title']['value']:'{title}';
$oneset->input = $this->CreateInputText(null, 'meta_og_title', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_og_title_help'));
$metadeflts[] = $oneset;
//ogtype
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_og_type_title');
$val = (!empty($meta['meta_og_type'])) ? $meta['meta_og_type']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_og_type', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_og_type_help'));
$metadeflts[] = $oneset;
//ogsite
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_og_sitename_title');
$val = (!empty($meta['meta_og_sitename'])) ? $meta['meta_og_sitename']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_og_sitename', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_og_sitename_help'));
$metadeflts[] = $oneset;
//ogimage
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_og_image_title');
$val = (!empty($meta['meta_og_image'])) ? $meta['meta_og_image']['value']:-1;
$oneset->input = $this->CreateInputDropdown(null, 'meta_og_image', $img_files, null, $val);
$oneset->help = htmlentities($this->Lang('meta_og_image_help'));
$metadeflts[] = $oneset;
//ogadmin
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_og_admins_title');
$val = (!empty($meta['meta_og_admins'])) ? $meta['meta_og_admins']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_og_admins', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_og_admins_help'));
$metadeflts[] = $oneset;
//ogapp
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_og_application_title');
$val = (!empty($meta['meta_og_application'])) ? $meta['meta_og_application']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_og_application', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_og_application_help'));
$metadeflts[] = $oneset;

$oneset = new stdClass();
$oneset->head = $this->Lang('meta_twt_description');
$oneset->title = $this->Lang('meta_twt_card_title');
$val = (!empty($meta['meta_twt_card'])) ? $meta['meta_twt_card']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_twt_card', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_twt_card_help'));
$metadeflts[] = $oneset;
/*
'meta_twt_card'       
'meta_twt_site'       
'meta_twt_title'      
'meta_twt_description'
'meta_twt_creator'
'meta_twt_image'
*/
$oneset = new stdClass();
$oneset->head = $this->Lang('meta_gplus_description');
$oneset->title = $this->Lang('meta_gplus_name_title');
$val = (!empty($meta['meta_gplus_name'])) ? $meta['meta_gplus_name']['value']:'';
$oneset->input = $this->CreateInputText(null, 'meta_gplus_name', $val, 32);
$oneset->help = htmlentities($this->Lang('meta_gplus_name_help'));
$metadeflts[] = $oneset;
/*
'meta_gplus_name'
'meta_gplus_description'
'meta_gplus_image'
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
/*
'meta_gplus_description'
'meta_gplus_name'
ETC
'meta_twt_description'
'meta_twt_site'
'meta_twt_title'
ETC
*/


$smarty->assign('metadeflts', $metadeflts);

/* Additional Meta Tags */

$smarty->assign('start_extra_set',$this->CreateFieldsetStart(null, 'meta_additional', $this->Lang('title_meta_additional')));

//extra
$extraset = array();
$oneset = new stdClass();
$oneset->title = $this->Lang('meta_additional_title');
$val = (!empty($meta['meta_additional'])) ? $meta['meta_additional']['value']:'';
$oneset->input = $this->CreateTextArea(false, null, $val, 'meta_additional',
	'', '', '', '', 60, 5, '', '', 'style="height:10em;"');
$oneset->help = htmlentities($this->Lang('meta_additional_help'));
$extraset[] = $oneset;

$smarty->assign('extraset', $extraset);
//$smarty->assign('', $);

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
//create_map
$oneset = new stdClass();
$oneset->title = $this->Lang('create_sitemap_title').' *';
$oneset->inline = 1;
$oneset->input = $this->CreateInputCheckbox(null, 'create_sitemap', 1, $this->GetPreference('create_sitemap',0));
//$oneset->help = ;
$fileset[] = $oneset;
//push_map
$oneset = new stdClass();
$oneset->title = $this->Lang('push_sitemap_title');
if (ini_get('allow_url_fopen') || function_exists('curl_version')) {
	$oneset->inline = 1;
	$i = $this->CreateInputCheckbox(null, 'push_sitemap', 1, $this->GetPreference('push_sitemap',0));
}
else {
	$i = $this->Lang('no_pusher');
}
$oneset->input = $i;
//$oneset->help = ;
$fileset[] = $oneset;
//verify_code
$oneset = new stdClass();
$oneset->title = $this->Lang('verification_title');
$val = (!empty($meta['verification'])) ? $meta['verification']['value']:'';
$oneset->input = $this->CreateInputText(null, 'verification', $val, 40);
$oneset->help = htmlentities($this->Lang('verification_help'));
$fileset[] = $oneset;
//create_bots
$oneset = new stdClass();
$oneset->title = $this->Lang('create_robots_title').' *';
$oneset->inline = 1;
$oneset->input = $this->CreateInputCheckbox(null, 'create_robots', 1, $this->GetPreference('create_robots',0));
//$oneset->help = ;
$fileset[] = $oneset;
//early_bots
$oneset = new stdClass();
$oneset->title = $this->Lang('custom_before_title');
$oneset->input = $this->CreateTextArea(false, null, $this->GetPreference('robot_start',''),
 'robot_start', '', '', '', '', 50, 5, '', '', 'style="height:5em;width:50em;"'); //needs inline styling
//$oneset->help = ;
$fileset[] = $oneset;
//late_bots
$oneset = new stdClass();
$oneset->title = $this->Lang('custom_after_title');
$oneset->input = $this->CreateTextArea(false, null, $this->GetPreference('robot_end',''),
 'robot_end', '', '', '', '', 50, 5, '', '', 'style="height:5em;width:50em;"');
//$oneset->help = ;
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
