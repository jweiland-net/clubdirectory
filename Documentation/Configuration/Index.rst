.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**

How to configure the extension. Try to make it easy to configure the extension.
Give a minimal example or a typical example.


Minimal Example
===============

- It is necessary to include static template `Clubdirectory (clubdirectory)`

We prefer to set a Storage PID with help of TypoScript Constants:

.. code-block:: none

   plugin.tx_clubdirectory.persistence {
      # Define Storage PID where club records are located
      storagePid = 4
   }

.. _configuration-typoscript:

TypoScript Setup Reference
==========================

.. container:: ts-properties

   =========================== ===================================== ======================= ====================
   Property                    Data type                             :ref:`t3tsref:stdwrap`  Default
   =========================== ===================================== ======================= ====================
   pidOfMaps2Plugin_           int                                   no                      null
   pidOfDetailPage_            Comma separated list of page UIDs     no                      null
   list_                       Array
   show_                       Array
   pageBrowser_                Array
   =========================== ===================================== ======================= ====================


Property details
================

.. only:: html

   .. contents::
      :local:
      :depth: 1


.. _pidOfMaps2Plugin:

pidOfMaps2Plugin
----------------

Example: plugin.tx_clubdirectory.settings.pidOfMaps2Plugin = 24

In our templates we link to this page, to show a detailed vaie of the location on a map.


.. _pidOfDetailPage:

pidOfDetailPage
---------------

Example: plugin.tx_clubdirectory.settings.pidOfDetailPage = 4

Here you can add one or a comma separated list of Storage PIDs where your club
records are located.


.. _show:

show
----

Default: 64c for width and height

Example: plugin.tx_clubdirectory.settings.show.image.width = 120c

If you want, you can use this setting to show one or more images
with a defined width and height in detail view.


.. _list:

list
----

Default: 100c for width and height of images and logo

Example: plugin.tx_clubdirectory.settings.list.logo.width = 150c
Example: plugin.tx_clubdirectory.settings.list.image.width = 150c

You can use this setting to show one or more images with a defined width and height in list view.


.. _pageBrowser:

pageBrowser
-----------

You can fine tuning the page browser

Example: plugin.tx_clubdirectory.settings.pageBrowser.itemsPerPage = 15
Example: plugin.tx_clubdirectory.settings.pageBrowser.insertAbove = 1
Example: plugin.tx_clubdirectory.settings.pageBrowser.insertBelow = 0
Example: plugin.tx_clubdirectory.settings.pageBrowser.maximumNumberOfLinks = 5

**itemsPerPage**

Reduce result of club records to this value for a page

**insertAbove**

Insert page browser above list of club records

**insertBelow**

Insert page browser below list of club records. I remember a bug in TYPO3 CMS. So I can not guarantee
that this option will work.

**maximumNumberOfLinks**

If you have many club records it makes sense to reduce the amount of pages in page browser to a fixed maximum
value. Instead of 1, 2, 3, 4, 5, 6, 7, 8 you will get 1, 2, 3...8, 9 if you have configured this option to 5.
