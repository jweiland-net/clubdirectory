..  include:: /Includes.rst.txt


..  _upgrade:

=======
Upgrade
=======

If you upgrade `clubdirectory` to a newer version, please read this
section carefully!

..  _upgrade-9-0-1:

Update to Version 9.0.1
=======================

This version contains security fixes. Please go through all steps below.

..  _upgrade-9-0-1-export:

Delete the old CSV export file
------------------------------

The backend module streams the CSV export as download now. Earlier
versions stored it publicly accessible in
:file:`public/typo3temp/tx_clubdirectory/export.csv`. Delete this file
and its directory, as it contains the mail addresses, addresses and phone
numbers of all exported clubs:

..  code-block:: bash

    rm -rf public/typo3temp/tx_clubdirectory/

The export contains the clubs of the selected page or folder only, if the
backend user has access to it. Values starting with `=`, `+`, `-` or `@`
are prefixed with a single quote, so spreadsheet applications do not
execute them as formula.

..  _upgrade-9-0-1-usergroup:

Usergroup is checked on the server
----------------------------------

The site setting :confval:`clubdirectory.userGroup
<site-setting-usergroup>` controlled the rendering of the templates only.
Now the actions of the Management plugin to create, edit and update clubs
are denied with the 403 error handling of the site for anonymous visitors
and for frontend users, which are not member of this group. The mail links
with a valid moderation token keep working without login.

Check, that the owners of existing clubs are members of this usergroup.
Additionally restrict the access of the management page to this usergroup
in the page properties.

..  _upgrade-9-0-0:

Update to Version 9.0.0
=======================

This version contains breaking changes. The plugin was split into three
plugins, the frontend form was rewritten and the activation of new or
changed clubs is now protected by a signed link. Please go through all
steps below.

..  _upgrade-9-0-0-requirements:

Requirements
------------

`clubdirectory` requires `jweiland/maps2` in version 12.2.1 or higher now:

..  code-block:: bash

    composer req jweiland/clubdirectory:^9.0 jweiland/maps2:^12.2.1

Please also read the upgrade notes of
:ref:`maps2 <maps2:upgrade>`, if you update maps2, too.

..  _upgrade-9-0-0-database:

Database: upgrade wizard first, then the database analyzer
-----------------------------------------------------------

File :file:`ext_tables.sql` has been removed. TYPO3 13 creates all tables
and columns from TCA now. Some column types change: `website`,
`path_segment` and the text columns get bigger types, `district`, `club`
and `addresses` become `NOT NULL` and the relation and file counters
become unsigned.

Column `barrier_free` of table `tx_clubdirectory_domain_model_address`
changes from `VARCHAR` to `SMALLINT`. In strict SQL mode this change fails,
if there are records with an empty or non-numeric value in this column.

Please execute the upgrade wizard
`clubdirectory_barrierFreeToIntegerUpdate` first. It sets all invalid
values to `0`. After that use :guilabel:`Analyze Database Structure` in
:guilabel:`Admin Tools > Maintenance` (or
`vendor/bin/typo3 extension:setup`) to change the column types.

..  code-block:: bash

    vendor/bin/typo3 upgrade:run clubdirectory_barrierFreeToIntegerUpdate
    vendor/bin/typo3 extension:setup

..  _upgrade-9-0-0-plugins:

New plugins: Management and Search
----------------------------------

The former single plugin `Clubdirectory` has been split into three
plugins (content element types):

`Clubdirectory` (`clubdirectory_clubdirectory`)
    List and detail view of clubs. Existing content elements keep this
    type and their FlexForm settings.

`Club Directory Management` (`clubdirectory_management`)
    List of own clubs, form to create and edit a club, map positioning of
    new clubs and activation of clubs by the administrator.

`Club Directory Search` (`clubdirectory_search`)
    Search form and search results.

There is no automatic migration, as the new plugins usually belong onto
their own pages:

#.  Insert a content element of type `Club Directory Management` on the
    page, where frontend users should manage their clubs.

#.  Insert a content element of type `Club Directory Search` on the page,
    where the search results should be shown.

