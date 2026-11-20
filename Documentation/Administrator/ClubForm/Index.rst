:navigation-title: Club form

..  include:: /Includes.rst.txt
..  _club-form:

================================
Club form based on EXT:form
================================

..  versionadded:: 10.0.0

    Clubs are created and edited with a form based on EXT:form. It
    replaces the Extbase form and the map step of the Management plugin.

Frontend users create and edit their clubs with the plugin
`Club Directory Management` (action `perform`). The plugin renders the
form definition :file:`EXT:clubdirectory/Configuration/Form/NewClub.form.yaml`
of EXT:form.

..  contents::
    :caption: Table of contents
    :local:

..  _club-form-requirements:

Requirements of the club form
=============================

*   The system extension EXT:form (:composer:`typo3/cms-form`). The site
    set `jweiland/clubdirectory` depends on its site set `typo3/form`.
*   EXT:maps2 12.3 or newer. It provides the form element
    `Maps2PoiPosition`, which shows a map with a draggable marker. The
    site set `jweiland/clubdirectory` depends on the site set
    `jweiland/maps2-default`, which registers the element.
*   A working geocoding configuration of maps2 and a storage folder for
    the POI records in :confval:`poiCollectionPid
    <ext-conf-poicollectionpid>`.
*   All requirements of :ref:`moderation-requirements`.

..  _club-form-steps:

Steps of the club form
======================

The form has two pages:

#.  :guilabel:`Club data`: Title, categories, activity, description,
    contact data, website, district, up to three addresses, social media
    links, tags, the logo and up to three images. For each address with
    an address type, street, ZIP and city are required.

#.  :guilabel:`Map positions`: One map per used address with the position
    of the address. The marker can be dragged to correct the position.

When the first page is submitted, `clubdirectory` geocodes each used
address with maps2. Addresses which were geocoded before and have not
been changed are not geocoded again, so a corrected position is kept. If
an address can not be geocoded, the street of the address shows an error
and the user stays on the first page:

*   The address was not found: The user is asked to check the address.
*   The map service rejected the request (e.g. an API key restricted to
    other IP addresses), the quota is exceeded or the service is not
    available: The user gets a hint to inform the administrator. The
    technical message of the map service is written to the TYPO3 log only.

After the second page is submitted, the club is saved, the administrator
gets the moderation mail and the club is hidden until it is activated. See
:ref:`moderation-flow`.

..  _club-form-map-consent:

Map consent
-----------

The element `Maps2PoiPosition` respects the setting of maps2 to request
the consent of the visitor before data is sent to the map provider. The
geocoding of the addresses is done on the server and does not need this
consent. See the documentation of maps2 for the element.

..  _club-form-edit:

Edit a club
===========

The edit links (detail view, list of the own clubs and moderation mail)
point to the action `perform` with the argument `club`. The form is
prefilled with the club incl. its addresses, the positions of their POI
records, categories, logo and images. An edited club keeps its page, its
creation date, its path segment and its frontend users.

Addresses without address type are removed together with their POI
record. Logo and images can be replaced by uploading a new file at the
same position. The image upload of EXT:form can not remove a file, use
the backend to remove it.

..  _club-form-storage:

Storage of the club form
========================

The finisher `SaveClubFinisher` stores the club, its categories, the
frontend user (new clubs only), the addresses, their POI records and the
file references. It checks the access with the same rules as the
Management plugin again, see :ref:`moderation-access`, and stores values
of select boxes and checkboxes only, if they are an option of the element.

*   New clubs and addresses are stored on the first page of
    :confval:`clubdirectory.storagePid <site-setting-storagepid>`.
*   The logo and the images are stored in
    :confval:`clubdirectory.newUploadPath <site-setting-newuploadpath>`.
*   The description is stored like the backend RTE does. With the
    :ref:`rich text editor <frontend-rich-text-editor>` it is sanitized
    and transformed like in the backend.
*   New POI records are created in :confval:`poiCollectionPid
    <ext-conf-poicollectionpid>` with the club title as title. The
    position of existing POI records is updated.

..  _club-form-checkfaluploads:

Rights checkbox of EXT:checkfaluploads
--------------------------------------

The uploads of logo and images are plain EXT:form elements of type
`ImageUpload`. To add the rights checkbox of EXT:checkfaluploads (6.0.0 or
higher), add a checkbox with :yaml:`checkboxType: uploadRights` and
:yaml:`referenceUploadIdentifier` to your copy of the form definition, as
described in the :ref:`documentation of EXT:checkfaluploads
<checkfaluploads:developer-manual>`.

..  _club-form-configuration:

Configuration of the club form
==============================

The TypoScript setting :typoscript:`plugin.tx_clubdirectory.settings.overrideForm`
overrides values of the form definition. The site set uses it for the
storage page, the upload folder, the management page of the mail links
and the redirect after saving:

