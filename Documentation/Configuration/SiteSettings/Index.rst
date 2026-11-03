..  include:: /Includes.rst.txt
..  _site-settings:

=============
Site settings
=============

`clubdirectory` ships the site set `jweiland/clubdirectory`. Add it as
dependency to the site set of your site package or to your site
configuration. All settings can be changed in the backend module
:guilabel:`Site Management > Settings` or in the file
:file:`config/sites/<identifier>/settings.yaml`.

..  code-block:: yaml
    :caption: config/sites/main/settings.yaml

    clubdirectory:
      storagePid: 12
      pidOfListPage: 20
      pidOfDetailPage: 21
      pidOfSearchResults: 22
      pidOfManagementPage: 23
      userGroup: 2

..  _site-settings-templates:

Templates
=========

..  confval:: clubdirectory.templateRootPath
    :name: site-setting-templaterootpath
    :type: string
    :default: `EXT:clubdirectory/Resources/Private/Templates/`

    Additional template root path, e.g. of your site package. See
    :ref:`upgrade-9-0-0-templates` before overriding the templates.

..  confval:: clubdirectory.partialRootPath
    :name: site-setting-partialrootpath
    :type: string
    :default: `EXT:clubdirectory/Resources/Private/Partials/`

    Additional partial root path, e.g. of your site package.

..  confval:: clubdirectory.layoutRootPath
    :name: site-setting-layoutrootpath
    :type: string
    :default: `EXT:clubdirectory/Resources/Private/Layouts/`

    Additional layout root path, e.g. of your site package.

..  _site-settings-pages:

Pages
=====

The plugins `Clubdirectory`, `Club Directory Management` and
`Club Directory Search` are usually placed on different pages. Links,
forms, redirects and the links of the notification mails use these
settings to find the page of the responsible plugin.

..  confval:: clubdirectory.storagePid
    :name: site-setting-storagepid
    :type: page
    :default: 0

    Storage folder of the club records. New clubs created in frontend are
    stored in this folder, too.

..  confval:: clubdirectory.pidOfListPage
    :name: site-setting-pidoflistpage
    :type: page
    :default: 0

    Page with the `Clubdirectory` plugin, which shows the list of clubs.
    Users are redirected to this page after they have created a club and
    the administrator after the activation of a club. The list of own
    clubs links back to this page.

..  confval:: clubdirectory.pidOfDetailPage
    :name: site-setting-pidofdetailpage
    :type: page
    :default: 0

    Page with the `Clubdirectory` plugin, which shows the detail view of a
    club. If empty, the detail view is shown on the current page.

..  confval:: clubdirectory.pidOfSearchResults
    :name: site-setting-pidofsearchresults
    :type: page
    :default: 0

    Page with the `Club Directory Search` plugin. The search and filter
    forms are submitted to this page.

..  confval:: clubdirectory.pidOfManagementPage
    :name: site-setting-pidofmanagementpage
    :type: page
    :default: 0

    Page with the `Club Directory Management` plugin. The links to create a
    new club, to list the own clubs and to edit a club point to this page.
    The edit and activate links in the notification mails to the
    administrator point to this page, too. See :ref:`moderation`.

..  confval:: clubdirectory.pidOfMaps2Plugin
    :name: site-setting-pidofmaps2plugin
    :type: page
    :default: 0

    Page with the maps2 plugin :ref:`Maps2: Show map
    <maps2:plugins-show-map>`. In the detail view each address of a club
    with a POI record links to this page to show the address on a map.

    The storage folders of this maps2 plugin must include the storage
    folder of the POI records, configured in the extension setting
    :confval:`poiCollectionPid <ext-conf-poicollectionpid>`. Otherwise
    maps2 does not find the POI record and the map shows no marker.

..  confval:: clubdirectory.userGroup
    :name: site-setting-usergroup
    :type: int
    :default: 1

    UID of the frontend usergroup allowed to create new clubs. Only members
    of this group see the links to create a new club and to list the own
    clubs (partial :file:`Partials/Club/InsertEditLinks.html`) and the list
    of the own clubs (:file:`Templates/Management/ListMyClubs.html`). All
    other users get a message, that they have no access to this page.

    Without a valid moderation token of the mail links, only logged-in
    members of this group are allowed to create, edit and update clubs.
    `-2` allows all logged-in frontend users, `0` denies the access for
    all of them. Restrict the access of the management page to this group,
    too. See :ref:`moderation-access`.

..  _site-settings-settings:

Settings
========

..  confval:: clubdirectory.pageBrowser.itemsPerPage
    :name: site-setting-itemsperpage
    :type: int
    :default: 10

    Number of clubs shown on one page of the list and the search results.

..  confval:: clubdirectory.newUploadPath
    :name: site-setting-newuploadpath
    :type: string
    :default: `1:user_upload/tx_clubdirectory/`

    Folder (combined identifier) for the logo and the images uploaded in
    the club form. The folder is created, if it does not exist. One logo
    and up to five images of type GIF, JPEG or PNG with up to 4 MB each
    are allowed.

..  _site-settings-media:

Image sizes
===========

The settings `clubdirectory.list.image.*` define the size of the club
image in the list view and in the club form. The settings
`clubdirectory.show.logo.*` and `clubdirectory.show.image.*` define the
sizes of the logo and the images in the detail view. Each of them provides
the properties `width`, `height`, `minWidth`, `minHeight`, `maxWidth` and
`maxHeight` of the :ref:`f:image ViewHelper <t3viewhelper:typo3-fluid-image>`.

..  _site-settings-glossary2:

Glossary
========

..  confval:: clubdirectory.glossary2.mergeNumbers
    :name: site-setting-glossary2-mergenumbers
    :type: bool
    :default: true

    Merge club titles starting with numbers to `0-9` in the glossary.

..  confval:: clubdirectory.glossary2.showAllLink
    :name: site-setting-glossary2-showalllink
    :type: bool
    :default: true

    Prepend an additional link in front of the glossary to show all clubs
    again.