#.  Configure the pages in the site settings of the site set
    `jweiland/clubdirectory`: :confval:`clubdirectory.pidOfListPage
    <site-setting-pidoflistpage>`, :confval:`clubdirectory.pidOfDetailPage
    <site-setting-pidofdetailpage>`,
    :confval:`clubdirectory.pidOfSearchResults
    <site-setting-pidofsearchresults>` and
    :confval:`clubdirectory.pidOfManagementPage
    <site-setting-pidofmanagementpage>`.

The links to the management, the redirects after saving a club and the
edit and activate links of the notification mails are built with these
settings. See :ref:`site-settings` and :ref:`moderation`.

If you have extended the controllers of `clubdirectory` or registered your
own routes, please check your implementation. The actions `listMyClubs`,
`new`, `create`, `edit`, `update` and `activate` have been moved from
`ClubController` to `ManagementController` and the former `search` action
is `SearchController::listSearchResults` now. The actions `edit` and
`update` of `MapController` have been removed. The map of an existing
club is edited in the club form of the Management plugin.

..  _upgrade-9-0-0-extension-configuration:

Extension configuration
-----------------------

The notification mails to the administrator need a receiver and a
sender. Please set :confval:`emailToAddress <ext-conf-emailtoaddress>` in
the :ref:`extension settings <extensionSettings>`. Check
:confval:`emailFromAddress <ext-conf-emailfromaddress>` and
:confval:`emailFromName <ext-conf-emailfromname>`, too, if there is no
default sender in the mail settings of TYPO3. If a value is missing,
saving a club in frontend fails with an exception, when the mail is sent.

Further set :confval:`poiCollectionPid <ext-conf-poicollectionpid>` to the
storage folder for the POI records which are created from the club
addresses.

The map links of the detail view point to the page in the site setting
:confval:`clubdirectory.pidOfMaps2Plugin <site-setting-pidofmaps2plugin>`.
Add the storage folder of the POI records to the storage folders of the
maps2 plugin on this page. Otherwise the map shows no marker.

..  _upgrade-9-0-0-translations:

Owners and district of translated clubs
---------------------------------------

The columns `fe_users` (:guilabel:`Assign Frontend Users`) and `district`
of table `tx_clubdirectory_domain_model_club` are excluded from
translation now (`l10n_mode = exclude`). Translated clubs use the owners
and the district of the club in default language. The backend does not
show these fields in translated records anymore.

Existing translations still contain their own, often empty values in the
database. DataHandler synchronizes them on the next save of the club in
default language. Please save all translated clubs in default language
once, either in the backend or with your own script using DataHandler.
Until then, a translated club may show no district and its owners may
not get the edit link.

..  _upgrade-9-0-0-update-reference-index:

Site setting updateReferenceIndex removed
-----------------------------------------

The site setting `clubdirectory.updateReferenceIndex` has been removed.
The site set always sets `persistence.updateReferenceIndex = 1`. To
disable it, override
`plugin.tx_clubdirectory.persistence.updateReferenceIndex` with your own
TypoScript.

..  _upgrade-9-0-0-user-group:

Extension setting userGroup moved to the site settings
------------------------------------------------------

The extension setting `userGroup` has been moved to the site setting
:confval:`clubdirectory.userGroup <site-setting-usergroup>`. The template
variable `allowedUserGroup` has been removed, use `{settings.userGroup}`
in your own templates instead. Set the UID of the usergroup of the club
owners in the site settings of your site, the default is `1`.

The setting controls the visibility of the links to create a new club and
to list the own clubs (:html:`<f:security.ifHasRole>` in
:file:`Partials/Club/InsertEditLinks.html`) and of the list in
:file:`Templates/Management/ListMyClubs.html`. Users not in this group get
a message, that they have no access to this page.

The setting is not an access check on the server. A logged-in frontend
user outside of this group can still call the actions to create a club
directly. Restrict the access of the management page to the same
usergroup in the page properties. See :ref:`moderation-access`.

..  _upgrade-9-0-0-sub-category:

Search property subCategory removed
-----------------------------------

The deprecated property `subCategory` of the search model has been
removed. The methods :php:`Search::getSubCategory()` and
:php:`Search::setSubCategory()` do not exist anymore. If you use them in
your own templates or code, use :php:`Search::getCategory()` and
:php:`Search::setCategory()` instead.

Links and search forms have to submit the category as
`search[category]` instead of `search[subCategory]`.

