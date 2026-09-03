..  include:: /Includes.rst.txt


..  _introduction:

============
Introduction
============


..  _what-it-does:

What does it do?
================

`checkfaluploads` adds a checkbox to the File List module and the
ElementBrowser that editors must confirm before a file upload is accepted,
granting the configured owner unrestricted rights to that file. It also
stores the uploading editor's user UID on the `sys_file` record, so
administrators can trace back who uploaded a given file.

This only covers what TYPO3 core itself handles. Extensions with their own
upload logic need a developer to wire in our `FalUploadService` /
`CheckFalUploadValidator` API by hand, and frontend forms without a login
have no user to assign at all, see :ref:`known-problems`.
