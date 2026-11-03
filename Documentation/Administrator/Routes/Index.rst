..  include:: /Includes.rst.txt
..  _routes:

======
Routes
======

You can configure an :ref:`Extbase plugin enhancer
<t3coreapi:routing-extbase-plugin-enhancer>` for speaking URLs of the
`Clubdirectory` plugin.

The column `path_segment` of a club is generated like DataHandler does:
the title of the club, made unique with a suffix like `-1`. Since version
9.0.0 the UID of the club is not part of the path segment anymore, so do
not add a requirement for the UID to `club_title`.

..  _routes-example:

Example configuration
=====================

..  code-block:: yaml
    :caption: config/sites/main/config.yaml

    routeEnhancers:
      ClubdirectoryPlugin:
        type: Extbase
        extension: Clubdirectory
        plugin: Clubdirectory
        routes:
          -
            routePath: '/first-club-page'
            _controller: 'Club::list'
          -
            routePath: '/club-page-{page}'
            _controller: 'Club::list'
            _arguments:
              page: 'currentPage'
          -
            routePath: '/club-by-letter/{letter}'
            _controller: 'Club::list'
          -
            routePath: '/show/{club_title}'
            _controller: 'Club::show'
            _arguments:
              club_title: club
        requirements:
          page: '\d+'
          letter: '^(0-9|[a-z])$'
        defaultController: 'Club::list'
        aspects:
          club_title:
            type: PersistedAliasMapper
            tableName: tx_clubdirectory_domain_model_club
            routeFieldName: path_segment

The actions of the plugins `Club Directory Management` and
`Club Directory Search` are not cached and are usually called with form
data or with the signed links of the notification mails. A route enhancer
is not required for them.