Old URLs still containing `search[subCategory]` do not result in an
exception. The parameter is ignored, so the search results are not
filtered by this category anymore.

..  _upgrade-9-0-0-page-tsconfig:

Page TSconfig removed
---------------------

The extension does not add `TCEFORM` page TSconfig based on the extension
settings `rootCategory` and `userGroup` anymore. Without it, the backend
offers the categories on root level in the plugin and the club records
and all frontend users in the field :guilabel:`Assign Frontend Users`.

Add the page TSconfig to your site package to restore the previous
restrictions, see :ref:`page-tsconfig`. The extension setting
:confval:`rootCategory <ext-conf-rootcategory>` only affects the category
lists in the frontend now.

..  _upgrade-9-0-0-page-cache:

Page cache of list and detail pages
-----------------------------------

The list and detail pages are tagged with the cache tags of the clubs and
are flushed after saving a club. You can remove a `TCEMAIN.clearCacheCmd`,
which you have configured for these pages only because of
`clubdirectory`. See :ref:`moderation-flow`.

..  _upgrade-9-0-0-templates:

Template overrides
------------------

If you have overridden templates or partials of `clubdirectory` in your
site package, please compare them with the original files. Especially:

*   The templates of the management actions have been moved from
    :file:`Templates/Club/` to :file:`Templates/Management/` (`Edit`,
    `ListMyClubs`, `New`, `Update`). :file:`Templates/Club/Search.html`
    has been moved to :file:`Templates/Search/ListSearchResults.html`.
    The new template :file:`Templates/Search/Show.html` renders the search
    form.

*   All sections of the partial :file:`Partials/Club/FormFields.html` have
    been removed: `renderTextProperty`, `renderSelectProperty`,
    `renderTextareaProperty`, `renderCheckboxProperty`,
    `renderMaps2Property`, `renderAddresses`, `renderAddressProperties`,
    `renderLogo`, `renderImages`, `renderLabel` and `renderHelpMessage`.
    Every field is written out now, so you can adjust each field on its
    own. Overrides calling one of these sections have to be adapted.

*   Logo and images use the file upload handling of Extbase. Use
    :html:`<f:form.upload property="logo"/>` and
    :html:`<f:form.upload property="images" multiple="1"/>` instead of the
    former indexed properties like `logo.0` or `images.1`. Existing files
    are removed with :html:`<f:form.uploadDeleteCheckbox>`.

*   The address type has no default anymore. Address blocks without an
    address type are skipped. A chosen address type requires street, ZIP
    and city and a club needs at least one address.

*   Links and forms pointing to the management or search actions need the
    arguments `controller`, `pluginName` and `pageUid`, e.g.
    `pluginName="Management"` and
    `pageUid="{settings.pidOfManagementPage}"`.

*   The mail templates :file:`Templates/Management/Update.html` and
    :file:`Templates/Map/Create.html` have to pass the argument
    `moderationToken` to the edit and activate links. The edit template
    :file:`Templates/Management/Edit.html` has to submit the token as
    hidden field. Without the token the administrator gets an "access
    denied" page, see :ref:`moderation`.

*   The site setting `clubdirectory.feJavaScript` and the jQuery UI dialog
    script for form hints have been removed. If you have used this
    setting to include your own JavaScript, include it in your site
    package now.

*   :file:`Partials/Club/FormFields.html` contains the new select field
    `district`. The actions `new` and `edit` of the Management plugin
    assign the variable `districts`.

*   The detail link in :file:`Partials/Club/List.html` sets the arguments
    `controller="Club"`, `pluginName="Clubdirectory"` and
    `extensionName="Clubdirectory"`, as the partial is rendered by all
    three plugins. Without them, the detail links of the search results
    and of the own clubs do not work.

*   :file:`Partials/Club/Properties.html` shows the categories and the
    tags of the club.

*   The condition of the "last" link in
    :file:`Partials/Component/Pagination.html` uses
    `{paginator.currentPageNumber}` now. `ClubPagination` does not
    provide the current page number.

..  _upgrade-9-0-0-checkfaluploads:

Rights checkbox of checkfaluploads
----------------------------------

