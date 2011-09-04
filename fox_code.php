<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'fox_code';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2.1';
$plugin['author'] = 'Riccardo Traverso';
$plugin['author_uri'] = 'http://www.riccardotraverso.it';
$plugin['description'] = 'A bridge to the powerful GeSHi syntax highlighter with many added features.';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the admin side
$plugin['type'] = '0';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '0';

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/*
  fox_code v0.2.1
  fox_code is a TextPattern plugin written by Riccardo Traverso (GreyFox) and it's released under
  the terms of the GPL 2.0 license
*/

function fox_code($atts, $thing) {
    global $file_base_path, $thisfile, $fox_code_prefs;
    
    if (isset($fox_code_prefs)) {
        foreach (Array('fileid','filename','language','lines','startline','overallclass','tabs','css','keywordslinks','encoding', 'fromline', 'toline') as $value) {
            $$value = $fox_code_prefs[$value];
        }
    } else {
        extract(lAtts(array(
            'fileid'               =>  '',
            'filename'             =>  '',
            'language'             =>  'php',
            'lines'                =>  '1',
            'startline'            =>  '1',
            'overallclass'         =>  '', //defaults to $language
            'tabs'                 =>  '2',
            'css'                  =>  '0',
            'keywordslinks'        =>  '0',
            'encoding'             => 'UTF-8',
            'fromline'             => '',
            'toline'               => ''
            ), $atts));
    }

    if (!$thisfile) {
        if ($fileid) {
            $thisfile = fileDownloadFetchInfo('id = '.intval($fileid));
        } else if ($filename) {
            $thisfile = fileDownloadFetchInfo("filename = '".$filename."'");
        } else $local_notfile=false;
    }

    if (!empty($thisfile)) {
        $filename = $thisfile['filename'];
        $fileid = $thisfile['id'];
        if (!empty($fromline) || !empty($toline)) {
            $handle=fopen($file_base_path.'/'.$filename, "r");
            $fromline = (!empty($fromline)) ? intval($fromline) : intval(1);
            $toline = (!empty($toline)) ? intval($toline) : intval(-1);
            $currentLine=0;
            $code="";
            while (!feof($handle)) {
                $currentLine++;
                if ($currentLine>=$fromline && ( $toline<0 || $currentLine<=$toline))
                    $code .= fgets($handle);
                else
                    fgets($handle);
            }
            fclose($handle);
            $startline = $fromline;
        } else
            $code = file_get_contents($file_base_path.'/'.$filename);
    } else {
        if (strlen($fox_code_prefs['code'])>0) $code = $fox_code_prefs['code'];
        else $code = $thing;
    }

    if (!$overallclass) $overallclass = $language;

    require_once('geshi.php');
    $geshi = new GeSHi(trim($code, "\r\n"), $language);
    if ((bool)$css) {
        $geshi->enable_classes();
        $geshi->set_overall_class($overallclass);
    }
    $geshi->start_line_numbers_at($startline);
    $geshi->set_header_type(GESHI_HEADER_DIV);
    $geshi->set_encoding($encoding);
    $geshi->set_tab_width(intval($tabs));
    $geshi->enable_keyword_links((bool)$keywordslinks);
    $geshi->enable_line_numbers((bool)$lines);

    if (!isset($local_notfile)) {
        $thisfile=NULL;
    }
    return $geshi->parse_code();
}

