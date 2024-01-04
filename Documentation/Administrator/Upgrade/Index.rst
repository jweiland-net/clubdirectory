..  include:: /Includes.rst.txt


..  _upgrade:

=======
Upgrade
=======

If you upgrade `clubdirectory` to a newer version, please read this
section carefully!

Update to Version 7.0.0
=======================

This version supports TYPO3 version 12 LTS only. Lower version support removed.
All Test Cases which were based on Nimut Testing framework has been replaced
with TYPO3 Testing Framework. The backend module for CSV export has been fixed
for new backend module API service. All the deprecations are fixed in this
version so there are no logs thrown in extension scanner.

Update to Version 6.0.0
=======================

`clubdirectory` allows `maps2` in version 10.0.0. This version does not work
with fluid widgets anymore. That's why you also have to switch to the new
`maps2` partial implementation for editPoi in your overwritten `clubdirectory`
templates. If you still use 9.3.1 of `maps2` no template modifications are
needed.

If you have extended the controllers of `clubdirectory` please check, if your
implementation is still working, as we have drastically re-factored the
controllers.

Update to Version 5.1.0
=======================

TYPO3 log fills up with warnings about removed GET/POST params in PageBrowser.
To solve this issue we have
switched to new Paginate API of TYPO3. Please update following fluid files:

Templates/Club/List.html
Partials/Club/List.html

And update the lines about PageBrowser. Use our files as an example

Update to Version 5.0.7
=======================

We have changed some method arguments, please flush cache in InstallTool

Update to Version 3.0.0
=======================

We have changed Fluid-Templates a lot. We have removed a lot of CSS classes
and changed them to be compatible with Bootstrap. Further we have moved
all Templates of Field directory into FormFields Template directly in to
f:section. Please check your templates, if you have overwritten them.

We have remove form.objectContext VH with no replacement. Please have a look
into our new templates and adopt your templates accordingly.

In case of TYPO3 9 you should execute the Slug Updater Wizard of
clubdirectory in Installtool, to fill path_segments for better slugs.

As we have modified a lot of PHP classes you should clear all caches.