The original templates do not contain the rights checkbox of
`jweiland/checkfaluploads` anymore. If checkfaluploads is installed,
`clubdirectory` still adds its validator to the uploads of `logo` and
`images` of the Management plugin. Without a checked rights checkbox
every upload is rejected.

Add the rights checkbox to your template override of
:file:`Partials/Club/FormFields.html` in your site package, as described
in the :ref:`developer manual of checkfaluploads
<checkfaluploads:developer-manual>`. `clubdirectory` registers the
validator with the property paths `club.logo` and `club.images`, so the
checkboxes have to be submitted with these names:

..  code-block:: text

    tx_clubdirectory_management[club][logo][rights]
    tx_clubdirectory_management[club][images][rights]

..  _upgrade-9-0-0-moderation:

Access control, moderation and error pages
------------------------------------------

*   Only frontend users assigned to a club and backend administrators are
    allowed to edit a club. All other requests stop with the 403 error
    handling of the site.

*   A club is activated by the administrator with the signed link of the
    notification mail only. Owners can not activate their own clubs
    anymore. Links of mails sent before the update and links of older
    mails of a changed club do not work anymore. Activate such clubs in
    the backend instead.

*   The detail view shows the 404 error handling of the site for hidden,
    deleted or unknown clubs instead of an empty page.

Please configure the
:ref:`error handling <t3coreapi:sitehandling-errorhandling>` of your site
for the status codes 403 and 404. Read :ref:`moderation` for details.

..  _upgrade-9-0-0-routing:

Routing and path segments
-------------------------

New clubs created in frontend get a path segment generated like
DataHandler does: the title, made unique with a suffix like `-1`. The UID
is not part of the path segment anymore. Existing path segments are not
changed. If your route enhancer has a requirement for the UID in the path
segment like `^[a-zA-Z0-9]+\-[0-9]+$`, remove it. See :ref:`routes`.

..  _upgrade-9-0-0-developer:

Removed and changed PHP API
---------------------------

*   `InitializeControllerActionEvent` and `PostCheckFileReferenceEvent`
    have been removed and the methods of `PreProcessControllerActionEvent`
    have changed. Please migrate your event listeners as described in
    :ref:`developer-events`.
*   The event listeners `AssignMediaTypeConverterEventListener` and
    `DeleteUploadedFilesEventListener` and the type converter
    `UploadMultipleFilesConverter` have been removed. Logo and images use
    the file upload handling of Extbase.
*   `ClubValidator` does not modify the club anymore. It validates the
    addresses only. Addresses without an address type are removed from the
    request in `ManagementController` before the property mapping.
*   `Club::getOriginalLogo()` and `Club::getOriginalImages()` have been
    removed. `Club::getLogo()` and `Club::getImages()` return the
    :php:`ObjectStorage` now.
*   `Club::setDistrict()` accepts `null` to remove the district.
*   `ClubPagination` does not read the arguments from the global request
    anymore. The constructor has a second argument for the arguments of
    the current plugin request:
    :php:`__construct(PaginatorInterface $paginator, array
    $pluginArguments = [])`. The public methods `getPluginArguments()`
    and `getRequestFromGlobalScope()` have been removed. If you create a
    `ClubPagination` in your own code, pass
    :php:`$request->getArguments()` of the Extbase request. Otherwise
    the page links lose the arguments of the plugin, e.g. the search.
*   The magic finder `CategoryRepository::findByParent()` is not used
    anymore and its `@method` annotation has been removed. Use
    `CategoryRepository::getCategories()` to get the categories of the
    configured root category.

..  _upgrade-9-0-0-caches:

Flush caches
------------

As many PHP classes, templates and TypoScript have changed, flush all
caches in :guilabel:`Admin Tools > Maintenance` after the update.

..  _upgrade-8-0-0:

Update to Version 8.0.0
=======================

This version supports TYPO3 version 13 LTS only. Lower version support removed.
Restructured ExtConf file and added SiteSet for the extension. Used Dependency
Injection wherever possible. Migrate clubdirectory plugins to CTypes and added
new migration wizard.

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

In case of TYPO3 9 you should execute the upgrade wizard
`clubdirectoryUpdateSlug` of clubdirectory in Installtool, to fill
path_segments for better slugs.

As we have modified a lot of PHP classes you should clear all caches.
