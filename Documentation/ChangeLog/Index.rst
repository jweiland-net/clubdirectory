..  include:: /Includes.rst.txt
..  _changelog:

=========
ChangeLog
=========

..  _changelog-9-0-3:

Version 9.0.3
=============

Tasks:

*   [TASK] Remove the conversion of an empty `pidOfDetailPage` to `NULL`.
    Since TYPO3 13 the Fluid ViewHelpers `f:link.action`, `f:uri.action`
    and `f:form` link to the current page, if `pageUid` is `0`

..  _changelog-9-0-2:

Version 9.0.2
=============

Tasks:

*   [TASK] Analyse the extension with PHPStan level 6 without errors and
    run PHPStan in CI. The baseline contains entries of this extension
    only
*   [TASK] Modernize the documentation for the render-guides rendering

Developers:

*   The events `PreProcessControllerActionEvent` and
    `PostProcessFluidVariablesEvent` and their interface
    `ControllerActionEventInterface` type the request with
    :php:`\TYPO3\CMS\Extbase\Mvc\RequestInterface` instead of
    :php:`\TYPO3\CMS\Extbase\Mvc\Request`. Use the interface in your
    event listeners, too

..  _changelog-9-0-1:

Version 9.0.1
=============

Please read :ref:`upgrade-9-0-1` before updating.

Security:

*   [SECURITY] Stream the CSV export as download instead of storing it in
    :file:`typo3temp/`, neutralize spreadsheet formulas and check the
    page permissions of the backend user
*   [SECURITY] Allow the actions of the Management plugin for logged-in
    members of `clubdirectory.userGroup` or with a valid moderation token
    only
*   [SECURITY] Activate clubs via POST of a confirmation form only. The
    activate link of the mail shows the new action `confirmActivation`
*   [SECURITY] Action `activate` gets the club UID as argument `clubUid`
    only and maps no property of the club
*   [SECURITY] Allow the map step (`Map` new and create) for hidden clubs
    only and keep the club hidden. `Map` create saves latitude and
    longitude of the existing POI collections only. Further fields in
    overridden :file:`Map/New.html` templates are ignored. Addresses
    without POI collection are kept
*   [SECURITY] Render website and social links with typolink, which drops
    `javascript:` and `data:` URIs. These links are not rendered
*   [SECURITY] Allow external URLs only in the backend fields website,
    Facebook, Twitter and Instagram (TCA type `link`). Run the database
    analyzer, as the social link columns change to `TEXT`
*   [SECURITY] Validate website and social links of the Management form
    as complete URLs incl. scheme, e.g. `https://`

Tasks:

*   [TASK] Export CSV according to RFC 4180: comma as delimiter, all
    fields enclosed in double quotes and CRLF as line break. Earlier
    versions used semicolon and single quotes

..  _changelog-9-0-0:

Version 9.0.0
=============

This version contains breaking changes. Please read
:ref:`upgrade-9-0-0` before updating.

Breaking changes:

*   [!!!][TASK] Split the Clubdirectory plugin into the plugins
    `Clubdirectory` (list, show), `Management` (own clubs, new, edit,
    activate) and `Search` (search form, search results)
*   [!!!][TASK] Use Extbase file upload handling for logo and images.
    Remove `UploadMultipleFilesConverter`, `PostCheckFileReferenceEvent`,
    `AssignMediaTypeConverterEventListener` and
    `DeleteUploadedFilesEventListener`
*   [!!!][TASK] Remove the checkfaluploads checkbox from the templates
*   [!!!][TASK] Remove the jQuery UI dialog script for form hints and the
    site setting `clubdirectory.feJavaScript`
*   [!!!][TASK] Separate the address cleanup from the club validation
*   [!!!][TASK] Write out all club form fields without sections
*   [!!!][TASK] Remove `InitializeControllerActionEvent`. The arguments of
    `PreProcessControllerActionEvent` have changed
*   [!!!][TASK] Remove the site setting
    `clubdirectory.updateReferenceIndex`
*   [!!!][TASK] Remove the page TSconfig for categories and frontend users
    from :file:`ext_localconf.php`
