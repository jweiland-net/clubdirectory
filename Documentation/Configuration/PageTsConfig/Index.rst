:navigation-title: Page TSconfig

..  include:: /Includes.rst.txt
..  _page-tsconfig:

===============================================
Page TSconfig for categories and frontend users
===============================================

`clubdirectory` does not add any page TSconfig. Without page TSconfig the
backend offers these records:

*   The field :guilabel:`Category` of the `Clubdirectory` plugin offers
    the categories on root level.
*   The category tree of the club records starts at root level.
*   The field :guilabel:`Assign Frontend Users` of the club records offers
    all frontend users.

..  versionchanged:: 9.0.0
    Before version 9.0.0 the extension added this page TSconfig
    automatically, based on the extension settings `rootCategory` and
    `userGroup`. Restricting these fields is the job of the integrator
    now.

..  _page-tsconfig-example:

Restrict categories and frontend users in the backend
=====================================================

Add the following page TSconfig to the :file:`page.tsconfig` of the site
set of your site package or to the field :guilabel:`Page TSconfig` in the
:guilabel:`Resources` tab of the page properties of your root page.
Replace the UIDs with the ones of your installation:

..  literalinclude:: _snippets/_page.tsconfig
    :language: typoscript
    :caption: EXT:my_sitepackage/Configuration/Sets/MySitePackage/page.tsconfig

Use the same category UID as in the extension setting
:confval:`rootCategory <ext-conf-rootcategory>`, so the backend and the
frontend offer the same categories.

See :ref:`TCEFORM <t3tsref:tceform>`,
:ref:`PAGE_TSCONFIG_ID <t3tsref:tceform-page-tsconfig-id>` and
:ref:`treeConfig <t3tsref:pagetsconfigtceformconfigtreeconfig>` in the
TSconfig reference for details.
