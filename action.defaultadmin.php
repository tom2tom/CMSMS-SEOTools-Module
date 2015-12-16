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
		$funcs = new SEO_file();
		if ($this->GetPreference('create_robots',0)) {
			$funcs->createRobotsTXT($this);
		}
		if ($this->GetPreference('create_sitemap',0)) {
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
			$funcs = new SEO_file();
			$funcs->createSitemap($this);
		}
*/
		break;
	case 'reset_priority':
		$query = 'UPDATE '.$pre.'module_seotools SET priority=NULL WHERE content_id=?';
		$db->Execute($query,array($cid));
/* only manual updates
		if ($this->GetPreference('create_sitemap',0)) {
			$funcs = new SEO_file();
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
			if (!array_key_exists('active', $alert) || $alert['active'] == TRUE) {
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
						$oneset->action = $funcs->getFixLink($this, $_GET[$this->pathstr], $id);
						$sig = '@'.$id.'-'.$data[1];
					}
				}
				else {
					$s = array();
					$sig = '';
					foreach($links as $id => $data) {
						$s[] = $funcs->getFixLink($this, $_GET[$this->pathstr], $id, $data[0]);
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
						$oneset->action = $funcs->getFixLink($this, $_GET[$this->pathstr], $id);
						$sig = '@'.$id.'-'.$data[1];
					}
				}
				else {
					$s = array();
					$sig = '';
					foreach($links as $id => $data) {
						$s[] = $funcs->getFixLink($this, $_GET[$this->pathstr], $id, $data[0]);
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

/* Page settings Tab */

$smarty->assign('startform_pages',$this->CreateFormStart($id, 'allindex'));
//$smarty->assign('title_id',$this->Lang('page_id'));
$smarty->assign('title_name',$this->Lang('page_name'));
$smarty->assign('title_priority',$this->Lang('priority'));
$smarty->assign('title_ogtype',$this->Lang('og_type'));
$smarty->assign('title_keywords',$this->Lang('keywords'));
$smarty->assign('title_desc',$this->Lang('description'));
$smarty->assign('title_index',$this->Lang('title_index'));

$iconreset = '<img src="'.$this->GetModuleURLPath().'/images/reset.png" class="systemicon" />';
$iconedit = '<img src="'.$theme_url.'/system/edit.gif" class="systemicon" />';
$icondown = '<img src="'.$theme_url.'/system/arrow-d.gif" class="systemicon" />';
$iconup = '<img src="'.$theme_url.'/system/arrow-u.gif" class="systemicon" />';
$default_ogtype = $this->GetPreference('meta_opengraph_type','');
$sep = $this->GetPreference('keyword_separator',' ');

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
				$description_auto = TRUE;
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
				$oneset->desc = '<a href="editcontent.php?'.$this->pathstr.'='.$_GET[$this->pathstr].
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

/* SEO Settings Tab */

// Get image files from /uploads/images
$files_list = array('('.$this->Lang('none').')'=>'');
$dp = opendir(cms_join_path($config['root_path'],'uploads','images'));
if ($dp) {
	while ($file = readdir($dp)) {
		if (strlen($file) >= 5) {
			foreach (array('.gif','.png','.jpg','.jpeg') as $type) {
				if (strripos($file, $type, -5) !== false) {
					$files_list[$file] = $file;
					break;
				}
			}
		}
	}
	closedir($dp);
}

$smarty->assign('cancel',$this->CreateInputSubmit(null, 'cancel', $this->Lang('cancel')));

$smarty->assign('startform_settings',$this->CreateFormStart($id, 'changesettings')); //several uses
/* Page Title */

$smarty->assign('pr_ctype',$this->Lang('title_type'));
$smarty->assign('in_ctype',$this->CreateInputText(null, 'content_type', $this->GetPreference('content_type','html'), 10)
	.'<br />'.$this->Lang('help_content_type'));
$smarty->assign('start_page_set',$this->CreateFieldsetStart(null, 'title_description', $this->Lang('title_title_description')));
$smarty->assign('pr_ptitle',$this->Lang('title_title'));
$smarty->assign('in_ptitle',$this->CreateInputText(null, 'title', $this->GetPreference('title','{title} | {$sitename} - {$title_keywords}'), 60)
	.'<br />'.$this->Lang('title_title_help'));
