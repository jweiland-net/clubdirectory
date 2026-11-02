..  include:: /Includes.rst.txt


..  _moderation:

==============================
Club management and moderation
==============================

Frontend users can create and edit their own clubs with the plugin
`Club Directory Management`. Each created or changed club is hidden until
the administrator has checked and activated it.

..  _moderation-requirements:

Requirements
============

*   A page with the plugin `Club Directory Management`, configured in
    :confval:`clubdirectory.pidOfManagementPage
    <site-setting-pidofmanagementpage>`.
*   A page with the plugin `Clubdirectory` for the list of clubs,
    configured in :confval:`clubdirectory.pidOfListPage
    <site-setting-pidoflistpage>`.
*   Optional: an access restriction of the management page to the
    frontend usergroup of the club owners, see
    :ref:`moderation-access`.
*   The receiver and sender of the notification mails in
    :confval:`emailToAddress <ext-conf-emailtoaddress>` and
    :confval:`emailFromAddress <ext-conf-emailfromaddress>`.
*   A storage folder for the POI records in
    :confval:`poiCollectionPid <ext-conf-poicollectionpid>` and a working
    geocoding configuration of maps2. See
    :ref:`maps2 <maps2:extensionsettings>`.

..  _moderation-flow:

Moderation flow
===============

#.  A logged-in frontend user fills out the club form. The form requires at
    least one address. For each chosen address type street, ZIP and city
    are required.

#.  `clubdirectory` geocodes each address with maps2. If an address can
    not be found, the user is asked to check the address. If the map
    service rejects the request (e.g. an API key restricted to other IP
    addresses) or is not available, the user gets a hint to inform the
    administrator. The technical message of the map service is written to
    the TYPO3 log only.

#.  The club is saved as hidden record and assigned to the frontend user.
    In the next step the user can correct the position of the map marker.

#.  After confirming the map, the administrator gets a mail with the data
    of the club and two links: one to edit and one to activate the club.
    The user is redirected to the list page, which shows a message about
    the pending moderation.

#.  The administrator opens the edit link to check and correct the club,
    or the activate link to publish it. After the activation the
    administrator is redirected to the list page, which shows a
    confirmation message.

The list page is not cached, while it shows such a message. The
`Clubdirectory` plugin tags its cached list pages with
`tx_clubdirectory_domain_model_club` and its detail pages with
`tx_clubdirectory_domain_model_club_<uid>`. Saving a club in frontend or
backend flushes these tags, so new, activated and changed clubs are shown
right away. A `TCEMAIN.clearCacheCmd` for the list and detail pages is
not required anymore.

Each time an owner changes a club, the club is hidden again and the
administrator gets a new mail with new links. The map position of an
existing club is changed in the same edit form.

..  _moderation-mail-links:

Mail links and moderation token
===============================

The edit and activate links of the mails point to the Management plugin
on the page configured in :confval:`clubdirectory.pidOfManagementPage
<site-setting-pidofmanagementpage>`. They contain the argument
`moderationToken`, an HMAC of the club UID and the `tstamp` of the club
record:

*   A valid token grants access to the club without a frontend login, as
    the administrator is usually not logged in as the owner. The edit form
    submits the token as hidden field, so the administrator can save the
    form, too.

*   A club can be activated in frontend with a valid token only. Neither
    the owner nor a logged-in backend administrator can activate a club
    without it.

*   As soon as the club is changed again, in frontend or backend, the
    `tstamp` changes and the links of all older mails stop working. Only
    the links of the latest mail are valid.

If a link does not work anymore, use the links of the latest mail or
activate the club in the backend by removing the hidden flag of the
record.

..  attention::

    The page of the Management plugin must be accessible for the
    administrator. If you restrict this page to the usergroup of the club
    owners, the administrator has to log in with a member of this group
    before opening the links of the mail.

If you override the mail templates :file:`Templates/Management/Update.html`
and :file:`Templates/Map/Create.html`, keep the argument `moderationToken`
in both links and the hidden field `moderationToken` in
:file:`Templates/Management/Edit.html`.

..  _moderation-access:

Access control and error pages
==============================

*   The links to create a new club and to list the own clubs are shown to
    every logged-in frontend user (partial
    :file:`Partials/Club/InsertEditLinks.html`).
*   Every logged-in frontend user with access to the management page can
    create a club. To allow this for a specific usergroup only, restrict
    the access of the management page to this group. Override the partial
    :file:`Partials/Club/InsertEditLinks.html` in your site package, if the
    links should be shown to this group only, e.g. with
    :html:`<f:security.ifHasRole role="...">`.
*   Only frontend users assigned to the club (field
    :guilabel:`Assign Frontend Users` of the club record) and backend
    administrators are allowed to edit and update a club.
*   Addresses and POI records submitted with the form must belong to the
    edited club.
*   All other requests stop with the 403 error handling of the site.
*   The detail view of a hidden, deleted or unknown club stops with the
    404 error handling of the site.

Configure the :ref:`error handling <t3coreapi:sitehandling-errorhandling>`
of your site for the status codes 403 and 404 to show a helpful page.
