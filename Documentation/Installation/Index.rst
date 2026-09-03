..  include:: /Includes.rst.txt


..  _installation:

============
Installation
============

Composer
========

If your TYPO3 installation runs in Composer mode, execute the following
command:

..  code-block:: bash

    composer req jweiland/checkfaluploads
    vendor/bin/typo3 extension:setup --extension=checkfaluploads

If you work with DDEV, execute this command instead:

..  code-block:: bash

    ddev composer req jweiland/checkfaluploads
    ddev exec vendor/bin/typo3 extension:setup --extension=checkfaluploads

ExtensionManager
================

On non-Composer-based TYPO3 installations, you can still install
`checkfaluploads` via the Extension Manager:

..  rst-class:: bignums

1.  Log in

    Log in to your TYPO3 installation's backend as an administrator or
    system maintainer.

2.  Open the Extension Manager

    Click `Extensions` in the left-hand menu to open the Extension Manager.

3.  Update the extension list

    Choose `Get Extensions` from the drop-down at the top and click the
    `Update now` button in the upper right corner.

4.  Install `checkfaluploads`

    Use the search field to find `checkfaluploads`, select it from the
    search results, and click the cloud icon to install it.

Next step
=========

:ref:`Configure checkfaluploads <configuration>`.