$smarty->assign('pr_mtitle',$this->Lang('title_meta_title'));
$smarty->assign('in_mtitle',$this->CreateInputText(null, 'meta_title', $this->GetPreference('meta_title','{title} | {$sitename}'), 60)
	.'<br />'.$this->Lang('title_meta_help'));
$smarty->assign('pr_blockname',$this->Lang('title_description_block'));
$smarty->assign('in_blockname',$this->CreateInputText(null, 'description_block', $this->GetPreference('description_block',''), 60)
	.'<br />'.$this->Lang('description_block_help'));
$smarty->assign('pr_autodesc',$this->Lang('description_auto_generate'));
$smarty->assign('in_autodesc',$this->CreateInputCheckbox(null, 'description_auto_generate', 1, $this->GetPreference('description_auto_generate',0)));
$smarty->assign('pr_autotext',$this->Lang('description_auto_title'));
$smarty->assign('in_autotext',$this->CreateInputText(null, 'description_auto', $this->GetPreference('description_auto','This page covers the topics {keywords}'), 40)
	.'<br />'.$this->Lang('description_auto_help'));
/* META Types */
$smarty->assign('start_meta_set',$this->CreateFieldsetStart(null, 'meta_type', $this->Lang('title_meta_type')));
$smarty->assign('pr_meta_stand',$this->Lang('meta_create_standard'));
$smarty->assign('in_meta_stand',$this->CreateInputCheckbox(null, 'meta_standard', 1, $this->GetPreference('meta_standard',0)));
$smarty->assign('pr_meta_dublin',$this->Lang('meta_create_dublincore'));
$smarty->assign('in_meta_dublin',$this->CreateInputCheckbox(null, 'meta_dublincore', 1, $this->GetPreference('meta_dublincore',0)));
$smarty->assign('pr_meta_open',$this->Lang('meta_create_opengraph'));
$smarty->assign('in_meta_open',$this->CreateInputCheckbox(null, 'meta_opengraph', 1, $this->GetPreference('meta_opengraph',0)));
/* META Defaults */
$smarty->assign('start_deflt_set',$this->CreateFieldsetStart(null, 'meta_defaults', $this->Lang('title_meta_defaults')));
$smarty->assign('pr_publish',$this->Lang('meta_publisher'));
$smarty->assign('in_publish',$this->CreateInputText(null, 'meta_publisher', $this->GetPreference('meta_publisher',''), 32)
	.'<br />'.$this->Lang('meta_publisher_help'));
$smarty->assign('pr_contrib',$this->Lang('meta_contributor'));
$smarty->assign('in_contrib',$this->CreateInputText(null, 'meta_contributor', $this->GetPreference('meta_contributor',''), 32)
	.'<br />'.$this->Lang('meta_contributor_help'));
$smarty->assign('pr_copyr',$this->Lang('meta_copyright'));
$smarty->assign('in_copyr',$this->CreateInputText(null, 'meta_copyright', $this->GetPreference('meta_copyright','(C) '.date('Y').'. All rights reserved.'), 32)
	.'<br />'.$this->Lang('meta_copyright_help'));
$smarty->assign('intro_location',$this->Lang('meta_location_description'));
$smarty->assign('pr_location',$this->Lang('meta_location'));
$smarty->assign('in_location',$this->CreateInputText(null, 'meta_location', $this->GetPreference('meta_location',''), 32)
	.'<br />'.$this->Lang('meta_location_help'));
$smarty->assign('pr_region',$this->Lang('meta_region'));
$smarty->assign('in_region',$this->CreateInputText(null, 'meta_region', $this->GetPreference('meta_region',''), 5, 5)
	.'<br />'.$this->Lang('meta_region_help'));
$smarty->assign('pr_lat',$this->Lang('meta_latitude'));
$smarty->assign('in_lat',$this->CreateInputText(null, 'meta_latitude', $this->GetPreference('meta_latitude',''), 15)
	.'<br />'.$this->Lang('meta_latitude_help'));
$smarty->assign('pr_long',$this->Lang('meta_longitude'));
$smarty->assign('in_long',$this->CreateInputText(null, 'meta_longitude', $this->GetPreference('meta_longitude',''), 15)
	.'<br />'.$this->Lang('meta_longitude_help'));
