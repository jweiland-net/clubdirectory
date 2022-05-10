.. include:: ../../Includes.txt

.. _extensionSettings:

==================
Extension Settings
==================

Some general settings for `clubdirectory` can be configured in *Admin Tools -> Settings*.

Tab: Basic
==========

userGroup
"""""""""

Default: 0

If you will allow for frontend users to create and edit their own club records you have to
assign them a frontend usergroup and add this group UID to this property.

poiCollectionPid
""""""""""""""""

Default: 0

Only valid, if you have installed EXT:maps2, too.

While creating location records we catch the address and automatically create a maps2 record
for you. Define a storage PID where we should store these records.

rootCategory
""""""""""""

Default: 0

If you have many sys_category records with huge trees in your TYPO3 project, it may make sense to
reduce the category trees in our Plugins to a parent category UID (root UID).

emailFromAddress
""""""""""""""""

Default: empty (use value from INSTALL_TOOL)

With clubdirectory you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add an email address
of the sender, if a new record was created over the frontend.

emailFromName
"""""""""""""

Default: empty (use value from INSTALL_TOOL)

With clubdirectory you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add a name
of the sender, if a new record was created over the frontend.

emailToAddress
""""""""""""""

Default: empty

With clubdirectory you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add an email address
of the receiver, if a new record was created over the frontend.

emailToName
"""""""""""

Default: empty

With clubdirectory you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add a name
of the receiver, if a new record was created over the frontend.
