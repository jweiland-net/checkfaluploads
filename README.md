# TYPO3 Extension `checkfaluploads`

[![Latest Stable Version](https://poser.pugx.org/jweiland/checkfaluploads/v/stable.svg)](https://packagist.org/packages/jweiland/checkfaluploads)
[![TYPO3 12.4](https://img.shields.io/badge/TYPO3-12.4-green.svg)](https://get.typo3.org/version/12)
[![License](http://poser.pugx.org/jweiland/checkfaluploads/license)](https://packagist.org/packages/jweiland/checkfaluploads)
[![Total Downloads](https://poser.pugx.org/jweiland/checkfaluploads/downloads.svg)](https://packagist.org/packages/jweiland/checkfaluploads)
[![Monthly Downloads](https://poser.pugx.org/jweiland/checkfaluploads/d/monthly)](https://packagist.org/packages/jweiland/checkfaluploads)
![Build Status](https://github.com/jweiland-net/checkfaluploads/actions/workflows/typo3_12.yml/badge.svg)

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
