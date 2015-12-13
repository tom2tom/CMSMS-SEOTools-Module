{if !empty($message)}{$message}{/if}
{$tab_headers}

{$start_alerts_tab}
<div class="pageoverflow">
{$start_urgent_set}
<p>{$urgent_icon}&nbsp;{$urgent_text}{if isset($urgent_link)}&nbsp;{$urgent_link}{/if}</p>
{$end_set}
{$start_important_set}
<p>{$important_icon}&nbsp;{$important_text}{if isset($important_link)}&nbsp;{$important_link}{/if}</p>
{$end_set}
{if isset($notices)}
 {$start_notice_set}
 {foreach from=$notices item=entry}
 <p>{$entry->icon}&nbsp;{$entry->text}{if isset($entry->link)}&nbsp;{$entry->link}{/if}</p>
 {/foreach}
 {$end_set}
{/if}
{if !empty($resource_links)}
{$start_resources_set}
<ul>
{foreach from=$resource_links item=one}<li>{$one}</li>{/foreach}
</ul>
{$end_set}
{/if}
</div>
{$end_tab}

{$start_urgent_tab}
{if !empty($urgents)}
{$startform_problems}
<div class="pageoverflow">
<table class="pagetable" style="border-spacing:0;">
 <tr>
  <th>{$title_pages}</th>
  <th>{$title_active}</th>
  <th>{$title_problem}</th>
  <th>{$title_action}</th>
  <th>{$title_ignored}</th>
  <th class="checkbox seocb"><input id="allurgent" type="checkbox" onclick="select_all('urgent');" /></th>
 </tr>
{foreach from=$urgents item=entry}
 <tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
  <td>{$entry->pages}</td>
  <td>{$entry->active}</td>
  <td>{$entry->problem}</td>
  <td>{$entry->action}</td>
  <td>{$entry->ignored}</td>
  <td><input type="checkbox" name="urgentsel[]"{if ($entry->sel)} checked="checked"{/if} value="{$entry->checkval}" /></td>
 </tr>
{/foreach}
</table>
<br />
<div style="float:right;">{$unignore1}&nbsp;{$ignore1}</div><div style="clear:right;"></div>
</div>
{$end_form}
{/if}
{$end_tab}

{$start_important_tab}
{if !empty($importants)}
{$startform_problems}
<div class="pageoverflow">
<table class="pagetable" style="border-spacing:0;">
 <tr>
  <th>{$title_pages}</th>
  <th>{$title_active}</th>
  <th>{$title_problem}</th>
  <th>{$title_action}</th>
  <th>{$title_ignored}</th>
  <th class="checkbox seocb"><input id="allimportant" type="checkbox" onclick="select_all('important');" /></th>
 </tr>
{foreach from=$importants item=entry}
 <tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
  <td>{$entry->pages}</td>
  <td>{$entry->active}</td>
  <td>{$entry->problem}</td>
  <td>{$entry->action}</td>
  <td>{$entry->ignored}</td>
  <td><input type="checkbox" name="importantsel[]"{if ($entry->sel)} checked="checked"{/if} value="{$entry->checkval}" /></td>
 </tr>
{/foreach}
</table>
<br />
<div style="float:right;">{$unignore2}&nbsp;{$ignore2}</div><div style="clear:right;"></div>
</div>
{$end_form}
{/if}
{$end_tab}

{$start_description_tab}
{$startform_pages}
<div class="pageoverflow">
<table class="pagetable" style="border-spacing:0;">
 <tr>
  <th>{$title_name}</th>
  <th>{$title_priority}</th>
  <th>{$title_ogtype}</th>
  <th>{$title_keywords}</th>
  <th>{$title_desc}</th>
  <th>{$title_index}</th>
  <th class="checkbox seocb"><input id="allindx" type="checkbox" onclick="select_all('indx');" /></th>
 </tr>
{if isset($items)}
 {foreach from=$items item=entry}
  <tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
   <td>{$entry->name}</td>
   <td>{$entry->priority}</td>
   <td>{$entry->ogtype}</td>
   <td>{$entry->keywords}</td>
   <td>{$entry->desc}</td>
   <td>{$entry->index}</td>
{if $entry->index}
   <td><input type="checkbox" name="indxsel[]"{if ($entry->sel)} checked="checked"{/if} value="{$entry->checkval}" /></td>
{else}
   <td></td>
{/if}
  </tr>
 {/foreach}
{/if}
</table>
<br />
{if isset($items)}
<div style="float:right;">{$unindex}&nbsp;{$index}</div><div style="clear:right;"></div>
{/if}
</div>
{$end_form}
{$end_tab}

