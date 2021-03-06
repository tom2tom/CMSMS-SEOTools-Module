{if !empty($message)}{$message}{/if}
{$tabs_header}

{$start_alerts_tab}
<div class="pageinput pageoverflow">
{if $pset}
<fieldset class="settings">
<legend>{$urgent_set}</legend>
<p>{$urgent_icon}&nbsp;{$urgent_text}{if isset($urgent_link)}&nbsp;{$urgent_link}{/if}</p>
</fieldset >
<fieldset class="settings">
<legend>{$important_set}</legend>
<p>{$important_icon}&nbsp;{$important_text}{if isset($important_link)}&nbsp;{$important_link}{/if}</p>
</fieldset>
{if isset($notices)}
 <fieldset class="settings">
 <legend>{$notice_set}</legend>
 {foreach from=$notices item=entry}
 <p>{$entry->icon}&nbsp;{$entry->text}{if isset($entry->link)}&nbsp;{$entry->link}{/if}</p>
 {/foreach}
 </fieldset>
{/if}
{/if}{*pset*}
{if !empty($resource_links)}
<fieldset class="settings">
<legend>{$resources_set}</legend>
<ul>
{foreach from=$resource_links item=one}<li>{$one}</li>{/foreach}
</ul>
</fieldset>
{/if}
</div>
{$end_tab}

{if $pset}

{$start_urgent_tab}
{if !empty($urgents)}
{$startform_problems}
<div class="pageinput pageoverflow" style="display:inline-block;">
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
<br />
<div style="float:right;">{$unignore1}&nbsp;{$ignore1}</div><div style="clear:both;"></div>
</div>
{$end_form}
{/if}
{$end_tab}

{$start_important_tab}
{if !empty($importants)}
{$startform_problems}
<div style="display:inline-block;" class="pageinput pageoverflow">
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
<br />
<div style="float:right;">{$unignore2}&nbsp;{$ignore2}</div><div style="clear:both;"></div>
</div>
{$end_form}
{/if}
{$end_tab}

{/if}{*pset*}

{$start_description_tab}
{$startform_pages}
<div style="display:inline-block;" class="pageinput pageoverflow">
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
{if $pset && isset($items)}
<br />
<div style="float:right;">{$unindex}&nbsp;{$index}</div><div style="clear:both;"></div>
{/if}{*pset*}
</div>
{$end_form}
{$end_tab}

{if $pset}

{$start_meta_tab}
{$startform_settings}
<div class="pageinput pageoverflow" style="display:inline-block;">
{foreach from=$metaset item=row}
{if $row[0]}<fieldset class="settings">
<legend>{$row[0]}</legend>{/if}
 {foreach from=$row[1] item=entry}
  {if !empty($entry->head)}<h4 class="pagetext leftward" style="margin-top:1.5em">{$entry->head}</h4>{/if}
  {if empty($entry->inline)}
<p class="pagetext leftward">{$entry->title}:</p>
<p{if !empty($entry->help)} class="slidetip"{/if}>{$entry->input}</p>
  {else}
<p class="pagetext leftward{if !empty($entry->help)} slidetip{/if}">{$entry->title}:&nbsp;&nbsp;{$entry->input}</p>
  {/if}
{if !empty($entry->help)}<div class="slidediv">
<p>{$entry->help}</p>
</div>{/if}
 {/foreach}
{if $row[0]}</fieldset>{/if}
{/foreach}
<br />
<div style="float:right;">{$submit1}&nbsp;{$cancel}</div><div style="clear:both;"></div>
</div>
{$end_form}
{$end_tab}

{$start_keyword_tab}
<p style="font-weight:bold;max-width:50em;">{$keyword_help}</p>
{$startform_settings}
<div class="pageoverflow" style="margin-top:0;display:inline-block;">
{foreach from=$keywordset item=row}
<fieldset class="settings">
<legend>{$row[0]}</legend>
 {foreach from=$row[1] item=entry}
{if empty($entry->inline)}
<p class="pagetext leftward">{$entry->title}:</p>
<p{if !empty($entry->help)}class="slidetip"{/if}>{$entry->input}</p>
{else}
<p class="pagetext leftward{if !empty($entry->help)} slidetip{/if}">{$entry->title}:&nbsp;&nbsp;{$entry->input}</p>
{/if}
{if !empty($entry->help)}<div class="slidediv">
<p class="pageinput">{$entry->help}</p>
</div>{/if}
 {/foreach}
</fieldset>
{/foreach}
<br />
<div style="float:right;">{$submit2}&nbsp;{$cancel}</div><div style="clear:both;"></div>
</div>
{$end_form}
{$end_tab}

{$start_sitemap_tab}
{$startform_settings}
<div class="pageoverflow" style="margin-top:0;display:inline-block;">
{foreach from=$sitemapset item=row name=bundle}
<fieldset class="settings">
<legend>{$row[0]}</legend>
 {foreach from=$row[1] item=entry}
{if empty($entry->inline)}
<p class="pagetext leftward">{$entry->title}:</p>
<p{if !empty($entry->help)}class="slidetip"{/if}>{$entry->input}</p>
{else}
<p class="pagetext leftward{if !empty($entry->help)} slidetip{/if}">{$entry->title}:&nbsp;&nbsp;{$entry->input}</p>
{/if}
{if !empty($entry->help)}<div class="slidediv">
<p>{$entry->help}</p>
</div>{/if}
 {/foreach}
{if $smarty.foreach.bundle.last}
<br />
<div style="float:right;">{$submit3}&nbsp;{$cancel}&nbsp;{$display}</div><div style="clear:both;"></div>
{/if}
</fieldset>
{/foreach}
{if isset($regenerate)}
 <fieldset class="settings">
 <legend>{$regen_set}</legend>
  <p class="pageinput">{$help_regenerate}<br /><br />{$regenerate}</p>
  <br />
  <p style="max-width:50em;">{$sitemap_help}</p>
 </fieldset>
{/if}
</div>
{$end_form}
{$end_tab}

{/if}{*pset*}

{$tabs_footer}
