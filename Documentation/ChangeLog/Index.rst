.. include:: ../Includes.txt


.. _changelog:

=========
ChangeLog
=========

**Version 4.0.3**

- Remove title from SVG Icons

**Version 4.0.2**

- Update requirement of maps2 to 9.3.0
- Add title attribute to editPoi VH
- Store path_segment while frontend edit

**Version 4.0.1**

- Replace Google+ column with Instagram

**Version 4.0.0**

- Add validator for club manually
- Add Services.yaml
- Add strict_types where possible
- Use GitHub Actions
- Better ImageFileConverter
- Remove icon from sys_category
- Rename ts files to typoscript
- remove unused imports
- Update PHP DocHeaders
- Update requirement of maps2 to 8.0.0
- Remove TYPO3 8 compatibility
- Add TYPO3 10 compatibility
- Add club getter to get first category

**Version 3.0.3**

- Show error, if address was not found and redirect to previous action

**Version 3.0.2**

- Replace ext icon and all tables icons
- Use correct address label, if title is of type array
- [PERFORMANCE] Use group instead of select type for all l10n_parent columns in TCA

**Version 3.0.1**

- Do not change label of address records, if shown as inline in club record

**Version 3.0.0**

- Set default values of relations to 0 in TCA and FlexForm
- Better ordering of TCA columns
- CleanUp Translations
- Add configuration for newContentElementWizard
- Add CSH
- Wrap all templates into new HTML namespace declaration
- Add possibility to delete images from FE
- Move JS include from Controller into TS Setup
- Code CleanUps like correct strict type declaration
- Set return value of getAddresses, getImages and getLogo to array in Club Model
- Add edit/activate links into create/update mails
- Complete reworked Fluid-Templating incl. Bootstrap
- Set default ordering in Search class, so you can see it in FE, too
- Allow sorting of all records in ClubRepository
- Generate better label for address and poi-collection records
- Remove address record, if club was deleted
- Remove poi-collection, if address was deleted
- Remove many PropertyMappingConfiguration for tx_maps2_uid
- Add and Update Documentation
- Add column path_segment for better slugs incl. UpdateWizard
- Simplify address record handling. Remove all theses for loops
- Better implementation with maps2
- Use club as int in many actions, to prevent calling Validation
- Redirect to listMyClubs instead of list after editing a club
- Add club as readOnly to address records for a better overview
- Set width and height for logo and image in TS setup
- Remove form.objectContext ViewHelper
- Remove migration code from TYPO3 6.2
