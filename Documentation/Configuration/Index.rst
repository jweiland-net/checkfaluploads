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

The confirmation checkbox is shown in two places, each with its own
extension setting: drag & drop uploads (the File List module and
FormEngine's inline "Select & upload files" fields, which both use TYPO3
core's DragUploader) and uploads through the ElementBrowser popup.

File List and the inline fields cannot be switched off individually: they
both submit through the very same upload mechanism internally, and TYPO3
core gives us no way to tell them apart on the server side. Disabling the
drag & drop setting therefore turns off the check for both at once.

Store uploader on file
----------------------

Alongside the checkbox, checkfaluploads stores the uploading user as a
reference on the `sys_file` record. Backend and frontend uploads have their
own setting, so you can switch either one off independently.

All settings on this page are enabled by default.
