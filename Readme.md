# TYPO3 Extension `checkfaluploads`

![Build Status](https://github.com/jweiland-net/checkfaluploads/workflows/CI/badge.svg)

With checkfaluploads we will add a new checkboxes to filelist module and ElementBrowser
where the user gives unrestricted rights to the owner for the uploaded files.
Further the user UID will be added to the sys_file record, so an admin can see and filter files by
user UID.

## 1 Features

* The user UID of the uploader will be added to the sys_file record

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
2) Reload Backend
