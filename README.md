Textpattern plugin: fox_code
============================

Description
-----------

In order to have an easy way to use syntax a highlighter into TextPattern I
wrote this plugin, fox_code.
It brings some useful tags that will allow you to customize your code sections
just like you want, because of it's integration with textpattern's templates.

Here is the link to the plugin's page:
[information page](http://www.riccardotraverso.it/Software/51/fox_code)
It's written in italian, but the plugin's help page (the one that you can read
from textpattern I mean) it's in english.
This plugin also has
[his own page on textpattern.org](http://textpattern.org/plugins/810/fox_code).

[GeSHi](http://qbnz.com/highlighter) supports more than 130 languages,
including PHP, HTML, CSS, Java, C, Lisp, XML, Perl, Python, ASM and many more.


WARNING: Upgrade from 0.2
-------------------------

If you're upgrading fox_code from version 0.2 to 0.2.1 please be careful:
because of new mandatory naming conventions starting from Textpattern 4.4, two
tags changed name.
More precisely:

* `fox_codeForm` becomes `fox_code_form`
* `fox_codeFormAttr` becomes `fox_code_form_attr`

Be sure to update your articles and forms in order to use the new names.
You could consider to launch a couple of MySQL UPDATE queries with `REPLACE()`
on the tables `textpattern` and `txp_form`  instead of manually replace every
instance.
In this case, be sure to include both `Body` and `Body_html` fields for the
`textpattern` table and the `Form` field for `txp_form`.


Installation
------------

You have to install the GeSHi library before starting to use the plugin
([download page](http://sourceforge.net/project/showfiles.php?group_id=114997)).
Unpack it and move `geshi.php` and the `geshi` folder to `textpattern/lib`.
If you don't want to write your own form template, you can download the example
one from the information page.

History
-------

* 0.2.1 - compatibility with Textpattern 4.4 (2011-09-03)
* 0.2 - fromline and toline options added (2007-12-08)
* 0.1.1 - bugfix (2007-07-16)
* 0.1 - first release (2007-07-06)

