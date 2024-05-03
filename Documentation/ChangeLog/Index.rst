..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 3.0.7
=============

*   [BUGFIX] Backporting a fix from new version to old version.


Version 3.0.6
=============

*   [BUGFIX] Do not try to add user information to images in CLI mode.

Version 3.0.5
=============

*   Replace duplicate code with traits
*   Add MessageHelper to centralize message handling
*   BUGFIX: Prevent uploading files on EXT:form validation errors

Version 3.0.4
=============

*   Test for existing user record before accessing user record array

Version 3.0.3
=============

*   Add image rights checkbox in replace file form

Version 3.0.2
=============

*   Check also for image rights while replacing a file (API only)

Version 3.0.1
=============

*   Update func tests
*   Update Readme.md
*   Move labelForUserRights to ExtConf object
*   Add userHasRights checkbox to FileBrowser PopUp
*   Add further description to EventListerner in Services.yaml

Version 3.0.0
=============

*   Remove TYPO3 9 compatibility
*   Add TYPO3 11 compatibility
*   Set hook classes as public in Services.yaml
*   Prevent GU::makeInstance where possible
*   Add tests for TYPO3 11
*   Migrate from SignalSlots to EventListeners
*   Use ExtensionConfiguration as constructor argument
*   Remove clearcacheonload from ext_emconf.php

Version 2.2.1
=============

*   Update .gitattributes
*   Update .gitignore
*   Update .editorconfig
*   Update structure of documentation

Version 2.2.0
=============

*   Update documentation for upload rights checkbox
*   Add hook to add dynamic validator for upload rights checkbox

Version 2.1.1
=============

*   Do not load inline language file on AJAX requests based on pageType

Version 2.1.0
=============

*   Add new ViewHelper to generate an image user rights message for checkboxes in Fluid templates
*   Add Unit- and FunctionalTest

Version 2.0.0
=============

*   Remove TYPO3 8.7 compatibility
*   Add TYPO3 10.4 compatibility
*   Make owner in label configurable
*   Merge Hooks into one file
*   Use TYPO3 Messages for better visibility in filelist
*   Add little API to check uploads against marked user rights checkbox
