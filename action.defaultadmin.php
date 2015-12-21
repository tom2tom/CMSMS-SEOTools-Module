<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright(C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright(C) 2014-2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

# Setup and display admin page, after processing any action-request

function langval(&$mod,$key,$def) {
	static $cmsvers = 0;
	static $trans = NULL;
	static $realm;

	if ($cmsvers == 0) {
		$cmsvers = ($mod->before20) ? 1:2;
		if ($cmsvers == 1) {
			$var = cms_current_language(); //CMSMS 1.8+
			$trans = $mod->langhash[$var];
		}
		else {
			$realm = $mod->GetName();
		}
	}
	if ($cmsvers == 1) {
		if (array_key_exists($key,$trans)) {
			//NOTE $trans[] values could be any encoding
			//use $mod->Lang($k) to transcode to UTF-8, interpret embedded params etc
			return $trans[$key];
		}
		else {
			return $def;
		}
	}
	else {
		if (CmsLangOperations::key_exists($key,$realm)) {
			return CmsLangOperations::lang_from_realm($realm,$key);
		}
		else {
			return $def;
		}
	}
}

function vardata_text(&$mod, &$meta, &$out, $name, $len, $def = '') {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = (!empty($meta[$name])) ? $meta[$name]['value']:$def;
	$oneset->input = $mod->CreateInputText(null, $name, $val, $len, $len);
	$out[$name] = $oneset;
}

function vardata_textarea(&$mod, &$meta, &$out, $name, $rows, $def = '') {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = (!empty($meta[$name])) ? $meta[$name]['value']:$def;
	$oneset->input = $mod->CreateTextArea(false, null, $val, $name,
		'', '', '', '', 60, $rows, '', '', 'style="height:'.($rows+1).'em;"');
	$out[$name] = $oneset;
}

function vardata_check(&$mod, &$meta, &$out, $name, $def = 0) {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = (!empty($meta[$name])) ? (int)$meta[$name]['value']:$def;
	$oneset->input = '<input type="hidden" name="'.$name.'" value="0" />'. //ensure an 'unchecked' report
		$mod->CreateInputCheckbox(null, $name, 1, $val);
	$oneset->inline = true;
	$out[$name] = $oneset;
}

function vardata_drop(&$mod, &$meta, &$out, $name, $choices, $def = '') {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = (!empty($meta[$name])) ? $meta[$name]['value']:$def;
	$oneset->input = '<input type="hidden" name="'.$name.'" value="0" />'. //ensure a 'false' report
		$mod->CreateInputDropdown(null, $name, $choices, $def, -1);
	$out[$name] = $oneset;
}

function varpref_text(&$mod, &$out, $name, $len, $def = '') {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = $mod->GetPreference($name, $def);
	$oneset->input = $mod->CreateInputText(null, $name, $val, $len, $len);
	$out[$name] = $oneset;
}

function varpref_textarea(&$mod, &$out, $name, $rows, $def = '') {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = $mod->GetPreference($name, $def);
	$oneset->input = $mod->CreateTextArea(false, null, $val, $name,
		'', '', '', '', 60, $rows, '', '', 'style="height:'.($rows+1).'em;"');
	$out[$name] = $oneset;
}

function varpref_check(&$mod, &$out, $name, $def = 0) {
	$oneset = new stdClass();
	$oneset->title = langval($mod,$name.'_title',$name);
	$oneset->help = langval($mod,$name.'_help',null);
	$val = (int)$mod->GetPreference($name, $def);
	$oneset->input = '<input type="hidden" name="'.$name.'" value="0" />'. //ensure an 'unchecked' report
		$mod->CreateInputCheckbox(null, $name, 1, $val);
	$oneset->inline = true;
	$out[$name] = $oneset;
}

$pset = $this->CheckAccess('Edit SEO Settings');
$pdesc = $this->CheckAccess('Edit page descriptions');
if (!($pset || $pdesc)) {
	return $this->DisplayErrorPage($this->Lang('accessdenied'));
}
$smarty->assign('pset', $pset);

if (isset($_GET['tab'])) {
	$params['tab'] = $_GET['tab'];
//	unset($_GET['tab']);
}

$pre = cms_db_prefix();

if ($pset) {
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
//		unset($_GET['what']);
	}
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

if ($pset) {
	$smarty->assign('tab_headers',$this->StartTabHeaders().
		$this->SetTabHeader('alerts',$this->Lang('title_alerts'),$indx==0).
		$this->SetTabHeader('urgentfixes',$this->Lang('title_urgent'),$indx==1).
		$this->SetTabHeader('importantfixes',$this->Lang('title_important'),$indx==2).
		$this->SetTabHeader('pagedescriptions',$this->Lang('title_descriptions'),$indx==3).
		$this->SetTabHeader('metasettings',$this->Lang('title_metasettings'),$indx==4).
		$this->SetTabHeader('keywordsettings',$this->Lang('title_keywordsettings'),$indx==5).
		$this->SetTabHeader('sitemapsettings',$this->Lang('title_sitemapsettings'),$indx==6).
		$this->EndTabHeaders().$this->StartTabContent());
}
else {
	$smarty->assign('tab_headers',$this->StartTabHeaders().
		$this->SetTabHeader('alerts',$this->Lang('friendlyname'),$indx==0).
		$this->SetTabHeader('pagedescriptions',$this->Lang('title_descriptions'),$indx==3).
		$this->EndTabHeaders().$this->StartTabContent());
}

$smarty->assign('start_alerts_tab',$this->StartTab('alerts'));
if ($pset) {
	$smarty->assign('start_urgent_tab',$this->StartTab('urgentfixes'));
	$smarty->assign('start_important_tab',$this->StartTab('importantfixes'));
}
$smarty->assign('start_description_tab',$this->StartTab('pagedescriptions'));
if ($pset) {
	$smarty->assign('start_meta_tab',$this->StartTab('metasettings'));
	$smarty->assign('start_keyword_tab',$this->StartTab('keywordsettings'));
	$smarty->assign('start_sitemap_tab',$this->StartTab('sitemapsettings'));
}

$smarty->assign('end_set',$this->CreateFieldsetEnd());
//NOTE CMSMS2+ barfs if EndTab() is called before EndTabContent() - some craziness to fix !!!
$smarty->assign('tab_footers',$this->EndTabContent());
$smarty->assign('end_tab',$this->EndTab());
$smarty->assign('end_form',$this->CreateFormEnd());

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

if ($pset) {
	/* Alerts and Fixes Tabs */

	$smarty->assign('startform_problems',$this->CreateFormStart($id, 'allignore')); //several uses
	$smarty->assign('start_urgent_set',$this->CreateFieldsetStart($id, 'alerts_urgent', $this->Lang('title_alerts_urgent')));
	$smarty->assign('start_important_set',$this->CreateFieldsetStart($id, 'alerts_important', $this->Lang('title_alerts_important')));
	$smarty->assign('start_notice_set',$this->CreateFieldsetStart($id, 'alerts_notices', $this->Lang('title_alerts_notices')));

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
} //end if $pset


$smarty->assign('start_resources_set',$this->CreateFieldsetStart(null, 'resources',$this->Lang('title_resources')));
$smarty->assign('resource_links',array(
 '<a href="http://validator.w3.org">W3C validator</a>',
 '<a href="http://brokenlinkcheck.com">Link checker</a>',
 '<a href="http://www.feedthebot.com/tools">FeedtheBot</a>',
 '<a href="http://www.siteliner.com">Siteliner</a>'
));

if ($pset) {
	$smarty->assign('cancel',$this->CreateInputSubmit(null, 'cancel', $this->Lang('cancel')));

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
}

$meta = $db->GetAssoc('SELECT mname,value,output,smarty,active FROM '.$pre.'module_seotools_meta ORDER BY mname');

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
			$val = ($kw) ? implode(', ',$kw).'; ':'';
			$keywords = '('.$this->Lang('auto').') '.count($kw).' '.$this->CreateTooltipLink(null, 'defaultadmin', '', $iconedit, $val.$this->Lang('edit_value'), array('what'=>'edit_keywords','content_id'=>$row['content_id']));
			$iname = 'true';

			$query = 'SELECT * FROM '.$pre.'module_seotools WHERE content_id = ?';
			$info = $db->GetRow($query,array($row['content_id']));
			if ($info && $info['content_id'] != '') {
				if ($info['priority'] != 0) {
				  $priority = '<strong>'.$info['priority'] . '% '.$this->CreateTooltipLink(null, 'defaultadmin', '', $iconreset, $this->Lang('reset_to_default'), array('what'=>'reset_priority','content_id'=>$row['content_id'])) . '</strong>';
				  $auto_priority = $info['priority'];
				}
				if ($info['ogtype']) {
				  $ogtype = '<strong>'.$info['ogtype'] . ' '
				  . $this->CreateTooltipLink(null, 'defaultadmin', '', $iconreset, $this->Lang('reset_to_default'), array('what'=>'reset_ogtype','content_id'=>$row['content_id']))
				  . $this->CreateTooltipLink(null, 'defaultadmin', '', $iconedit, $this->Lang('edit_value'), array('what'=>'edit_ogtype','content_id'=>$row['content_id'])).'</strong>';
				}
				if ($info['keywords']) {
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

if (!$pset) {
	echo $this->ProcessTemplate('adminpanel.tpl');
	return;
}

$smarty->assign('index',$this->CreateInputSubmit(null, 'index_selected',
	$this->Lang('index'),'title="'.$this->Lang('help_index').'" onclick="return confirm_click(\'indx\');"'));
$smarty->assign('unindex',$this->CreateInputSubmit(null, 'unindex_selected',
	$this->Lang('unindex'),'title="'.$this->Lang('help_unindex').'" onclick="return confirm_click(\'indx\');"'));

/* SEO Settings Tab */

$smarty->assign('startform_settings',$this->CreateFormStart($id, 'changesettings'));

$metaset = array();

$ungrouped = array();

vardata_text($this, $meta, $ungrouped, 'content_type', 10, 'html');

$metaset[] = array(
	'',
	$ungrouped
);

$pagevals = array();

vardata_text($this, $meta, $pagevals, 'title', 60, '{title} | {$sitename} - {$title_keywords}');
vardata_text($this, $meta, $pagevals, 'meta_std_title', 60, '{title} | {$sitename}');

varpref_text($this, $pagevals, 'description_block', 60, 'metadescription');
varpref_check($this, $pagevals, 'description_auto_generate');
varpref_textarea($this, $pagevals, 'description_auto', 2, 'This page covers the topics {keywords}');

$metaset[] = array(
	$this->CreateFieldsetStart(null, 'title_description', $this->Lang('title_title_description')),
	$pagevals
);


/* META Types */

$metatypes = array();

$groups = $db->GetAssoc('SELECT gname,active FROM '.$pre.
	'module_seotools_group WHERE gname != \'before\' AND gname != \'after\' ORDER BY vieworder');
foreach ($groups as $name=>$act) {
	vardata_check($this, $meta, $metatypes, $name, $act);
}

$metaset[] = array(
	$this->CreateFieldsetStart(null, 'meta_type', $this->Lang('title_meta_type')),
	$metatypes
);

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

$metavals = array();

vardata_text($this, $meta, $metavals, 'meta_std_publisher', 32);
vardata_text($this, $meta, $metavals, 'meta_std_contributor', 32);
vardata_text($this, $meta, $metavals, 'meta_std_copyright', 32, '(C) '.date('Y').'. All rights reserved.');
vardata_text($this, $meta, $metavals, 'meta_std_location', 32);
$oneset = &$metavals['meta_std_location'];
$oneset->head = $this->Lang('meta_std_location_description');
vardata_text($this, $meta, $metavals, 'meta_std_region', 6);
vardata_text($this, $meta, $metavals, 'meta_std_latitude', 15);
vardata_text($this, $meta, $metavals, 'meta_std_longitude', 15);
vardata_text($this, $meta, $metavals, 'meta_og_title', 60, '{title}');
$oneset = &$metavals['meta_og_title'];
$oneset->head = $this->Lang('meta_og_description');
vardata_text($this, $meta, $metavals, 'meta_og_type', 32);
vardata_text($this, $meta, $metavals, 'meta_og_sitename', 25);
vardata_drop($this, $meta, $metavals, 'meta_og_image', $img_files);
vardata_text($this, $meta, $metavals, 'meta_og_admins', 48);
vardata_text($this, $meta, $metavals, 'meta_og_application', 32);
/* twitter metas derived from others
vardata_text($this, $meta, $metavals, 'meta_twt_title', 60, '{title}');
vardata_textarea($this, $meta, $metavals, 'meta_twt_description', 6);
*/
vardata_text($this, $meta, $metavals, 'meta_twt_card', 20);
$oneset = &$metavals['meta_twt_card'];
$oneset->head = $this->Lang('meta_twt_description');
vardata_drop($this, $meta, $metavals, 'meta_twt_image', $img_files);
vardata_text($this, $meta, $metavals, 'meta_twt_site', 18);
vardata_text($this, $meta, $metavals, 'meta_twt_creator', 18);
/* google+ metas derived from others
vardata_text($this, $meta, $metavals, 'meta_gplus_name', 60, '{title}');
$oneset = &$metavals['meta_gplus_name'];
$oneset->head = $this->Lang('meta_gplus_description');
vardata_textarea($this, $meta, $metavals, 'meta_gplus_description', 6);
vardata_drop($this, $meta, $metavals, 'meta_gplus_image', $img_files);
*/
$j = 0;
foreach ($meta as $name=>&$data) {
	if($name != 'UNUSED' && $data['value'] != 'UNUSED') {
		if (!($name == 'meta_additional' || $name == 'verification' //populated elsewhere
		   || array_key_exists($name,$pagevals)
		   || array_key_exists($name,$metavals)
		   || array_key_exists($name,$ungrouped))) {
			vardata_text($this, $meta, $metavals, $name, 40);
			if ($j == 0) {
				$oneset = &$metavals[$name];
				$oneset->head = $this->Lang('meta_new_description');
				$j = 1;
			}
		}
	}
}
unset($data);
unset($oneset);

$metaset[] = array(
	$this->CreateFieldsetStart(null, 'meta_values', $this->Lang('title_meta_values')),
	$metavals
);

/* Additional Meta Tags */

$extraset = array();

vardata_textarea($this, $meta, $extraset, 'meta_additional', 8);

$metaset[] = array(
	$this->CreateFieldsetStart(null, 'meta_additional', $this->Lang('title_meta_additional')),
	$extraset
);

$smarty->assign('metaset',$metaset);
$smarty->assign('submit1',$this->CreateInputSubmit(null, 'save_meta_settings', $this->Lang('save')));
$smarty->assign('display1',$this->CreateInputSubmit(null, 'display_metadata', $this->Lang('display'),
 'title="'.$this->Lang('meta_display').'"'));

/* KEYWORD Settings */

$keywordset = array();

$keyset = array();

varpref_text($this, $keyset, 'keyword_block', 60, 'metakeywords');
varpref_text($this, $keyset, 'keyword_separator', 1, ',');
varpref_text($this, $keyset, 'keyword_minlength', 2, '6');
varpref_text($this, $keyset, 'keyword_minimum_weight', 2, '7');
varpref_text($this, $keyset, 'keyword_title_weight', 2, '6');
varpref_text($this, $keyset, 'keyword_description_weight', 2, '4');
varpref_text($this, $keyset, 'keyword_headline_weight', 2, '2');
varpref_text($this, $keyset, 'keyword_content_weight', 2, '1');

$keywordset[] = array(
	$this->CreateFieldsetStart(null, 'keyword_gener_description', $this->Lang('title_keyword_gener')),
	$keyset
);

$listset = array();

varpref_textarea($this, $listset, 'keyword_default', 3);
varpref_textarea($this, $listset, 'keyword_exclude', 5);

$keywordset[] = array(
	$this->CreateFieldsetStart(null, 'keyword_list_description', $this->Lang('title_keyword_lists')),
	$listset
);

$smarty->assign('keywordset',$keywordset);
$smarty->assign('keyword_help',$this->Lang('help_keyword_generator'));
$smarty->assign('submit2',$this->CreateInputSubmit(null, 'save_keyword_settings', $this->Lang('save')));

/* SITEMAP Settings */

$sitemapset = array();

$fileset = array();

varpref_check($this, $fileset, 'create_sitemap');
varpref_check($this, $fileset, 'push_sitemap', $this->GetPreference('create_sitemap',0));
if (!(ini_get('allow_url_fopen') || function_exists('curl_version'))) {
	$oneset = &$fileset['push_sitemap'];
	$oneset->input = $this->Lang('no_pusher');
}

vardata_text($this, $meta, $fileset, 'verification', 40); //NOTE tabled value

varpref_check($this, $fileset, 'create_robots');
varpref_textarea($this, $fileset, 'robot_start', 5);
varpref_textarea($this, $fileset, 'robot_end', 5);

$sitemapset[] = array(
	$this->CreateFieldsetStart(null, 'sitemap_description', $this->Lang('title_sitemap_description')),
	$fileset
);

$smarty->assign('sitemapset',$sitemapset);
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
