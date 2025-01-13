..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 5.0.0
=============

*   [TASK] TYPO3 Compatibility fix for 13 LTS
*   [TASK] Removed / Replaced deprecated functions
*   [TASK] Removed TYPO3 12 Compatibility

Version 4.0.3
=============

*   [BUGFIX] Do not try to add user information to images in CLI mode.

Version 4.0.2
=============

*   Use correct var in annotation of DynamicUploadValidatorHookTest

Version 4.0.1
=============

*   Exclude .crowdin.yml while packaging

Version 4.0.0
=============

*   Add TYPO3 12 compatibility
*   Remove TYPO3 10 compatibility
*   Remove TYPO3 11 compatibility
*   Change ext icon
*   BUGFIX: Do not upload files on validation error in EXT:form

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