*   [!!!][TASK] Move the extension setting `userGroup` to the site
    setting `clubdirectory.userGroup`. It controls the visibility of the
    links to create a club and to list the own clubs, and of the list of
    the own clubs
*   [!!!][TASK] Remove the deprecated search property `subCategory`. Use
    `search[category]` instead. Old links with `search[subCategory]` are
    ignored
*   [TASK] Remove `ext_tables.sql`. TYPO3 creates all columns from TCA
    now. Run the upgrade wizard before the database analyzer
*   [!!!][BUGFIX] Pass the arguments of the plugin request into
    `ClubPagination`. The constructor is
    `__construct(PaginatorInterface $paginator, array $pluginArguments
    = [])` now. The public methods `getPluginArguments()` and
    `getRequestFromGlobalScope()` have been removed

Security:

*   [SECURITY] Restrict the club management to the owners of a club
*   [SECURITY] Allow the activation by the administrator only
*   [SECURITY] Sign the club `tstamp` in moderation tokens, so old mail
    links expire

Features and tasks:

*   [FEATURE] Add site settings for the list page, the search results page
    and the management page
*   [FEATURE] Add the optional district to the club form
*   [FEATURE] Show categories and tags in the detail view
*   [TASK] Require maps2 12.2.1 or higher
*   [TASK] Suggest jweiland/checkfaluploads 6.x
*   [TASK] Generate a unique path segment like DataHandler, without the
    club UID
*   [TASK] Restrict MapController to the creation of clubs. The map of an
    existing club is edited in the club form
*   [TASK] Add upgrade wizard `clubdirectory_barrierFreeToIntegerUpdate`
*   [TASK] Remove TCA columns created by TYPO3 core
*   [TASK] Move all labels of the site set into a translatable `labels.xlf`
*   [TASK] Add tests for club access, moderation and helpers
*   [TASK] Replace the deprecated magic finder
    `CategoryRepository::findByParent()` and remove its `@method`
    annotation
*   [TASK] Allow `null` in `Club::setDistrict()`

Bugfixes:

*   [BUGFIX] Register the Management and Search plugins as content elements
*   [BUGFIX] Submit search forms to the search results page and remove the
    obsolete cHash condition
*   [BUGFIX] Fix links in mails and redirects to the management plugin
*   [BUGFIX] Link edit in detail view to the management plugin
*   [BUGFIX] Redirect to the club list of the list plugin
*   [BUGFIX] Show a meaningful message, if an address can not be geocoded
*   [BUGFIX] Do not render the map without POI record
*   [BUGFIX] Use the club title as POI title only
*   [BUGFIX] Show 404 page for hidden or unknown clubs
*   [BUGFIX] Allow sorting search results by title only
*   [BUGFIX] Fix invalid search order
*   [BUGFIX] Render the club plugin uncached for logged-in users
*   [BUGFIX] Explain the moderation in created and updated messages
*   [BUGFIX] Show the activity of the club in mails
*   [BUGFIX] Allow removing all categories of a club
*   [BUGFIX] Split select fields into single and multiple variant
*   [BUGFIX] Add telephone field to the address form
*   [BUGFIX] Render email, telephone and fax fields with their HTML5 types
*   [BUGFIX] Clean up help text of categories field
*   [BUGFIX] Use selected page as storage in export module
*   [BUGFIX] Handle select values as array in address label
*   [BUGFIX] Add site set labels to Crowdin configuration
*   [BUGFIX] Restore the identifier `clubdirectoryUpdateSlug` of the slug
    upgrade wizard
*   [BUGFIX] Show flash messages on the club list
*   [BUGFIX] Tag club pages to clear them after changes
*   [BUGFIX] Exclude owners and district of clubs from translation, see
    :ref:`upgrade-9-0-0-translations`
*   [BUGFIX] Create POI records only if all addresses were found
*   [BUGFIX] Keep the search arguments in the page browser, so page 2 of
    the search results works
*   [BUGFIX] Hide the last page link on the last page
*   [BUGFIX] Link the search results and the own clubs to the detail view
    of the Clubdirectory plugin
