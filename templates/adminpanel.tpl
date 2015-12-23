{if !empty($message)}{$message}{/if}
{$tabs_header}

{$start_alerts_tab}
<div class="pageoverflow">
{if $pset}
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
{/if}{*pset*}
{if !empty($resource_links)}
{$start_resources_set}
<ul>
{foreach from=$resource_links item=one}<li>{$one}</li>{/foreach}
</ul>
{$end_set}
{/if}
</div>
{$end_tab}

{if $pset}

{$start_urgent_tab}
{if !empty($urgents)}
{$startform_problems}
<div class="pageinput pageoverflow">
<table class="pagetable">
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
</div>
<br />
<div class="pageinput">{$unignore1}&nbsp;{$ignore1}</div>
{$end_form}
{/if}
{$end_tab}

{$start_important_tab}
{if !empty($importants)}
{$startform_problems}
<div class="pageinput pageoverflow">
<table class="pagetable">
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
</div>
<br />
<div class="pageinput">{$unignore2}&nbsp;{$ignore2}</div>
{$end_form}
{/if}
{$end_tab}

{/if}{*pset*}

{$start_description_tab}
{$startform_pages}
<div class="pageinput pageoverflow">
<table class="pagetable">
 <tr>
  <th>{$title_name}</th>
{if $pset}
  <th>{$title_priority}</th>
  <th>{$title_ogtype}</th>
  <th>{$title_keywords}</th>
{/if}
  <th>{$title_desc}</th>
{if $pset}
  <th>{$title_index}</th>
  <th class="checkbox seocb"><input id="allindx" type="checkbox" onclick="select_all('indx');" /></th>
{/if}
 </tr>
{if isset($items)}
 {foreach from=$items item=entry}
  <tr class="{$entry->rowclass}" onmouseover="this.className='{$entry->rowclass}hover';" onmouseout="this.className='{$entry->rowclass}';">
   <td>{$entry->name}</td>
{if $pset}
   <td>{$entry->priority}</td>
   <td>{$entry->ogtype}</td>
   <td>{$entry->keywords}</td>
{/if}
   <td>{$entry->desc}</td>
{if $pset}
   <td>{$entry->index}</td>
{if $entry->index}
   <td><input type="checkbox" name="indxsel[]"{if ($entry->sel)} checked="checked"{/if} value="{$entry->checkval}" /></td>
{else}
   <td></td>
{/if}
{/if}{*pset*}
  </tr>
 {/foreach}
{/if}
</table>
</div>
{if $pset && isset($items)}
<br />
<div class="pageinput">{$unindex}&nbsp;{$index}</div>
{/if}{*pset*}
{$end_form}
{$end_tab}

{if $pset}

{$start_meta_tab}
{$startform_settings}
{foreach from=$metaset item=row}
{if $row[0]}{$row[0]}{/if}{*start set*}
<div class="pageoverflow" style="margin-top:0;">
 {foreach from=$row[1] item=entry}
  {if !empty($entry->head)}<h4 class="pagetext" style="margin-top:1.5em">{$entry->head}</h4>{/if}
  {if empty($entry->inline)}
<p class="pagetext">{$entry->title}:</p>
<p class="pageinput{if !empty($entry->help)} slidetip{/if}">{$entry->input}</p>
  {else}
<p class="pagetext{if !empty($entry->help)} slidetip{/if}">{$entry->title}:&nbsp;&nbsp;{$entry->input}</p>
  {/if}
{if !empty($entry->help)}<div class="slidediv">
<p class="pageinput">{$entry->help}</p>
</div>{/if}
 {/foreach}
</div>
{if $row[0]}{$end_set}{/if}
{/foreach}
<br />
<p class="pageinput">{$submit1}&nbsp;{$cancel}&nbsp;{$display1}</p>
{$end_form}
{$end_tab}

{$start_keyword_tab}
<strong>{$keyword_help}</strong>
<br />
{$startform_settings}
{foreach from=$keywordset item=row}
{$row[0]}{*start set*}
<div class="pageoverflow" style="margin-top:0;">
 {foreach from=$row[1] item=entry}
{if empty($entry->inline)}
<p class="pagetext">{$entry->title}:</p>
<p class="pageinput{if !empty($entry->help)} slidetip{/if}">{$entry->input}</p>
{else}
<p class="pagetext{if !empty($entry->help)} slidetip{/if}">{$entry->title}:&nbsp;&nbsp;{$entry->input}</p>
{/if}
{if !empty($entry->help)}<div class="slidediv">
<p class="pageinput">{$entry->help}</p>
</div>{/if}
 {/foreach}
</div>
{$end_set}
{/foreach}
<br />
<p class="pageinput">{$submit2}&nbsp;{$cancel}</p>
{$end_form}
{$end_tab}

{$start_sitemap_tab}
{$startform_settings}
{foreach from=$sitemapset item=row name=bundle}
{$row[0]}{*start set*}
<div class="pageoverflow" style="margin-top:0;">
 {foreach from=$row[1] item=entry}
{if empty($entry->inline)}
<p class="pagetext">{$entry->title}:</p>
<p class="pageinput{if !empty($entry->help)} slidetip{/if}">{$entry->input}</p>
{else}
<p class="pagetext{if !empty($entry->help)} slidetip{/if}">{$entry->title}:&nbsp;&nbsp;{$entry->input}</p>
{/if}
{if !empty($entry->help)}<div class="slidediv">
<p class="pageinput">{$entry->help}</p>
</div>{/if}
 {/foreach}
{if $smarty.foreach.bundle.last}
<br />
<p class="pageinput">{$submit3}&nbsp;{$cancel}&nbsp;{$display}</p>
{/if}
</div>
{$end_set}
{/foreach}
{if isset($regenerate)}
 {$start_regen_set}
  <div class="pageoverflow" style="margin-top:0;">
   <p class="pageinput">{$regenerate}<br /><br />{$help_regenerate}</p>
   <br />
  {$sitemap_help}
  </div>
 {$end_set}
{/if}
{$end_form}
{$end_tab}

{/if}{*pset*}

{$tabs_footer}
