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
