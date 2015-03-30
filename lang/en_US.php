<?php
$lang['friendlyname'] = 'SEO Tools';
$lang['postinstall'] = 'SEO Tools module has have been installed. Be sure to read the module help to learn how to use them.';
$lang['postuninstall'] = 'SEO Tools module has been uninstalled. Please be aware that you have lost all meta information for the site\'s pages!';
$lang['really_uninstall'] = 'Do you really want to uninstall SEO Tools?';
$lang['uninstalled'] = 'Module Uninstalled.';
$lang['installed'] = 'Module version %s installed.';
$lang['upgraded'] = 'Module upgraded to version %s.';
$lang['moddescription'] = 'Several tools to help with Search Engine Optimization and check for suboptimal SEO-related things.';
$lang['perm_editsettings'] = 'Edit SEO Settings';
$lang['perm_editdescription'] = 'Edit Page Descriptions';

$lang['error'] = 'Error!';
$land['admin_title'] = 'SEO Tools Admin Panel';
$lang['admindescription'] = 'Helps with SEO in general, automatically generating XML sitemaps and comprehensive meta descriptions';
$lang['accessdenied'] = 'Access Denied. Please check your permissions.';

$lang['title_alerts'] = 'Alerts';
$lang['title_descriptions'] = 'Page settings';
$lang['title_urgent'] = 'Urgent fixes';
$lang['title_important'] = 'Important fixes';
$lang['title_pages'] = 'Page(s)  ';
$lang['title_active'] = 'Active';
$lang['title_problem'] = 'Problem';
$lang['title_ignored'] = 'Ignored';
$lang['title_action'] = 'Action';

$lang['title_meta_type'] = 'Meta Tag Generator';
$lang['meta_create_standard'] = 'Generate standard Meta Tags';
$lang['meta_create_dublincore'] = 'Generate Dublin Core Meta Tags';
$lang['meta_create_opengraph'] = 'Generate OpenGraph Meta Tags (e.g. for Facebook Like Button)';

$lang['title_meta_defaults'] = 'META Tag Values';
$lang['meta_publisher'] = 'Site publisher';
$lang['meta_publisher_help'] = 'This is usually the organization whose website this is';
$lang['meta_contributor'] = 'Site contributor';
$lang['meta_contributor_help'] = 'Usually, this would be you or any other content author';
$lang['meta_copyright'] = 'Site copyright';
$lang['meta_copyright_help'] = 'Something like "(C) This Company. All rights reserved."';
$lang['meta_location_description'] = 'If this website is for an entity that can be located on a map, optionally fill in the following values';
$lang['meta_location'] = 'Location of site entity';
$lang['meta_location_help'] = 'Usually, this would be a town or city or suburb';
$lang['meta_region'] = 'Region of site entity';
$lang['meta_region_help'] = 'Enter something like "US-IL" for an entity located in Illinois, US';
$lang['meta_latitude'] = 'Latitude of site entity';
$lang['meta_latitude_help'] = 'Enter the decimal latitude geo coodinate of the entity';
$lang['meta_longitude'] = 'Longitude of site entity';
$lang['meta_longitude_help'] = 'Enter the decimal longitude geo coodinate of the entity';
$lang['meta_opengraph_description'] = 'If you are creating OpenGraph META tags, please fill in the following values';
$lang['meta_opengraph_title'] = 'OpenGraph page title';
$lang['meta_opengraph_title_help'] = 'You can use the tag {title} and/or any smarty tag(s) here, e.g. to replace with the actual page title';
$lang['meta_opengraph_type'] = 'OpenGraph default page type';
$lang['meta_opengraph_type_help'] = 'The default page type for OpenGraph, can be overridden for each page. Look <a href="http://developers.facebook.com/docs/opengraph#types" onclick="window.open(this.href,\'_blank\');return false;">here</a> for a list of allowed values';
$lang['meta_opengraph_sitename'] = 'OpenGraph site name';
$lang['meta_opengraph_sitename_help'] = 'A short version of this site\'s name, e.g. "Your Company", max. 25 chars';
$lang['meta_opengraph_image'] = 'OpenGraph default image';
$lang['meta_opengraph_image_help'] = 'Select an image from the site\'s /uploads/images directory to be used as the default for OpenGraph pages';
$lang['meta_opengraph_admins'] = 'Facebook site administrators';
$lang['meta_opengraph_admins_help'] = 'A comma-separated list of facebook account IDs who are able to administer the streams of your pages';
$lang['meta_opengraph_application'] = 'Facebook application';
$lang['meta_opengraph_application_help'] = 'The ID of a facebook application able to administer the streams of your pages';