*   [BUGFIX] Respect category and district of the plugin in the glossary

..  _changelog-8-1-3:

Version 8.1.3
=============

*   [TASK] Add RestrictAccessEventListener to check update actions against
    unauthorised edits by non-owner fe_users

..  _changelog-8-1-2:

Version 8.1.2
=============

*   [BUGFIX] Replace hardcoded tab labels with localized labels in tt_content

..  _changelog-8-1-1:

Version 8.1.1
=============

*   [TASK] Improve robustness of hidden object resolution and event

..  _changelog-8-1-0:

Version 8.1.0
=============

*   [TASK] Simplify access restiction in Backend Module configuration

..  _changelog-8-0-6:

Version 8.0.6
=============

*   [BUGFIX] Issue with frontend file upload in create and upload module fixed

..  _changelog-8-0-5:

Version 8.0.5
=============

*   [BUGFIX] Remove deprecated usage of SoftRef parser: images

..  _changelog-8-0-4:

Version 8.0.4
=============

*   [BUGFIX] Fixed Property Mapper with CheckFal validation issues

..  _changelog-8-0-3:

Version 8.0.3
=============

*   Retrieve FE user from request instead from TSFE
*   Set return value for create and update actions

..  _changelog-8-0-2:

Version 8.0.2
=============

*   Repair search and filtering
*   Repair CSV download in BE detail view
*   Repair link to show detail action
*   Repair backend module
*   Remove userfunc from ext_localconf.php
*   Remove deprecated and removed CSH lang files
*   Upgrade sys_language_uid, hidden, starttime and endtime of TCA
*   Use type page for pages in site setting definitions

..  _changelog-8-0-1:

Version 8.0.1
=============

*   Fixed SiteSets name
*   Fixed Plugin Preview

..  _changelog-8-0-0:

Version 8.0.0
=============

*   TYPO3 Version compatibility for 13 LTS
*   Removed Version compatibility for 12 LTS and below
*   Updated Test Suite
*   Migrate clubdirectory plugins to CTypes
*   Restructured ExtConf file
*   SiteSets added and removed TypoScript configuration
*   Migrated tests to "podman"
*   Upgrade tests against MariaDB 10.4 to 10.5

..  _changelog-7-0-1:

Version 7.0.1
=============

*   TASK: Moved categories filed of club TCA to seperate categories tab

..  _changelog-7-0-0:

Version 7.0.0
=============

*   TYPO3 Version compatibility for 12 LTS
*   Backend Module rewrite with new API
*   Test Cases rewrites to TYPO3 Testing Framework
*   Removed compatibility for TYPO3 11 and lower versions
*   All deprecated calls replaced

..  _changelog-6-0-1:

Version 6.0.1
=============

*   Replace GeneralUtility::makeInstance where possible
*   BUGFIX: Solve AJAX error while calling UpgradeWizards module

..  _changelog-6-0-0:

Version 6.0.0
=============

*   Add TYPO3 11 compatibility
*   Remove TYPO3 9 compatibility
*   Add EventListener for POI title as replacement for SignalSlot in future
*   Replace ObjectManager with GeneralUtility::makeInstance
*   Migrate SignalSlot of maps2 to EventListener
*   Refactor the controllers

..  _changelog-5-3-1:

Version 5.3.1
=============

*   Repair export of clubs as CSV.
*   Add district to CSV download

..  _changelog-5-3-0:

Version 5.3.0
=============

*   Use our own FrontendUserRepo and model.
*   Move methods to get current user to new FrontendUSerRepo.
*   Remove column "sortTitle"
*   Deprecate getter/setter for sub-categories in search model
*   Update label for filter by
*   Do not show filter box, if a category was preset
*   Repair pageTSConfig for category fields
*   Remove DE translation. THX Crowdin
*   Add district as select field to search template
*   Remove hint how to configure categories in TCA form
*   Repair label of hidden field in TCA
*   Move activity behind title
*   Reduce field height of activity/times to 5 rows

..  _changelog-5-2-1:

Version 5.2.1
=============

