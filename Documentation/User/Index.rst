..  include:: /Includes.rst.txt
..  _user-manual:

============
Users manual
============

Target group: **Editors**

#.  Select a page where you want to insert clubdirectory

#.  Insert a new content element of type `Club Directory` from the tab
    :guilabel:`Plugins` of the content element wizard.

#.  On tab :guilabel:`Plugin` configure the options of the plugin.

#.  Save

..  _user-manual-plugins:

Plugins
=======

`Club Directory`
    Shows the list of clubs with an A-Z navigation and the detail view of
    a club.

`Club Directory Management`
    Lets logged-in frontend users create, edit and manage their own clubs.
    New and changed clubs are hidden until the administrator activates
    them, see :ref:`moderation`.

`Club Directory Search`
    Shows the search form and the search results.

Your administrator configures on which pages these plugins are placed, see
:ref:`site-settings-pages`.

..  _user-manual-plugin-options:

Plugin options
==============

The following options are available for the plugin `Club Directory`.

..  _user-manual-plugin-options-glossary:

Show A-Z links
--------------

You can decide to show A-Z links above or not. Whether an additional
All-link is prepended is configured by your administrator in the site
setting :confval:`clubdirectory.glossary2.showAllLink
<site-setting-glossary2-showalllink>`.

..  _user-manual-plugin-options-category:

Category
--------

Sometimes it makes sense to reduce list of club entries to a given category.
The A-Z links only contain letters of clubs in this category.

..  _user-manual-plugin-options-district:

Choose a district
-----------------

Reduce the list of club entries to a given district. The A-Z links only
contain letters of clubs in this district.

..  _user-manual-detail-view:

Detail view of a club
=====================

The detail view shows the properties of the club, including its
categories and tags, and its addresses. Addresses with a POI record link
to a map, see :confval:`clubdirectory.pidOfMaps2Plugin
<site-setting-pidofmaps2plugin>`.

The search results and the list of own clubs of the plugin
`Club Directory Management` link to the detail view of the plugin
`Club Directory`.

..  _user-manual-club-record:

Club record
===========

#.  Select a Storage Page

#.  Create a new record of type `Club`

#.  Give it a title. We will use the first letter of title to sort it into
    our glossary navigation (if activated).

#.  Enter a description which will be shown on configured detail page

#.  By default you can insert up to 5 images to club record.

#.  At tab `Categories` you can relate club entry to a category. That way
    you can show only club records of a defined category in plugin, if you
    want.