function fox_code_form($atts, $thing) {
    global $fox_code_prefs, $thisfile;
    extract(lAtts(array(
        'fileid'               =>  '',
        'filename'             =>  '',
        'title'                =>  '', //defauts to $filename or to $language
        'height'               =>  '400',
        'language'             =>  'php',
        'lines'                =>  '1',
        'startline'            =>  '1',
        'width'                =>  '500',
        'overallclass'         =>  '', //defaults to $language
        'tabs'                 =>  '2',
        'css'                  =>  '0',
        'keywordslinks'        =>  '0',
        'encoding'             =>  'UTF-8',
        'form'                 =>  'fox_code_form',
        'fromline'             =>  '',
        'toline'               =>  ''
        ), $atts));
    
    if ($fileid || $filename || $thing) {
        
        if (empty($thisfile)) {
            if ($fileid) {
                $thisfile = fileDownloadFetchInfo('id = '.intval($fileid));$local_thisfile=true;
            } else if ($filename) {
                $thisfile = fileDownloadFetchInfo("filename = '".$filename."'");$local_thisfile=true;
            }
        }
        
        $fox_code_prefs['fileid']        = $fileid       ;
        $fox_code_prefs['filename']      = $filename     ;
        $fox_code_prefs['title']         = $title        ;
        $fox_code_prefs['height']        = $height       ;
        $fox_code_prefs['language']      = $language     ;
        $fox_code_prefs['lines']         = $lines        ;
        $fox_code_prefs['startline']     = $startline    ;
        $fox_code_prefs['width']         = $width        ;
        $fox_code_prefs['overallclass']  = $overallclass ;
        $fox_code_prefs['tabs']          = $tabs         ;
        $fox_code_prefs['css']           = $css          ;
        $fox_code_prefs['keywordslinks'] = $keywordslinks;
        $fox_code_prefs['encoding']      = $encoding     ;
        $fox_code_prefs['fromline']      = $fromline     ;
        $fox_code_prefs['toline']        = $toline       ;

        if ( strlen($fileid)==0 && strlen($filename)==0 ) $fox_code_prefs['code'] = $thing;
        if (!empty($thisfile)) {
            $fox_code_prefs['filename'] = $thisfile['filename'];
            $fox_code_prefs['fileid'] = $thisfile['id'];
        }
        if (!$title) $fox_code_prefs['title'] = $thisfile['filename']!='' ? $thisfile['filename'] : $language;

        $form = fetch_form($form);
        $out = parse($form);
        $thisfile=$fox_code_prefs=NULL;
        return $out;
    }
}