*   Add TS glossary options for GlossaryService
*   Remove GlossaryService from controller

..  _changelog-5-2-0:

Version 5.2.0
=============

*   Set dependency for glossary2 to 5.0.0

..  _changelog-5-0-7:

Version 5.0.7
=============

*   Move SlugHelper from constructor argument into getSlugHelper()

..  _changelog-5-0-6:

Version 5.0.6
=============

*   Use Extbase Category

..  _changelog-5-0-5:

Version 5.0.5
=============

*   Use traverse to disable cHash check in TS condition

..  _changelog-5-0-4:

Version 5.0.4
=============

*   Use unique instead of uniqueInSite for slug

..  _changelog-5-0-3:

Version 5.0.3
=============

*   Add translation for yes/no
*   Add barrier-free information into address partial

..  _changelog-5-0-2:

Version 5.0.2
=============

*   Add CSH for sortTitle in FE
*   Allow storing of hidden records for admins in FE

..  _changelog-5-0-1:

Version 5.0.1
=============

*   Use correct namespace for EventDispatcher

..  _changelog-5-0-0:

Version 5.0.0
=============

*   Remove TYPO3 9 compatibility

..  _changelog-4-0-4:

Version 4.0.4
=============

*   Only add uid to RouteEnhancer, if club was created over frontend

..  _changelog-4-0-3:

Version 4.0.3
=============

*   Remove title from SVG Icons

..  _changelog-4-0-2:

Version 4.0.2
=============

*   Update requirement of maps2 to 9.3.0
*   Add title attribute to editPoi VH
*   Store path_segment while frontend edit

..  _changelog-4-0-1:

Version 4.0.1
=============

*   Replace Google+ column with Instagram

..  _changelog-4-0-0:

Version 4.0.0
=============

*   Add validator for club manually
*   Add Services.yaml
*   Add strict_types where possible
*   Use GitHub Actions
*   Better ImageFileConverter
*   Remove icon from sys_category
*   Rename ts files to typoscript
*   remove unused imports
*   Update PHP DocHeaders
*   Update requirement of maps2 to 8.0.0
*   Remove TYPO3 8 compatibility
*   Add TYPO3 10 compatibility
*   Add club getter to get first category

..  _changelog-3-0-3:

Version 3.0.3
=============

*   Show error, if address was not found and redirect to previous action

..  _changelog-3-0-2:

Version 3.0.2
=============

*   Replace ext icon and all tables icons
*   Use correct address label, if title is of type array
*   [PERFORMANCE] Use group instead of select type for all l10n_parent
    columns in TCA

..  _changelog-3-0-1:

Version 3.0.1
=============

*   Do not change label of address records, if shown as inline in club record

..  _changelog-3-0-0:

Version 3.0.0
=============

*   Set default values of relations to 0 in TCA and FlexForm
*   Better ordering of TCA columns
*   CleanUp Translations
*   Add configuration for newContentElementWizard
*   Add CSH
*   Wrap all templates into new HTML namespace declaration
*   Add possibility to delete images from FE
*   Move JS include from Controller into TS Setup
*   Code CleanUps like correct strict type declaration
*   Set return value of getAddresses, getImages and getLogo to array in Club
    Model
*   Add edit/activate links into create/update mails
*   Complete reworked Fluid-Templating incl. Bootstrap
*   Set default ordering in Search class, so you can see it in FE, too
*   Allow sorting of all records in ClubRepository
*   Generate better label for address and poi-collection records
*   Remove address record, if club was deleted
*   Remove poi-collection, if address was deleted
*   Remove many PropertyMappingConfiguration for tx_maps2_uid
*   Add and Update Documentation
*   Add column path_segment for better slugs incl. UpdateWizard
*   Simplify address record handling. Remove all theses for loops
*   Better implementation with maps2
*   Use club as int in many actions, to prevent calling Validation
*   Redirect to listMyClubs instead of list after editing a club
*   Add club as readOnly to address records for a better overview
*   Set width and height for logo and image in TS setup
*   Remove form.objectContext ViewHelper
*   Remove migration code from TYPO3 6.2
