<?php
# This file is part of CMS Made Simple module: SEOTools.
# Copyright (C) 2010-2011 Henning Schaefer <henning.schaefer@gmail.com>
# Copyright (C) 2011-2016 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SEOTools.module.php

class SEO_utils
{
	/**
	ProcessTemplate:
	@mod: reference to current SEOTools module object
	@tplname: template identifier
	@tplvars: associative array of template variables
	@cache: optional boolean, default TRUE
	Returns: string, processed template
	*/
	public static function ProcessTemplate(&$mod,$tplname,$tplvars,$cache=TRUE)
	{
		if ($mod->before20) {
			global $smarty;
		} else {
			$smarty = $mod->GetActionTemplateObject();
			if (!$smarty) {
				global $smarty;
			}
		}
		$smarty->assign($tplvars);
		if ($mod->oldtemplates) {
			return $mod->ProcessTemplate($tplname);
		} else {
			if ($cache) {
				$cache_id = md5('seo'.$tplname.serialize(array_keys($tplvars)));
				$lang = CmsNlsOperations::get_current_language();
				$compile_id = md5('seo'.$tplname.$lang);
				$tpl = $smarty->CreateTemplate($mod->GetFileResource($tplname),$cache_id,$compile_id,$smarty);
				if (!$tpl->isCached()) {
					$tpl->assign($tplvars);
				}
			} else {
				$tpl = $smarty->CreateTemplate($mod->GetFileResource($tplname),NULL,NULL,$smarty,$tplvars);
			}
			return $tpl->fetch();
		}
	}

	/**
	ProcessTemplateFromData:
	@mod: reference to current SEOTools module object
	@data: string
	@tplvars: associative array of template variables
	No cacheing.
	Returns: string, processed template
	*/
	public static function ProcessTemplateFromData(&$mod,$data,$tplvars)
	{
		if ($mod->before20) {
			global $smarty;
		} else {
			$smarty = $mod->GetActionTemplateObject();
			if (!$smarty) {
				global $smarty;
			}
		}
		$smarty->assign($tplvars);
		if ($mod->oldtemplates) {
			return $mod->ProcessTemplateFromData($data);
		} else {
			$tpl = $smarty->CreateTemplate('eval:'.$data,NULL,NULL,$smarty,$tplvars);
			return $tpl->fetch();
		}
	}

	/**
	DisplayErrorPage:
	@message:
	*/
	public static function DisplayErrorPage($message)
	{
		$tplvars = array(
			'title_error' => $this->Lang('error'),
			'message' => $message
		);
		echo self::ProcessTemplate($this,'error.tpl',$tplvars);
	}
}
?>
