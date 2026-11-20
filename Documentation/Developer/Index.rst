..  include:: /Includes.rst.txt
..  _developer:

================
Developer manual
================

This chapter describes the PSR-14 events of `clubdirectory` and the event
listeners shipped with the extension.

..  _developer-events:

Events
======

All controller events implement
:php:`\JWeiland\Clubdirectory\Event\ControllerActionEventInterface` with the
methods `getRequest()`, `getControllerName()`, `getActionName()` and
`getSettings()`. The controller name is the short name like `Club`,
`Management` or `Search`.

The club form based on EXT:form does not dispatch these events, use the
hooks of EXT:form instead. The tags of the rich text editor of the form can
be modified with the PSR-14 event `ModifyRichTextSanitizerConfigurationEvent`,
see :ref:`frontend-rich-text-editor-sanitizer-event`.

..  _developer-events-pre-process:

PreProcessControllerActionEvent
-------------------------------

:php:`\JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent` is
dispatched before the property mapping of an action starts, mostly within
the `initialize*Action()` methods. Listeners can replace the request and the
controller arguments, e.g. to modify the property mapping or the file
upload configuration.

Additional methods:

`getArguments()` / `setArguments(Arguments $arguments)`
    The controller arguments
    (:php:`\TYPO3\CMS\Extbase\Mvc\Controller\Arguments`).

`setRequest(Request $request)`
    Replaces the Extbase request.

..  versionchanged:: 9.0.0

    The event replaces the removed `InitializeControllerActionEvent` and
    has the same methods now. The methods `getController()`,
    `getClubController()`, `getMapController()` and `getClub()` have been
    removed. Read the club from the request instead.

..  _developer-events-post-process-fluid-variables:

PostProcessFluidVariablesEvent
------------------------------

:php:`\JWeiland\Clubdirectory\Event\PostProcessFluidVariablesEvent` is
dispatched before the variables are assigned to the view. Use
`getFluidVariables()` to read and `addFluidVariable(string $key, $value)`
to add or override a variable.

..  literalinclude:: _snippets/_AddClubListVariableEventListener.php
    :language: php
    :caption: EXT:my_site_package/Classes/EventListener/AddClubListVariableEventListener.php

..  _developer-events-removed:

Removed events
--------------

..  versionchanged:: 9.0.0

    *   `InitializeControllerActionEvent` has been removed. Use
        `PreProcessControllerActionEvent`.
    *   `PostCheckFileReferenceEvent` has been removed without replacement.
        Logo and images use the file upload handling of Extbase. Modify the
        file upload configuration with `PreProcessControllerActionEvent`
        instead.

..  _developer-event-listeners:

Event listeners of clubdirectory
================================

Use these identifiers, if you want to register your own listener `before`
or `after` one of them.

..  list-table::
    :header-rows: 1

    *   -   Identifier
        -   Event
        -   Controller actions and purpose

    *   -   `clubdirectory/restrictAccess`
        -   `PreProcessControllerActionEvent`
        -   `Management`: perform. Allows the access to the club form for
            members of the usergroup, owners, backend administrators and
            valid moderation tokens only. Stops with the 403 error handling
            of the site otherwise. The actions `confirmActivation` and
            `activate` check the moderation token on their own. See
            :ref:`moderation-access`.

    *   -   `clubdirectory/register-hidden-club`
        -   `PreProcessControllerActionEvent`
        -   `Management`: confirmActivation. Registers the hidden club for
            the property mapper.
            Executed after `clubdirectory/restrictAccess`.

    *   -   `clubdirectory/allow-search-parameter`
        -   `PreProcessControllerActionEvent`
        -   `Search`: listSearchResults. Allows the search arguments
            also for GET requests, e.g. of the page browser.

    *   -   `JWeiland\Clubdirectory\EventListener\AddPaginatorEventListener`
        -   `PostProcessFluidVariablesEvent`
        -   `Club`: list. `Search`: listSearchResults. Adds the
            paginator and the pagination.

    *   -   `JWeiland\Clubdirectory\EventListener\AddGlossaryEventListener`
        -   `PostProcessFluidVariablesEvent`
        -   `Club`: list. Adds the glossary of glossary2.

    *   -   `JWeiland\Clubdirectory\EventListener\SetTitleOfPOIToClubTitleEventListener`
        -   `\JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent`
        -   Sets the club title as title of the POI records of clubs
            saved in the backend.

..  versionchanged:: 9.0.0

    The event listeners `AssignMediaTypeConverterEventListener` and
    `DeleteUploadedFilesEventListener` have been removed. The listeners
    `clubdirectory/restrictAccess` and `clubdirectory/allow-search-parameter`
    listen to `PreProcessControllerActionEvent` now.

..  versionchanged:: 10.0.0

    The event listeners `clubdirectory/apply-upload-folder` and
    `clubdirectory/check-fal-uploads` have been removed together with the
    Extbase form, see :ref:`upgrade-10-0-0`.

..  _developer-form-hooks:

Hooks of the club form
======================

`clubdirectory` registers these hooks of EXT:form for its club form, see
:ref:`club-form`. EXT:form calls them for all forms of the installation,
but they act on forms built by the `ArrayFormFactory` of clubdirectory
only. The factory marks these forms with the rendering option
`formOwner: clubdirectory` (:php:`\JWeiland\Clubdirectory\Form\FormOwner`).
Other extensions like events2 and yellowpages2 use the same rendering
option with their own value, so each extension adds its rich text editor,
prefill and geocoding to its own forms only.

If you copy :file:`NewClub.form.yaml`, render it with the action `perform`
of the Management plugin and the factory class of clubdirectory, see
:ref:`club-form-own-definition`. Rendered with the form plugin of EXT:form,
the form gets no options, prefill, geocoding and rich text editor.

`initializeFormElement`: :php:`\JWeiland\Clubdirectory\Hook\Form\PrefillOptionsHook`
    Adds the options of the elements `categories` and `district`.

`afterBuildingFinished`: :php:`\JWeiland\Clubdirectory\Hook\Form\PrefillForEditUsageHook`
    Prefills the form with the club to edit.

`afterSubmit`: :php:`\JWeiland\Clubdirectory\Hook\Form\GeocodeAddressesHook`
    Geocodes the addresses of a submitted page for the elements of type
    `Maps2PoiPosition`, see :ref:`club-form-position-element`.

`beforeRendering`: :php:`\JWeiland\Clubdirectory\Hook\Form\RichTextEditorHook`
    Adds the CKEditor to rich text elements, see
    :ref:`frontend-rich-text-editor-elements`.
