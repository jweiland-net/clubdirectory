..  include:: /Includes.rst.txt


..  _configuration-typoscript:

==========
TypoScript
==========

..  tip::

    You must configure the extension maps2 first to run this extension
    correctly. See the :ref:`maps2 documentation <maps2:start>`.

The site set `jweiland/clubdirectory` adds the TypoScript of all plugins
to `plugin.tx_clubdirectory`. Please change the values with the
:ref:`site settings <site-settings>` instead of overriding the TypoScript.

..  _configuration-typoscript-mapping:

Site settings in TypoScript
===========================

The site settings are available in TypoScript as follows:

`view.templateRootPaths.1`
    :confval:`site-setting-templaterootpath`

`view.partialRootPaths.2`
    :confval:`site-setting-partialrootpath`

`view.layoutRootPath.1`
    :confval:`site-setting-layoutrootpath`

`persistence.storagePid`
    :confval:`site-setting-storagepid`

`settings.pidOfListPage`
    :confval:`site-setting-pidoflistpage`

`settings.pidOfDetailPage`
    :confval:`site-setting-pidofdetailpage`

`settings.pidOfSearchResults`
    :confval:`site-setting-pidofsearchresults`

`settings.pidOfManagementPage`
    :confval:`site-setting-pidofmanagementpage`

`settings.pidOfMaps2Plugin`
    :confval:`site-setting-pidofmaps2plugin`

`settings.new.uploadFolder`
    :confval:`site-setting-newuploadpath`

`settings.pageBrowser.itemsPerPage`
    :confval:`site-setting-itemsperpage`

`settings.glossary.mergeNumbers`
    :confval:`site-setting-glossary2-mergenumbers`

`settings.glossary.showAllLink`
    :confval:`site-setting-glossary2-showalllink`

`settings.list.image.*`, `settings.show.*`
    :ref:`site-settings-media`

The partial root path `1` is reserved for the partials of maps2, which
render the map in the club form.

..  _configuration-typoscript-uncached:

Uncached plugin for logged-in users
===================================

The list and the detail view render links depending on the logged-in
frontend user, e.g. the edit link for the owner of a club. As the page
cache only varies by usergroup, the site set replaces
`tt_content.clubdirectory_clubdirectory.20` by a `COA_INT` with the
`EXTBASEPLUGIN` of the `Clubdirectory` plugin within the condition
`[frontend.user.isLoggedIn]`. Anonymous visitors still get the cached
page.

If you modify `tt_content.clubdirectory_clubdirectory.20` in your site
package, keep this condition in mind.

All actions of the `Club Directory Management` plugin, except the empty
form of the `new` action, and the search results of the
`Club Directory Search` plugin are not cached at all.