$smarty->assign('intro_ogmeta',$this->Lang('meta_opengraph_description'));
$smarty->assign('pr_ogtitle',$this->Lang('meta_opengraph_title'));
$smarty->assign('in_ogtitle',$this->CreateInputText(null, 'meta_opengraph_title', $this->GetPreference('meta_opengraph_title','{title}'), 32)
	.'<br />'.$this->Lang('meta_opengraph_title_help'));
$smarty->assign('pr_ogtype',$this->Lang('meta_opengraph_type'));
$smarty->assign('in_ogtype',$this->CreateInputText(null, 'meta_opengraph_type', $this->GetPreference('meta_opengraph_type',''), 32)
	.'<br />'.$this->Lang('meta_opengraph_type_help'));
$smarty->assign('pr_ogsite',$this->Lang('meta_opengraph_sitename'));
$smarty->assign('in_ogsite',$this->CreateInputText(null, 'meta_opengraph_sitename', $this->GetPreference('meta_opengraph_sitename',''), 32)
	.'<br />'.$this->Lang('meta_opengraph_sitename_help'));
$smarty->assign('pr_ogimage',$this->Lang('meta_opengraph_image'));
$smarty->assign('in_ogimage',$this->CreateInputDropdown(null, 'meta_opengraph_image', $files_list, null, $this->GetPreference('meta_opengraph_image',''))
	.'<br />'.$this->Lang('meta_opengraph_image_help'));
$smarty->assign('pr_ogadmin',$this->Lang('meta_opengraph_admins'));
$smarty->assign('in_ogadmin',$this->CreateInputText(null, 'meta_opengraph_admins', $this->GetPreference('meta_opengraph_admins',''), 32)
	.'<br />'.$this->Lang('meta_opengraph_admins_help'));
$smarty->assign('pr_ogapp',$this->Lang('meta_opengraph_application'));
$smarty->assign('in_ogapp',$this->CreateInputText(null, 'meta_opengraph_application', $this->GetPreference('meta_opengraph_application',''), 32)
	.'<br />'.$this->Lang('meta_opengraph_application_help'));
/* Additional Meta Tags */
$smarty->assign('start_extra_set',$this->CreateFieldsetStart(null, 'additional_meta', $this->Lang('title_additional_meta_tags')));
$smarty->assign('pr_extra',$this->Lang('additional_meta_tags_title'));
$smarty->assign('in_extra',
	$this->CreateTextArea(false, null, $this->GetPreference('additional_meta_tags',''), 'additional_meta_tags', '', '', '', '', '60', '1','','','style="height:10em;"')
	.'<br />'.$this->Lang('additional_meta_tags_help'));

$smarty->assign('submit1',$this->CreateInputSubmit(null, 'save_meta_settings', $this->Lang('save')));

/* SITEMAP Settings */

$smarty->assign('start_map_set',$this->CreateFieldsetStart(null, 'sitemap_description', $this->Lang('title_sitemap_description')));
$smarty->assign('pr_create_map',$this->Lang('create_sitemap_title').' *');
$smarty->assign('in_create_map',$this->CreateInputCheckbox(null, 'create_sitemap', 1, $this->GetPreference('create_sitemap',0)));
$smarty->assign('pr_push_map',$this->Lang('push_sitemap_title'));
if (ini_get('allow_url_fopen') || function_exists('curl_version')) {
	$smarty->assign('in_push_map',$this->CreateInputCheckbox(null, 'push_sitemap', 1, $this->GetPreference('push_sitemap',0)));
}
else {
	$smarty->assign('input_push_map',$this->Lang('no_pusher'));
}
$smarty->assign('pr_verify_code',$this->Lang('verification_title'));
$smarty->assign('in_verify_code',$this->CreateInputText(null, 'verification', $this->GetPreference('verification',''), 40));
$smarty->assign('help_verify',$this->Lang('verification_help'));
$smarty->assign('pr_create_bots',$this->Lang('create_robots_title').' *');
$smarty->assign('in_create_bots',$this->CreateInputCheckbox(null, 'create_robots', 1, $this->GetPreference('create_robots',0)));
$smarty->assign('pr_early_bots',$this->Lang('custom_before_title'));
$smarty->assign('in_early_bots',$this->CreateTextArea(false, null, $this->GetPreference('robot_start',''),
 'robot_start', '', '', '', '', 50, 5, '', '', 'style="height:5em;width:50em;"')); //needs inline styling
