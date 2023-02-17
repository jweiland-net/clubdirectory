..  include:: /Includes.rst.txt


========
Updating
========

If you update EXT:clubdirectory to a newer version, please read this section carefully!

Update to Version 5.1.0
=======================

TYPO3 log fills up with warnings about removed GET/POST params in PageBrowser. To solve this issue we have
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
all Templates of Field directory into FormFields Template directly in to f:section.
Please check your templates, if you have overwritten them.

We have remove form.objectContext VH with no replacement. Please have a look into our new
templates and adopt your templates accordingly.

In case of TYPO3 9 you should execute the Slug Updater Wizard of clubdirectory in Installtool, to fill
path_segments for better slugs.

As we have modified a lot of PHP classes you should clear all caches.
