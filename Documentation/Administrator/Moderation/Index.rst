..  include:: /Includes.rst.txt
..  _moderation:

==============================
Club management and moderation
==============================

Frontend users can create and edit their own clubs with the plugin
`Club Directory Management` and its form based on EXT:form, see
:ref:`club-form`. Each created or changed club is hidden until the
administrator has checked and activated it.

..  _moderation-requirements:

Requirements
============

*   A page with the plugin `Club Directory Management`, configured in
    :confval:`clubdirectory.pidOfManagementPage
    <site-setting-pidofmanagementpage>`.
*   A page with the plugin `Clubdirectory` for the list of clubs,
    configured in :confval:`clubdirectory.pidOfListPage
    <site-setting-pidoflistpage>`.
*   The frontend usergroup of the club owners in
    :confval:`clubdirectory.userGroup <site-setting-usergroup>` and an
    access restriction of the management page to this group, see
    :ref:`moderation-access`.
*   The receiver and sender of the notification mails in
    :confval:`emailToAddress <ext-conf-emailtoaddress>` and
    :confval:`emailFromAddress <ext-conf-emailfromaddress>`.
*   A storage folder for the POI records in
    :confval:`poiCollectionPid <ext-conf-poicollectionpid>` and a working
    geocoding configuration of maps2. See
    :ref:`maps2 <maps2:extensionsettings>`.
*   EXT:form and maps2 12.3 or newer, see :ref:`club-form-requirements`.

..  _moderation-flow:

Moderation flow
===============

#.  A logged-in frontend user fills out the first page of the club form.
    The form requires at least one address. For each chosen address type
    street, ZIP and city are required. The district is optional.

#.  `clubdirectory` geocodes each address with maps2, when the first page
    is submitted. If an address can not be found, the user is asked to
    check the address. If the map service rejects the request (e.g. an API
    key restricted to other IP addresses) or is not available, the user
    gets a hint to inform the administrator. The technical message of the
    map service is written to the TYPO3 log only. The user stays on the
    first page, until all addresses were found.

#.  On the second page the user can correct the position of each address
    by dragging the marker on the map.

#.  After submitting the second page, the club is saved and assigned to
    the frontend user. The administrator gets a mail with the data of the
    club and two links: one to edit and one to activate the club. The club
    is hidden afterwards. The user is redirected to the management page,
    which shows a message about the pending moderation.

#.  The administrator opens the edit link to check and correct the club,
    or the activate link to publish it. The activate link shows a summary
    of the club with a button. The club is activated after submitting this
    form only, so mail security scanners opening the link can not activate
    it. After the activation the administrator is redirected to the list
    page, which shows a confirmation message. If the link is invalid or
    outdated, the list page shows an error message. If the club is active
    already, it shows an information.

The list page is not cached, while it shows such a message. The
`Clubdirectory` plugin tags its cached list pages with
`tx_clubdirectory_domain_model_club` and its detail pages with
`tx_clubdirectory_domain_model_club_<uid>`. Saving a club in frontend or
backend flushes these tags, so new, activated and changed clubs are shown
right away. A `TCEMAIN.clearCacheCmd` for the list and detail pages is
not required anymore.

Each time an owner changes a club, the club is hidden again and the
administrator gets a new mail with new links. The map position of an
existing club is changed on the second page of the same form.

..  _moderation-mail-links:

Mail links and moderation token
===============================

The edit and activate links of the mails point to the Management plugin
on the page configured in :confval:`clubdirectory.pidOfManagementPage
<site-setting-pidofmanagementpage>`. They contain the argument
`moderationToken`, an HMAC of the club UID and the `tstamp` of the club
record:

*   A valid token grants access to the club without a frontend login, as
    the administrator is usually not logged in as the owner. The club form
    keeps the token in the URI of the form action, so the administrator can
    save the form, too.

*   A club can be activated in frontend with a valid token only. Neither
    the owner nor a logged-in backend administrator can activate a club
    without it. The actions `confirmActivation` and `activate` check the
    token on their own. Without a valid token they redirect to the list
    page, which shows an error message.

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

If you override the mail templates of the club form, keep the argument
`moderationToken` in both links, see :ref:`club-form-mail`. If you override
:file:`Templates/Management/ConfirmActivation.html`, keep its hidden field
`moderationToken`.

..  _moderation-access:

Access control and error pages
==============================

*   The links to create a new club and to list the own clubs are shown to
    members of the usergroup configured in
    :confval:`clubdirectory.userGroup <site-setting-usergroup>` only
    (:html:`<f:security.ifHasRole>` in the partial
    :file:`Partials/Club/InsertEditLinks.html`). The list of the own clubs
    shows a message to all other users, that they have no access.
*   The club form (action `perform`) requires a logged-in member of this
    usergroup or a valid moderation token. Access is denied before the form
    is rendered. The finisher of the form checks the access again before
    anything is stored. Additionally restrict the access of the management
    page to the same usergroup in the page properties (field
    :guilabel:`Usergroup Access Rights`).
*   The activation (actions `confirmActivation` and `activate`) requires a
    valid moderation token. Without it, the administrator is redirected to
    the list page with an error message, see :ref:`moderation-mail-links`.
*   Members of this usergroup are allowed to edit and update the clubs
    assigned to them only (field :guilabel:`Assign Frontend Users` of the
    club record). Backend administrators are allowed to edit and update
    all clubs.
*   The club to edit is always taken from the query parameter `club` of
    the form action. Addresses and POI records are taken from this club.
    Values of select boxes and checkboxes are stored only, if they are an
    option of the form.
*   All other requests to the club form stop with the 403 error handling
    of the site.
*   The detail view of a hidden, deleted or unknown club stops with the
    404 error handling of the site.

Configure the :ref:`error handling <t3coreapi:sitehandling-errorhandling>`
of your site for the status codes 403 and 404 to show a helpful page.
