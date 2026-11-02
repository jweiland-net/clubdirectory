..  include:: /Includes.rst.txt


..  _extensionSettings:

==================
Extension Settings
==================

Some general settings for `clubdirectory` can be configured
in :guilabel:`Admin Tools > Settings > Extension Configuration`.

..  _extensionSettings-tab-basic:

Tab: Basic
==========

..  confval:: userGroup
    :name: ext-conf-usergroup
    :type: int
    :default: 0

    If you will allow for frontend users to create and edit their own club
    records you have to assign them a frontend usergroup and add this group
    UID to this property. The links to create a new club and to list the
    own clubs are shown to members of this usergroup only.

..  confval:: poiCollectionPid
    :name: ext-conf-poicollectionpid
    :type: int
    :default: 0

    While saving a club in frontend we geocode its addresses and create a
    maps2 POI record for each address automatically. Define the UID of a
    storage folder where these records should be stored.

    How addresses are geocoded and which API keys are required is
    described in the documentation of
    :ref:`maps2 <maps2:extensionsettings>`.

..  confval:: rootCategory
    :name: ext-conf-rootcategory
    :type: int
    :default: 0

    If you have many sys_category records with huge trees in your TYPO3
    project, it may make sense to reduce the category trees in our plugins
    to a parent category UID (root UID).

..  confval:: emailFromAddress
    :name: ext-conf-emailfromaddress
    :type: string
    :default: empty (use `defaultMailFromAddress` of the TYPO3 mail settings)

    Clubs created or changed in frontend are hidden until the administrator
    activates them. The administrator is informed by mail. Add the email
    address of the sender of this mail.

    If this value and `$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']`
    are empty, saving a club in frontend ends with an exception.

..  confval:: emailFromName
    :name: ext-conf-emailfromname
    :type: string
    :default: empty (use `defaultMailFromName` of the TYPO3 mail settings)

    Name of the sender of the mail to the administrator.

    If this value and `$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']`
    are empty, saving a club in frontend ends with an exception.

..  confval:: emailToAddress
    :name: ext-conf-emailtoaddress
    :type: string
    :default: empty
    :required: true

    Email address of the administrator, who receives the mails about
    created and changed clubs. These mails contain the links to check and
    activate the club, see :ref:`moderation`.

    There is no fallback for this value. If it is empty, saving a club in
    frontend ends with an exception.

..  confval:: emailToName
    :name: ext-conf-emailtoname
    :type: string
    :default: empty

    Name of the administrator, who receives the mails about created and
    changed clubs.