$lang['title_type'] = 'Pages content type';
$lang['help_content_type'] = 'A recognised format like: html, html5, xhtml. Applied to all frontend pages';
$lang['title_title_description'] = 'Page Title and Description';
$lang['title_title'] = 'Page title';
$lang['title_title_help'] = 'The page title to be displayed in the browser\'s title bar. You can use the tags {title}, {seo_keywords} and/or any smarty tag(s) and UDT(s) here';
$lang['title_meta_title'] = 'Page Meta title';
$lang['title_meta_help'] = 'The page title to be used in the Meta title tags. You can use the tags {title}, {seo_keywords} and/or any smarty tag(s) and UDT(s) here';
$lang['title_description_block'] = 'Name of page description content-block';
$lang['description_block_help'] = 'The name of each page\'s description content-block. Please read the module help for an explanation';

$lang['title_sitemap_description'] = 'Google sitemap and crawler settings';
$lang['create_sitemap_title'] = 'Create an XML sitemap for Google*';
$lang['push_sitemap_title'] = 'Automatically push each newly-created sitemap to Google';
$lang['create_robots_title'] = 'Create a robots.txt file*';
$lang['verification_title'] = 'Google site-verification code';
$lang['verification_help'] = 'The code you obtain from Google Webmaster Tools, just the hash, not the complete meta tag';

$lang['title_alerts_urgent'] = 'To be fixed immediately';
$lang['title_alerts_important'] = 'To be fixed as soon as possible';
$lang['title_alerts_notices'] = 'Things you should consider fixing';
$lang['nothing_to_be_fixed'] = 'There are no actions you need to take in this category';
$lang['title_resources'] = 'Useful external SEO resources';

$lang['save'] = 'Save';
$lang['cancel'] = 'Cancel';
$lang['none'] = 'none';

$lang['ignore'] = 'Ignore';
$lang['help_ignore'] = 'Ignore selected items';
$lang['unignore'] = 'UnIgnore';
$lang['help_unignore'] = 'Do not ignore selected items';

$lang['settings_updated'] = 'The SEO settings were successfully updated.';
$lang['problem_alert'] = 'has detected severe problems with this site\'s SEO. %s';
$lang['problem_link_title'] = 'Click here to fix them';

$lang['help_sitemap_robots'] = '*) To generate a sitemap- and/or robots-file, the root directory of this CMSMS installation must be writeable by the web server. If you are refreshing an existing sitemap.xml and/or robots.txt file, those file(s) must be writeable by the web server.';

$lang['use_standard_or_dublincore_meta'] = 'Use at least one of the standard or Dublin Core Meta Tag generators';
$lang['use_standard_meta'] = 'You should always enable the standard Meta generator';
//$lang['meta_description_missing'] = 'The page <i>%s</i> is lacking a Meta description';
//$lang['meta_description_short'] = 'The Meta description of <i>%s</i> is rather short (< 75 chars)';
//$lang['duplicate_titles'] = 'The pages <i>%s</i> and <i>%s</i> have the same title';
//$lang['duplicate_descriptions'] = 'The pages <i>%s</i> and <i>%s</i> have the same Meta description';
$lang['meta_description_missing'] = 'Missing Meta description';
$lang['meta_description_short'] = 'Short (< 75 chars) Meta description';
$lang['duplicate_titles'] = 'Pages have the same title';
$lang['duplicate_descriptions'] = 'Pages have the same Meta description';
$lang['provide_an_author'] = 'The name of the page publisher is not defined';
$lang['set_up_description_block'] = 'No content block has been defined for Meta descriptions';
$lang['activate_pretty_urls'] = 'Pretty-URLs are not enabled';
$lang['get_help'] = 'Get help';
$lang['create_a_sitemap'] = 'Have a Google XML Sitemap automatically created for you';
$lang['automatically_upload_sitemap'] = 'Have the XML sitemap automatically uploaded to Google';
$lang['create_robots'] = 'Have a robots.txt file automatically created for you';
$lang['sitemap_not_writeable'] = 'The site\'s /sitemap.xml file is not writeable by the web server';
$lang['chmod_sitemap'] = 'Change permissions';
$lang['robots_not_writeable'] = 'The site\'s robots.txt file is not writeable by the web server';
$lang['chmod_robots'] = 'Change permissions';
$lang['no_opengraph_admins'] = 'You have not set an OpenGraph admin application resp. admin list';
$lang['no_opengraph_type'] = 'You have not set the default OpenGraph page type';
$lang['no_opengraph_sitename'] = 'You have not set the OpenGraph site name';
$lang['no_opengraph_image'] = 'You have not set an OpenGraph default image';
$lang['edit_page'] = 'Edit \'%s\'';
$lang['edit_page2'] = 'Edit this page';
$lang['visit_settings'] = 'Change settings';