..  code-block:: typoscript
    :caption: EXT:clubdirectory/Configuration/Sets/Clubdirectory/setup.typoscript (excerpt)

    plugin.tx_clubdirectory.settings.overrideForm {
      renderables.0.renderables.18.properties.saveToFileMount = {$clubdirectory.newUploadPath}
      finishers.1.options.1.databaseColumnMappings.pid.value = {$clubdirectory.storagePid}
      finishers.2.options.variables.pidOfManagementPage = {$clubdirectory.pidOfManagementPage}
      finishers.5.options.pageUid = {$clubdirectory.pidOfManagementPage}
    }

Sender and recipient of the moderation mail are taken from the extension
settings :confval:`emailFromAddress <ext-conf-emailfromaddress>` and
:confval:`emailToAddress <ext-conf-emailtoaddress>`, if they are not set in
the options of the finisher `EmailToReceiver`.

The labels of the form are translated with
:file:`EXT:clubdirectory/Resources/Private/Language/locallang_ext_form.xlf`.

..  _club-form-own-definition:

Use your own form definition
----------------------------

Copy :file:`NewClub.form.yaml` into your site package and render it in an
own template of the action `perform`:

..  code-block:: html
    :caption: EXT:my_sitepackage/Resources/Private/Templates/Management/Perform.html

    <formvh:render persistenceIdentifier="EXT:my_sitepackage/Configuration/Form/NewClub.form.yaml"
                   factoryClass="JWeiland\Clubdirectory\Domain\Factory\ArrayFormFactory"
                   overrideConfiguration="{settings.overrideForm}"/>

Keep the factory class: it adds the club and the moderation token to the
form action, configures the rich text elements and marks the form with
the rendering option `formOwner: clubdirectory`. The hooks of
clubdirectory act on marked forms only, see :ref:`developer-form-hooks`. Keep the element
identifiers `categories` and `district`, which get their options from the
database, and the identifier `newClub` of the form. Check the indexes of
:typoscript:`overrideForm`, if you add or remove elements.

..  _club-form-position-element:

Geocoding of a position element
-------------------------------

Each element of type `Maps2PoiPosition` with the property `geocoding` gets
the geocoded position of an address of the previous page as initial
position:

..  code-block:: yaml
    :caption: EXT:clubdirectory/Configuration/Form/NewClub.form.yaml (excerpt)

    - type: Maps2PoiPosition
      identifier: address-1-position
      label: 'Position of address 1'
      properties:
        dbMapping:
          address: 1
          dataType: poiCollection
        geocoding:
          # The address is used, if this element has a value
          requiredElement: address-1-title
          # This element gets the error, if the address can not be geocoded
          errorElement: address-1-street
          addressElements:
            street: address-1-street
            houseNumber: address-1-house-number
            zip: address-1-zip
            city: address-1-city
      renderingOptions:
        enabled: false
      variants:
        - identifier: address-1-position-enabled
          condition: 'traverse(formValues, "address-1-title") != ""'
          renderingOptions:
            enabled: true

All elements of `addressElements` and the `requiredElement` have to be on
the same page. The address is geocoded, when this page is submitted.

..  _club-form-finisher:

Options of the SaveClubFinisher
-------------------------------

Each step of the finisher options defines a :yaml:`table`, the form
:yaml:`elements` mapped on its columns (:yaml:`mapOnDatabaseColumn`) and
further :yaml:`databaseColumnMappings`. Addresses and file references
have a :yaml:`position`. These options of an element mapping are
supported:

:yaml:`castToInteger`
    Store the value as integer, e.g. for the district.

:yaml:`normalizePlainText`
    Store plain text like the backend textarea does.

:yaml:`convertPlainTextToHtml`
    Store plain text as HTML like the backend RTE does.

:yaml:`sanitizeRichText`
    Sanitize the HTML of the rich text editor. Added automatically for rich
    text elements, see :ref:`frontend-rich-text-editor-elements`.

:yaml:`poiCollection`
    Store the position of a `Maps2PoiPosition` element in the POI record of
    the address.

The value `{__currentFrontendUser}` of a column mapping is the UID of the
logged-in frontend user.

..  _club-form-mail:

Mail of the club form
=====================

The finisher `EmailToReceiver` of the club form uses the templates
:file:`EXT:clubdirectory/Resources/Private/FormFramework/Templates/Finishers/Email/ClubModeration.html`
and :file:`ClubModeration.txt`. They are configured with the options
`templateName: ClubModeration` and `templateRootPaths` of the finisher in
:file:`NewClub.form.yaml`, not in the prototype of EXT:form. So the mails
of all other forms of the installation keep their templates.

The templates contain the links to edit and to activate the club with the
moderation token and all values of the form. Rich text elements are
rendered with the `FormatRichTextViewHelper`, see
:ref:`frontend-rich-text-editor-mail`.

To override the templates, copy :file:`ClubModeration.html` and
:file:`ClubModeration.txt` into your site package and add their folder to
`templateRootPaths` of the finisher in your copy of
:file:`NewClub.form.yaml` with a key higher than `1763337600`.

If you override the templates, keep the argument `moderationToken` of both
links. The token is available as
`{finisherVariableProvider.SaveClub.moderationToken}`, the UID of the club
as
`{finisherVariableProvider.SaveClub.insertedUids.tx_clubdirectory_domain_model_club}`.
