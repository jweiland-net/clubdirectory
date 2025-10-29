..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 8.0.1
=============

*    Fixed SiteSets name
*    Fixed Plugin Preview

Version 8.0.0
=============

*    TYPO3 Version compatibility for 13 LTS
*    Removed Version compatibility for 12 LTS and below
*    Updated Test Suite
*    Migrate clubdirectory plugins to CTypes
*    Restructured ExtConf file
*    SiteSets added and removed TypoScript configuration
*    Migrated tests to "podman"
*    Upgrade tests against MariaDB 10.4 to 10.5

Version 7.0.1
=============

*   TASK: Moved categories filed of club TCA to seperate categories tab

Version 7.0.0
=============

*   TYPO3 Version compatibility for 12 LTS
*   Backend Module rewrite with new API
*   Test Cases rewrites to TYPO3 Testing Framework
*   Removed compatibility for TYPO3 11 and lower versions
*   All deprecated calls replaced

Version 6.0.1
=============

*   Replace GeneralUtility::makeInstance where possible
*   BUGFIX: Solve AJAX error while calling UpgradeWizards module

Version 6.0.0
=============

*   Add TYPO3 11 compatibility
*   Remove TYPO3 9 compatibility
*   Add EventListener for POI title as replacement for SignalSlot in future
*   Replace ObjectManager with GeneralUtility::makeInstance
*   Migrate SignalSlot of maps2 to EventListener
*   Refactor the controllers

Version 5.3.1
=============

*   Repair export of clubs as CSV.
*   Add district to CSV download

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

Version 5.2.1
=============

*   Add TS glossary options for GlossaryService
*   Remove GlossaryService from controller

Version 5.2.0
=============

*   Set dependency for glossary2 to 5.0.0

Version 5.0.7
=============

*   Move SlugHelper from constructor argument into getSlugHelper()

Version 5.0.6
=============

*   Use Extbase Category

Version 5.0.5
=============

*   Use traverse to disable cHash check in TS condition

Version 5.0.4
=============

*   Use unique instead of uniqueInSite for slug

Version 5.0.3
=============

*   Add translation for yes/no
*   Add barrier-free information into address partial

Version 5.0.2
=============

*   Add CSH for sortTitle in FE
*   Allow storing of hidden records for admins in FE

Version 5.0.1
=============

*   Use correct namespace for EventDispatcher

Version 5.0.0
=============

*   Remove TYPO3 9 compatibility

Version 4.0.4
=============

*   Only add uid to RouteEnhancer, if club was created over frontend

Version 4.0.3
=============

*   Remove title from SVG Icons

Version 4.0.2
=============

*   Update requirement of maps2 to 9.3.0
*   Add title attribute to editPoi VH
*   Store path_segment while frontend edit

Version 4.0.1
=============

*   Replace Google+ column with Instagram

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

Version 3.0.3
=============

*   Show error, if address was not found and redirect to previous action

Version 3.0.2
=============

*   Replace ext icon and all tables icons
*   Use correct address label, if title is of type array
*   [PERFORMANCE] Use group instead of select type for all l10n_parent columns in TCA

Version 3.0.1
=============

*   Do not change label of address records, if shown as inline in club record

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
*   Set return value of getAddresses, getImages and getLogo to array in Club Model
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
