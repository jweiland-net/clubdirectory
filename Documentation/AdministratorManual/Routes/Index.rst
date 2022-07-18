.. include:: ../../Includes.txt


======
Routes
======

With TYPO3 9 you have the possibility to configure RouteEnhancers

Example Configuration
=====================

.. code-block:: none

   routeEnhancers:
     ClubdirectoryGlossary2Plugin:
       type: Extbase
       extension: Clubdirectory
       plugin: clubdirectory
       routes:
         -
           routePath: '/first-club-page'
           _controller: 'Club::list'
         -
           routePath: '/club-page-{page}'
           _controller: 'Club::list'
           _arguments:
             page: '@widget_0/currentPage'
         -
           routePath: '/club-by-letter/{letter}'
           _controller: 'Club::search'
           _arguments:
             letter: 'search/letter'
         -
           routePath: '/show/{club_title}'
           _controller: 'Club::show'
           _arguments:
             club_title: club
       requirements:
         letter: '^(0-9|[a-z])$'
         club_title: '^[a-zA-Z0-9]+\-[0-9]+$'
       defaultController: 'Club::list'
       aspects:
         club_title:
           type: PersistedAliasMapper
           tableName: tx_clubdirectory_domain_model_club
           routeFieldName: path_segment