$lang['page_id'] = 'ID';
$lang['page_name'] = 'Page name';
$lang['priority'] = 'Sitemap Priority';
$lang['og_type'] = 'OpenGraph type';
$lang['keywords'] = 'Keywords #';
$lang['description'] = 'Description';
$lang['title_index'] = 'Indexable';
$lang['default'] = 'default';
$lang['auto'] = 'auto';
$lang['index'] = 'Index';
$lang['unindex'] = 'UnIndex';
$lang['help_index'] = 'Make selected items indexable';
$lang['help_unindex'] = 'Exclude selected items from indexing';

$lang['click_to_add_description'] = 'Click here to add a page description';
$lang['toggle'] = 'toggle';
$lang['reset'] = 'reset';
$lang['reset_to_default'] = 'Reset this to the default value';
$lang['edit_value'] = 'Edit this value';

$lang['help_opengraph'] = 'Look <a href="http://developers.facebook.com/docs/opengraph#types" onclick="window.open(this.href,\'_blank\');return false;">here</a> for a list of allowed values';
$lang['enter_new_ogtype'] = 'OpenGraph type for page \'%s\'';
$lang['help_new_ogtype'] = 'Leave blank to revert to the default setting';

$lang['enter_new_keywords'] = 'Keywords for page \'%s\'';
$lang['help_new_keywords'] = 'Series of word(s) and/or group(s) of words. Use the defined separator. Leave blank to revert to auto generated keywords';

$lang['summary_urgent'] = 'We have detected %d urgent problem(s)';
$lang['summary_important'] = 'We have detected %d important problem(s)';
//$lang['grouptitle_opengraph'] = 'We have detected %s OpenGraph related problem(s)';
//$lang['grouptitle_system'] = 'We have detected %s problem(s) with your system configuration';
//$lang['grouptitle_pages'] = 'We have detected %s problem(s) on your content pages';
//$lang['grouptitle_settings'] = 'We have detected %s problem(s) with your SEO Settings';
//$lang['grouptitle_descriptions'] = 'We have detected %s problem(s) with your page descriptions';
//$lang['grouptitle_titles'] = 'We have detected %s problem(s) with your page titles';

$lang['view_all'] = 'View all';

$lang['title_metasettings'] = 'Page title & Meta information';
$lang['title_sitemapsettings'] = 'Sitemap & Crawler settings';
$lang['title_keywordsettings'] = 'Keyword settings';

$lang['description_auto_generate'] = 'Automatically generate a page description where none is provided';
$lang['description_auto_title'] = 'Text for auto-generated descriptions';
$lang['description_auto_help'] = 'You <b>must</b> include the tag {keywords} here';

$lang['set_up_auto_description'] = 'Set up the description auto-generator and ensure the text contains the tag {keywords}';
$lang['auto_generated'] = 'Automatically generated';
$lang['and'] = 'and';

