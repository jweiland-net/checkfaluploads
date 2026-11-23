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

In frontend, Extbase and EXT:form forms get the same check, as soon as the
form template contains a rights checkbox for the upload field, see
:ref:`developer-manual`. Extensions with other upload logic need a developer
to wire in our `FalUploadService` API by hand, and frontend forms without a
login have no user to assign at all, see :ref:`known-problems`.

..  _screenshots:

Screenshots
===========

..  figure:: /_Images/UploadRightsDialog.png
    :alt: Confirmation dialog asking to confirm the file rights checkbox
    :class: with-shadow
    :zoom: lightbox
    :align: center

    The rights confirmation dialog shown before an inline file/media
    upload in FormEngine is accepted.