function fox_code_form_attr($atts) {
    global $fox_code_prefs;
    if (isset($fox_code_prefs)) {
        extract(lAtts(array(
            'attr'            =>  'title'
            ),$atts));
        return isset($fox_code_prefs[$attr]) ? $fox_code_prefs[$attr] : '';
    }
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>Plugin description</h1>
<p>This plugin brings you some useful tags that will allow you to customize your code sections just like you want.</p>
<p>Here is the tag list:</p>
<ul>
<li><strong>fox_code</strong></li>
<li><strong>fox_code_form</strong></li>
<li><strong>fox_code_form_attr</strong></li>
<ul>

<h1>&lt;fox_code /&gt;</h1>

<h2>description</h2>
<p>The <em>fox_code</em> tag allows you to use the powerful GeSHi syntax highlighter into TXP. This is the easiest tag, because it simply paste the highlighted code into you page, without anything else. However you can customize the output quite well because of the large amount of arguments.</p>
<p>You can call this tag without any argument into a form template: in this case the tag will behave just like you specified calling the <em>fox_code_form</em> tag (see below).</p>
<h2>usage</h2>
<p>Here are some examples:</p>
<ul>
<li>&lt;txp:fox_code language=&quot;c&quot; fileid=&quot;20&quot; /&gt;</li>
<li>&lt;txp:fox_code language=&quot;cpp&quot; filename=&quot;main.cpp&quot; fromline=&quot;10&quot; toline=&quot;35&quot; /&gt;</li>
<li>&lt;txp:fox_code language=&quot;php&quot; filename=&quot;mysource.php&quot; /&gt;</li>
<li>&lt;txp:fox_code language=&quot;css&quot; &gt;your css code here&lt;/txp:fox_code&gt;</li>
</ul>
<h2>arguments</h2>
<p>Note that they will be ignored if you call the tag into a form used by the <em>fox_code_form</em> tag.</p>
<ul>
<li><strong>fileid</strong> - optional, the id of the file to load</li>
<li><strong>filename</strong> - optional, the name of the file to load</li>
<li><strong>language</strong> - optional, specify the syntax to highlight (defaut: "php")</li>
<li><strong>lines</strong> - otpional, turns line numbers on and off (default: "1")</li>
<li><strong>startline</strong> - optional, if line numbers are shown, the first line number (default: "1")</li>
<li><strong>overallclass</strong> - optional, if you are using CSS this is the main div class (see GeSHi documentation about css usage; the default value is the language name)</li>
<li><strong>tabs</strong> - optional, it's the width in spaces that you'd like tabs to be (default: "2")</li>
<li><strong>css</strong> - optional, turns css syntax highlighting on and off (default: "0")</li>
<li><strong>keywordslinks</strong> - optional, disable and enable all GeSHi URL linking for keywords capabilities (default="0")</li>
<li><strong>encoding</strong> - optional, charset encoding for GeSHi (default: "UTF-8")</li>
<li><strong>fromline</strong> - optional, reads the file from this line (if omitted reads from the beginning)</li>
<li><strong>toline</strong> - optional, reads the file until this line (if omitted reads until the end)</li>
</ul>

<h1>&lt;fox_code_form /&gt;</h1>
<h2>description</h2>
<p><em>fox_code_form</em> allows to display highlighted code into a chosen form template. Like the previous tag, you can both write the code into the tag or specify to load it form some file.</p>
<p>If you specify a file (by name or by id) you'll be able to use all of the downlod form textpattern standard tags in order to add a download link to the highlighted code.</p>
<h2>usage</h2>
<p>Here are some examples:</p>
<ul>
<li>&lt;txp:fox_code_form form="code" language=&quot;c&quot; fileid=&quot;20&quot; /&gt;</li>
<li>&lt;txp:fox_code_form form="code1" language=&quot;php&quot; filename=&quot;mysource.php&quot; /&gt;</li>
<li>&lt;txp:fox_code_form language=&quot;css&quot; &gt;your css code here&lt;/txp:fox_code&gt;</li>
</ul>
<h2>arguments</h2>
<ul>
<li><strong>title</strong> - optional (default value is the file name or the language)</li>
<li><strong>height</strong> - optional</li>
<li><strong>width</strong> - optional</li>
<li><strong>form</strong> - optional, the form to process (default: "fox_code_form")</li>
</ul>
<p>You can also use all of the <em>fox_code</em> arguments.</p>
<h1>&lt;fox_code_form_attr /&gt;</h1>
<h2>description</h2>
<p>This tag is intended to be used in conjunction with <em>fox_code_form</em>. Using this tag you'll be able (inside a form) to read all of the parameters (defaults and not) specified by the <em>fox_code_form</em> tag just like all of the form related tags.</p>
<h2>usage</h2>
<pre>&lt;div class=&quot;fox_code_form&quot; style=&quot;height:&lt;txp:fox_code_form_attr attr=&apos;height&apos; /&gt;px;width:&lt;txp:fox_code_form_attr attr=&apos;width&apos; /&gt;px;&quot;&gt;
&lt;p class=&quot;fox_code_form_title&quot;&gt;
  &lt;txp:fox_code_form_attr attr=&quot;title&quot; /&gt;
    &lt;txp:file_download_link&gt;[Download #&lt;txp:file_download_downloads /&gt;,
    &lt;txp:file_download_size decimals=&quot;2&quot; /&gt;]&lt;/txp:file_download_link&gt;
&lt;/p&gt;
&lt;div class=&quot;fox_code_form_codeblock&quot;&gt;&lt;txp:fox_code /&gt;&lt;/div&gt;
&lt;/div&gt;</pre>
<p>Of course you can change this form template as you wish.</p>
<h2>arguments</h2>
<ul>
<li><strong>attr</strong> - one of the <em>fox_code_form</em> attributes (default: "title")</li>
</ul>
# --- END PLUGIN HELP ---
-->
<?php
}
?>
