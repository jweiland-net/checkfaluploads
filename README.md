# TYPO3 Extension `checkfaluploads`

[![Packagist][packagist-logo-stable]][extension-packagist-url]
[![Latest Stable Version][extension-build-shield]][extension-ter-url]
[![Total Downloads][extension-downloads-badge]][extension-packagist-url]
[![Monthly Downloads][extension-monthly-downloads]][extension-packagist-url]
[![TYPO3 13.4][TYPO3-shield]][TYPO3-13-url]

![Build Status](https://github.com/jweiland-net/checkfaluploads/actions/workflows/ci.yml/badge.svg)

## What is checkfaluploads?

`checkfaluploads` adds a checkbox to every place in the TYPO3 backend where
editors can upload a file: the File List module, the ElementBrowser, and
inline file/media fields on records. Editors must confirm it before an
upload is accepted, and it grants the owner you configure unrestricted
rights to use that file. The extension also stores the uploading user's
UID on the `sys_file` record, so administrators can see and filter files
by who uploaded them.

## Why you need it

Editors upload images and documents to TYPO3 all the time, and not every
one of them actually holds the usage rights to what they upload. Without
an explicit confirmation step, a site owner has no record of who uploaded
a given file or whether anyone ever checked that its use was permitted,
which becomes a real problem the moment a rights holder complains.
`checkfaluploads` closes that gap: no confirmation, no upload, and a
`cruser_id` / `fe_cruser_id` trail on `sys_file` afterward, so you can
always trace a file back to whoever put it there.

## Features

* Adds an image rights checkbox to the File List module
* Adds the same checkbox to the ElementBrowser's upload form
* Adds the same checkbox to inline file/media upload buttons in FormEngine (e.g. "Select & upload files")
* Stores the FE or BE user UID of the uploader on the `sys_file` record
* Ships an API to add your own image rights checkbox to Extbase and/or EXT:form forms

## Installation

### Using Composer

The recommended way to install the extension is via Composer. Run this
inside your Composer based TYPO3 project:

```
composer require jweiland/checkfaluploads
vendor/bin/typo3 extension:setup --extension=checkfaluploads
```

### Using the TYPO3 Extension Repository (TER)

On non-Composer-based installations, download and install
`checkfaluploads` via the Extension Manager module instead.

## Configuration

After installing, open **Admin Tools > Settings > Extension Configuration
> checkfaluploads** and set the **Owner** field to whoever should receive
the usage rights (e.g. your company name). Until you do, the checkbox
label falls back to a placeholder like `[Missing owner in ext settings of
checkfaluploads]` instead of a real name, so this step is not optional.

## Support

Free Support is available via [GitHub Issue Tracker](https://github.com/jweiland-net/checkfaluploads/issues).

For commercial support, please contact us at [support@jweiland.net](support@jweiland.net).

<!-- MARKDOWN LINKS & IMAGES -->

[extension-build-shield]: https://poser.pugx.org/jweiland/checkfaluploads/v/stable.svg?style=for-the-badge

[extension-downloads-badge]: https://poser.pugx.org/jweiland/checkfaluploads/d/total.svg?style=for-the-badge

[extension-monthly-downloads]: https://poser.pugx.org/jweiland/checkfaluploads/d/monthly?style=for-the-badge

[extension-ter-url]: https://extensions.typo3.org/extension/checkfaluploads/

[extension-packagist-url]: https://packagist.org/packages/jweiland/checkfaluploads/

[packagist-logo-stable]: https://img.shields.io/badge/--grey.svg?style=for-the-badge&logo=packagist&logoColor=white

[TYPO3-13-url]: https://get.typo3.org/version/13

[TYPO3-shield]: https://img.shields.io/badge/TYPO3-13.4-green.svg?style=for-the-badge&logo=typo3
