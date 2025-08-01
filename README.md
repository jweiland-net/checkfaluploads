# TYPO3 Extension `checkfaluploads`

[![Packagist][packagist-logo-stable]][extension-packagist-url]
[![Latest Stable Version][extension-build-shield]][extension-ter-url]
[![Total Downloads][extension-downloads-badge]][extension-packagist-url]
[![Monthly Downloads][extension-monthly-downloads]][extension-packagist-url]
[![TYPO3 13.4][TYPO3-shield]][TYPO3-13-url]

![Build Status](https://github.com/jweiland-net/sync_crop_areas/actions/workflows/ci.yml/badge.svg)

With `checkfaluploads` we will add a new checkbox to filelist module and FileBrowser
where the user gives unrestricted rights to the owner for the uploaded files.
Further the user UID will be added to the `sys_file` record, so an admin can see and filter files by
user UID.

## 1 Features

* The FE or BE user UID of the uploader will be added to the `sys_file` record
* Add checkbox for image rights into the FileBrowser (modal window)
* Add checkbox for image rights into the filelist module
* Integrated API to add your own image rights checkbox to extbase and/or EXT:form forms

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/checkfaluploads
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `checkfaluploads` with the extension manager module.

### 2.2 Minimal setup

1) Install the extension
2) Reload backend
<!-- MARKDOWN LINKS & IMAGES -->

[extension-build-shield]: https://poser.pugx.org/jweiland/checkfaluploads/v/stable.svg?style=for-the-badge

[extension-downloads-badge]: https://poser.pugx.org/jweiland/checkfaluploads/d/total.svg?style=for-the-badge

[extension-monthly-downloads]: https://poser.pugx.org/jweiland/checkfaluploads/d/monthly?style=for-the-badge

[extension-ter-url]: https://extensions.typo3.org/extension/sync_crop_areas/

[extension-packagist-url]: https://packagist.org/packages/jweiland/checkfaluploads/

[packagist-logo-stable]: https://img.shields.io/badge/--grey.svg?style=for-the-badge&logo=packagist&logoColor=white

[TYPO3-13-url]: https://get.typo3.org/version/13

[TYPO3-shield]: https://img.shields.io/badge/TYPO3-13.4-green.svg?style=for-the-badge&logo=typo3
