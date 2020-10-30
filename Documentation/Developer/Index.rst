.. include:: ../Includes.txt

.. _developer-manual:

================
Developer manual
================

Additional columns
==================

checkfaluploads adds two columns to table sys_file:

**cruser_id**: This column will be filled automatically by checkfaluploads in TYPO3 BE context.
**fe_cruser_id**: This column can be filled by YOU in TYPO3 FE context

FalUploadService
================

We deliver a little API you can use in your own Extension to check, if an uploaded file from FE context
has the user rights checkbox marked. Add checkbox to your Fluid Template:

.. code-block:: html

   <f:form.checkbox property="logo.0.rights"
                    id="logoRights"
                    class="form-check-input"
                    value="1" />

Somewhere in your extbase extension you should have an UploadTypeConverter. Add following lines:

.. code-block:: php

   if (
       ExtensionManagementUtility::isLoaded('checkfaluploads')
       && $error = GeneralUtility::makeInstance(FalUploadService::class)->checkFile($uploadedFile)
   ) {
       return $error;
   }