$smarty->assign('pr_late_bots',$this->Lang('custom_after_title'));
$smarty->assign('in_late_bots',$this->CreateTextArea(false, null, $this->GetPreference('robot_end',''),
 'robot_end', '', '', '', '', 50, 5, '', '', 'style="height:5em;width:50em;"'));
$smarty->assign('submit2',$this->CreateInputSubmit(null, 'save_sitemap_settings', $this->Lang('save')));

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

/* KEYWORD Settings */
$smarty->assign('start_ksettings_set',$this->CreateFieldsetStart(null, 'keyword_weight_description', $this->Lang('title_keyword_weight')));

$smarty->assign('pr_wordsblock_name',$this->Lang('title_keyword_block'));
$smarty->assign('in_wordsblock_name',$this->CreateInputText(null, 'keyword_block', $this->GetPreference('keyword_block',''), 60)
	.'<br />'.$this->Lang('keyword_block_help'));
$smarty->assign('pr_kw_sep',$this->Lang('keyword_separator_title'));
$smarty->assign('in_kw_sep',$this->CreateInputText(null, 'keyword_separator', $sep, 1)
	.'<br />'.$this->Lang('keyword_separator_help'));
$smarty->assign('pr_min_length',$this->Lang('keyword_minlength_title'));
$smarty->assign('in_min_length',$this->CreateInputText(null, 'keyword_minlength', $this->GetPreference('keyword_minlength','6'), 2)
	.'<br />'.$this->Lang('keyword_minlength_help'));
$smarty->assign('pr_title_weight',$this->Lang('keyword_title_weight_title'));
$smarty->assign('in_title_weight',$this->CreateInputText(null, 'keyword_title_weight', $this->GetPreference('keyword_title_weight','6'), 2)
	.'<br />'.$this->Lang('keyword_title_weight_help'));
$smarty->assign('pr_desc_weight',$this->Lang('keyword_description_weight_title'));
$smarty->assign('in_desc_weight',$this->CreateInputText(null, 'keyword_description_weight', $this->GetPreference('keyword_description_weight','4'), 2)
	.'<br />'.$this->Lang('keyword_description_weight_help'));
$smarty->assign('pr_head_weight',$this->Lang('keyword_headline_weight_title'));
$smarty->assign('in_head_weight',$this->CreateInputText(null, 'keyword_headline_weight', $this->GetPreference('keyword_headline_weight','2'), 2)
	.'<br />'.$this->Lang('keyword_headline_weight_help'));
$smarty->assign('pr_cont_weight',$this->Lang('keyword_content_weight_title'));
$smarty->assign('in_cont_weight',$this->CreateInputText(null, 'keyword_content_weight', $this->GetPreference('keyword_content_weight','1'), 2)
	.'<br />'.$this->Lang('keyword_content_weight_help'));
$smarty->assign('pr_min_weight',$this->Lang('keyword_minimum_weight_title'));
$smarty->assign('in_min_weight',$this->CreateInputText(null, 'keyword_minimum_weight', $this->GetPreference('keyword_minimum_weight','7'), 2)
	.'<br />'.$this->Lang('keyword_minimum_weight_help'));
$smarty->assign('start_kexclude_set',$this->CreateFieldsetStart(null, 'keyword_exclude_description', $this->Lang('title_keyword_exclude')));

$smarty->assign('pr_incl_words',$this->Lang('default_keywords_title'));
$smarty->assign('in_incl_words',
	$this->CreateTextArea(false, null, $this->GetPreference('default_keywords',''), 'default_keywords', '', '', '', '', '60', '1','','','style="height:5em;"')
	.'<br />'.$this->Lang('default_keywords_help'));
$smarty->assign('pr_excl_words',$this->Lang('keyword_exclude_title'));
$smarty->assign('in_excl_words',
	$this->CreateTextArea(false, null, $this->GetPreference('keyword_exclude',''), 'keyword_exclude', '', '', '', '', '60', '1','','','style="height:5em;"')
	.'<br />'.$this->Lang('keyword_exclude_help'));
$smarty->assign('submit3',$this->CreateInputSubmit(null, 'save_keyword_settings', $this->Lang('save')));
$smarty->assign('keyword_help',$this->Lang('help_keyword_generator'));

echo $this->ProcessTemplate('adminpanel.tpl');

?>
