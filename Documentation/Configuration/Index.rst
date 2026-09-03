..  include:: /Includes.rst.txt


..  _configuration:

=============
Configuration
=============

Extension Settings
==================

Owner
-----

When you upload a file, you must confirm a checkbox that grants the
configured owner unrestricted rights to it. The owner named in the checkbox
label is a placeholder you can set in the extension settings.

Upload rights checks
--------------------

The confirmation checkbox is shown in three places: the File List module,
the ElementBrowser popup and FormEngine's inline "Select & upload files"
fields. Each of these can be switched off independently in the extension
settings, in case you do not need the confirmation everywhere.

File List and the inline fields share the same upload mechanism internally,
though. Disabling only one of the two still enforces the checkbox for both,
since TYPO3 core gives us no way to tell them apart on the server side. To
fully remove the check from either, disable both settings together.

Store uploader on file
----------------------

Alongside the checkbox, checkfaluploads stores the uploading user as a
reference on the `sys_file` record. Backend and frontend uploads have their
own setting, so you can switch either one off independently.

All settings on this page are enabled by default.