$lang['title_keyword_block'] = 'Name of keywords content-block';
$lang['keyword_block_help'] = 'The name of each page\'s keywords content-block. Please read the module help for an explanation';
$lang['title_keyword_weight'] = 'Keyword generator';
$lang['keyword_separator_title'] = 'Separator';
$lang['keyword_separator_help'] = 'Character separating words or groups of words (usually space or comma), same as the separator used in each page\'s "description content block"';
$lang['keyword_minlength_title'] = 'Minimum keyword length';
$lang['keyword_minlength_help'] = 'The minimum length of a word to be considered as a keyword';
$lang['keyword_title_weight_title'] = 'Weight of words in the page title';
$lang['keyword_title_weight_help'] = 'The weight of words in the page title. The higher a word\'s weight, the more likely it is to become a keyword';
$lang['keyword_description_weight_title'] = 'Weight of words in the page description';
$lang['keyword_description_weight_help'] = 'The weight of words in the page description. The higher a word\'s weight, the more likely it is to become a keyword';
$lang['keyword_headline_weight_title'] = 'Weight of words in content headlines';
$lang['keyword_headline_weight_help'] = 'The weight of words between &lt;h1&gt; to &lt;h6&gt; tags. The higher a word\'s weight, the more likely it is to become a keyword';
$lang['keyword_content_weight_title'] = 'Weight of words in plain content';
$lang['keyword_content_weight_help'] = 'The weight of words inside the plain content. The higher a word\'s weight, the more likely it is to become a keyword';

$lang['keyword_minimum_weight_title'] = 'Minimum total weight of a keyword';
$lang['keyword_minimum_weight_help'] = 'The minimum total weight of a word to become a keyword. Should be greater than the highest weight from above. The smaller the number, the more keywords you get';

$lang['help_keyword_generator'] = 'The settings displayed on this page supplement any keywords you enter manually. Feel free to play with all values until you get the best results for your page.';

$lang['title_keyword_exclude'] = 'Keyword lists';
$lang['default_keywords_title'] = 'Keywords to always include';
$lang['default_keywords_help'] = 'Word(s) (and/or group(s) of words, if the separator is not a space) you would like to include on every page';
$lang['keyword_exclude_title'] = 'Words to never consider as keywords';
$lang['keyword_exclude_help'] = 'Words (and/or group(s) of words, if the separator is not a space) that should never appear in keyword lists';

$lang['increase_priority'] = 'Increase priority by 10%';
$lang['decrease_priority'] = 'Decrease priority by 10%';

$lang['title_regenerate_both'] = 'Regenerate';
$lang['button_regenerate_sitemap'] = 'Regenerate sitemap';
$lang['button_regenerate_robot'] = 'Regenerate robots.txt';
$lang['button_regenerate_both'] = 'Regenerate sitemap and robots.txt';
$lang['text_regenerate_sitemap'] = 'It\'s best to regenerate sitemap.xml and robots.txt files after extensive changes to the page structure';
$lang['robot_regenerated'] = 'The robots.txt file has been successfully regenerated.';
$lang['sitemap_regenerated'] = 'The sitemap has been successfully regenerated.';
$lang['both_regenerated'] = 'The files sitemap.xml and robots.txt have been successfully regenerated.';
$lang['none_regenerated'] = 'Nothing was regenerated. There seems to be a problem.';

$lang['install_database_error'] = 'An error has occured during installation: The database table could not be created.';
$lang['no_url_fopen'] = 'Not possible as allow_url_fopen has not been set to &quot;on&quot; in your php configuration.';
$lang['title_additional_meta_tags'] = 'Additional Meta Tags';
$lang['additional_meta_tags_title'] = 'Additional Meta tags to be inserted';
$lang['additional_meta_tags_help'] = 'Specify additional Meta tags here to be inserted into the page header. You can use all smarty variables and UDTs here.';

$lang['help_showbase'] = 'Set this parameter to <code>false</code> to suppress the output of the base href tag.';