{$start_meta_tab}
{$startform_settings}
  <p class="pagetext" style="margin-top:0;">{$pr_ctype}:</p>
  <p class="pageinput">{$in_ctype}</p>
{$start_page_set}
 <div class="pageoverflow">
  <p class="pagetext" style="margin-top:0;">{$pr_ptitle}:</p>
  <p class="pageinput">{$in_ptitle}</p>
  <p class="pagetext">{$pr_mtitle}:</p>
  <p class="pageinput">{$in_mtitle}</p>
  <p class="pagetext">{$pr_blockname}:</p>
  <p class="pageinput">{$in_blockname}</p>
  <p class="pagetext">{$pr_autodesc}:&nbsp;{$in_autodesc}</p>
  <p class="pagetext">{$pr_autotext}:</p>
  <p class="pageinput">{$in_autotext}</p>
 </div>
{$end_set}
{$start_meta_set}
 <div class="pageoverflow">
  <p class="pagetext" style="margin-top:0;">{$pr_meta_stand}&nbsp;{$in_meta_stand}</p>
  <p class="pagetext">{$pr_meta_dublin}&nbsp;{$in_meta_dublin}</p>
  <p class="pagetext">{$pr_meta_open}&nbsp;{$in_meta_open}</p>
 </div>
{$end_set}
{$start_deflt_set}
 <div class="pageoverflow">
  <p class="pagetext" style="margin-top:0;">{$pr_publish}:</p>
  <p class="pageinput">{$in_publish}</p>
  <p class="pagetext">{$pr_contrib}:</p>
  <p class="pageinput">{$in_contrib}</p>
  <p class="pagetext">{$pr_copyr}:</p>
  <p class="pageinput">{$in_copyr}</p>
  <h4 class="pageinput" style="margin-top:15px;">{$intro_location}:</h4>
  <p class="pagetext">{$pr_location}:</p>
  <p class="pageinput">{$in_location}</p>
  <p class="pagetext">{$pr_region}:</p>
  <p class="pageinput">{$in_region}</p>
  <p class="pagetext">{$pr_lat}:</p>
  <p class="pageinput">{$in_lat}</p>
  <p class="pagetext">{$pr_long}:</p>
  <p class="pageinput">{$in_long}</p>
  <h4 class="pageinput" style="margin-top:15px;">{$intro_ogmeta}:</h4>
  <p class="pagetext">{$pr_ogtitle}:</p>
  <p class="pageinput">{$in_ogtitle}</p>
  <p class="pagetext">{$pr_ogtype}:</p>
  <p class="pageinput">{$in_ogtype}</p>
  <p class="pagetext">{$pr_ogsite}:</p>
  <p class="pageinput">{$in_ogsite}</p>
  <p class="pagetext">{$pr_ogimage}:</p>
  <p class="pageinput">{$in_ogimage}</p>
  <p class="pagetext">{$pr_ogadmin}:</p>
  <p class="pageinput">{$in_ogadmin}</p>
  <p class="pagetext">{$pr_ogapp}:</p>
  <p class="pageinput">{$in_ogapp}</p>
 </div>
{$end_set}
{$start_extra_set}
 <p class="pagetext" style="margin-top:0;">{$pr_extra}:</p>
 <p class="pageinput">{$in_extra}</p>
{$end_set}
<br />
<p class="pageinput">{$submit1}&nbsp;{$cancel}</p>
{$end_form}
{$end_tab}

{$start_keyword_tab}
{$startform_settings}
{$start_ksettings_set}
 <div class="pageoverflow">
  <p class="pagetext" style="margin-top:0;">{$pr_min_length}:</p>
  <p class="pageinput">{$in_min_length}</p>
  <p class="pagetext">{$pr_title_weight}:</p>
  <p class="pageinput">{$in_title_weight}</p>
  <p class="pagetext">{$pr_desc_weight}:</p>
  <p class="pageinput">{$in_desc_weight}</p>
  <p class="pagetext">{$pr_head_weight}:</p>
  <p class="pageinput">{$in_head_weight}</p>
  <p class="pagetext">{$pr_cont_weight}:</p>
  <p class="pageinput">{$in_cont_weight}</p>
  <p class="pagetext">{$pr_min_weight}:</p>
  <p class="pageinput">{$in_min_weight}</p>
 </div>
{$end_set}
{$start_kexclude_set}
 <div class="pageoverflow">
  <p class="pagetext" style="margin-top:0;">{$pr_wordsblock_name}:</p>
  <p class="pageinput">{$in_wordsblock_name}</p>
  <p class="pagetext">{$pr_kw_sep}:</p>
  <p class="pageinput">{$in_kw_sep}</p>
  <p class="pagetext">{$pr_incl_words}:</p>
  <p class="pageinput">{$in_incl_words}</p>
  <p class="pagetext">{$pr_excl_words}:</p>
  <p class="pageinput">{$in_excl_words}</p>
 </div>
{$end_set}
<br />
<p class="pageinput">{$submit3}&nbsp;{$cancel}</p>
{$end_form}
<br />
{$keyword_help}
{$end_tab}

{$start_sitemap_tab}
{$startform_settings}
{$start_map_set}
 <div class="pageoverflow">
  <p class="pagetext" style="margin-top:0;">{$pr_create_map}:&nbsp;{$in_create_map}</p>
  <p class="pagetext">{$pr_push_map}:&nbsp;{$in_push_map}</p>
  <p class="pagetext">{$pr_verify_code}:</p>
  <p class="pageinput">{$in_verify_code}<br />{$help_verify}</p>
  <p class="pagetext">{$pr_create_bots}:&nbsp;{$in_create_bots}</p>
  <br />
  <p class="pageinput">{$submit2}&nbsp;{$cancel}</p>
 </div>
{$end_set}
{if isset($regenerate)}
 {$start_regen_set}
  <div class="pageoverflow">
   <p class="pageinput" style="margin-top:0;">{$regenerate}<br />{$help_regenerate}</p>
   <br />
  {$sitemap_help}
  </div>
 {$end_set}
{/if}
{$end_form}
{$end_tab}

{$tab_footers}
