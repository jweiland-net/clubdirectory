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
`Management`, `Map` or `Search`.

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
    The controller arguments (:php:`\TYPO3\CMS\Extbase\Mvc\Controller\Arguments`).

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
        -   `Management`: create, edit, update, activate.
            `Map`: new, create. Allows the access to the club for owners,
            backend administrators and valid moderation tokens only. Stops
            with the 403 error handling of the site otherwise. `activate`
            gets the club UID as scalar argument `clubUid`. See
            :ref:`moderation-access`.

    *   -   `clubdirectory/register-hidden-club`
        -   `PreProcessControllerActionEvent`
        -   `Management`: edit, update, confirmActivation. `Map`: new,
            create. Registers the hidden club for the property mapper.
            Executed after `clubdirectory/restrictAccess`.

    *   -   `clubdirectory/apply-upload-folder`
        -   `PreProcessControllerActionEvent`
        -   `Management`: create, update. Sets the upload folder of
            logo and images from `settings.new.uploadFolder`.

    *   -   `clubdirectory/check-fal-uploads`
        -   `PreProcessControllerActionEvent`
        -   `Management`: create, update. Adds the validator of
            checkfaluploads to logo and images, if checkfaluploads is
            installed. See :ref:`upgrade-9-0-0-checkfaluploads`.

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