$lang['help'] = '<h3>What Does This Do?</h3>
<p>This helps you get your SEO (Search Engine Optimization) right. The module adds several SEO capabilities to your CMSMS installation and alerts you if you missed out on something SEO related. Currently, the following features are supported:</p>
<ul>
<li>generate meta tags in standard html, Dublin Core and OpenGraph formats</li>
<li>extract keywords from site pages and add the keywords to the meta tags and page title</li>
<li>alerts about multiple pages featuring the same page titles and descriptions</li>
<li>alerts about very short page meta descriptions</li>
<li>user-choice whether or not specified site-pages should be indexed by search engines</li>
<li>generate a sitemap.xml file for the site, with full control over the priorities of site pages</li>
<li>automatically submit the sitemap file to Google when site content is changed</li>
<li>generate a robots.txt file explicitly disallowing access to pages you don\'t want to be indexed</li>
</ul>
<h3>How Do I Use It</h3>
<p>First, open each template/page where you want to apply SEO Tools. For each of them:</p>
<ol>
<li>Remove the whole &lt;title&gt;&lt;/title&gt; line, and the {metadata} tag (even if it says to never remove it) and replace them with the tag
<pre>{SEOTools}</pre></li>
<li>If page-title metadata is to be updated each time the page is constructed, insert at the bottom (after the closing &lt;/body&gt; tag), insert code like the following:
<pre>{content assign=\'meta_description\' block=\'meta_description\' label=\'Your block label\' wysiwyg=\'false\'}</pre>
The assigned value may include variable(s).</li>
The content block name is your choice, and you\'ll need to record it as one of the module preferences. This block will be available to enter a short page description (one or two sentences).<br />
As an alternative to the content block, you can enable the SEO Tools option "Automatically generate descriptions where none is given". This is not as good, but better than nothing.<br />
With either of these approaches, the provided description will appear in the search engine\'s results and if, for example, someone shares a link to your page on facebook.</li>
</ol><br />
<p>Next, visit the module\'s settings tabs to record page title & meta information, sitemap & crawler settings and keyword settings for the site.<br />
Under "Name of page description content-block" you need to enter the name of the content-block you just added to your templates ("Page Description" in our example).<br />
You should also consider signing up your site with Google Webmaster tools. There, select "Meta tag validation" and paste the verification code (quite a long string of characters and numbers) into the box "Verification Code" in "SEO Settings" to automatically verify that you are really
the owner of your page (no need to paste the Google Meta-code into your template).<br />
To set your own keywords (instead of the automatically generated ones) or OpenGraph type for certain pages, visit the "Page settings" tab where you\'ll find a list of all pages you have in your system. There, you can also change the sitemap priority (which is auto-calculated by default) of a page to indicate that it should
be considered more important for search engine matches on your page or exclude certain pages from the search index.<br />
The module will also automatically insert a link to the image to be used as the main image of your page if you select an "Image" for the page from within your page\'s "Options" tab (this is important for people submitting a link to your page e.g. on facebook). Also, if you are using OpenGraph, the image selected here
will override the default OpenGraph image you specified from within "SEO Settings". Please be aware that you need to upload those images to your /uploads/images directory.</p>
<p>Last, visit the module\'s "Alerts" and "Fixes" tabs and deal with any problems detected. Take care to insert a (unique) page description on every page you edit in the field "Page Description" at the bottom of your "Edit Page" form.</p>
<h3>Can I use smarty variables?</h3>
<p>You can use all smarty variables and UDTs within the settings fields <i>Page title</i>, <i>Meta page title</i>, <i>Automatically generated descriptions</i> and <i>Additional meta tags</i>. Enter <code>{debug}</code> to see a list of all available variables.</p>
<h3>Which smarty variables are exported by this module?</h3>
<code>{$seo_keywords}</code>: A string comprised of all page-specific and default keywords or groups of keywords, using the module\'s default separator<br />
<code>{$default_keywords}</code>: As for {$seo_keywords}, without page-specific keywords<br />
<code>{$page_keywords}</code>: As for {$seo_keywords}, without default keywords<br />
<code>{$title_keywords}</code>: As for {$seo_keywords}, except the separator is always a space-character
<h3>Support</h3>
<p>This software is provided as-is. Please read the text of the license (see below) for the full disclaimer.</p>
<h3>Copyright and License</h3>
<p>Copyright &copy; 2010-2011, Henning Schaefer &lt;henning.schaefer@gmail.com&gt;.<br />
Copyright &copy; 2014-2015, Tom Phane &lt;tpgww@onepost.net&gt;.<br />
All rights reserved.</p>
<p>This module has been released under the <a href="http://www.gnu.org/licenses/licenses.html#AGPL">GNU Affero General Public License</a> version 3.
You must comply with that license when distributing or using the module.</p>';
?>
