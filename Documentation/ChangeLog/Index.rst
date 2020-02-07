.. include:: ../Includes.txt


.. _changelog:

=========
ChangeLog
=========

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
