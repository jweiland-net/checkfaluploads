.. include:: /Includes.rst.txt

.. _developer-manual:

================
Developer manual
================

Additional columns
==================

checkfaluploads adds two columns to table sys_file:

**cruser_id**

This column will be filled automatically by `checkfaluploads` in TYPO3 BE context.

**fe_cruser_id**

This column will be automatically filled by the current logged in FE user, as long
as you use the officially TYPO3 API for FAL files. In any other cases you have to fill this column on your own.

FalUploadService
================

We deliver a little API you can use in your own Extension to check, if an uploaded file from FE context
has the user rights checkbox marked. Add checkbox to your Fluid Template:

Checkbox via Fluid
------------------

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

Checkbox via YAML EXT:form
--------------------------

.. code-block:: yaml

   -
     label: 'Example image upload'
     type: ImageUpload
     identifier: image-1
     properties:
       saveToFileMount: '1:/Extensions/[myExt]'
       allowedMimeTypes:
       - image/jpg
       - image/jpeg
       - image/png
       elementDescription: 'Select an image'
   -
     type: Checkbox
     identifier: image-1-userrights
     label: 'Upload Rights'
     properties:
       elementDescription: 'You have to confirm that %s has unrestricted rights to use the files you will upload'
       # non-official property. Needed by DynamicUploadValidatorHook. Helps to identify the checkbox
       checkboxType: uploadRights
       # non-official property. Enter the identifier of the image/file upload
       referenceUploadIdentifier: image-1
     validators:
     # Do not add NotEmpty validator. It will be added dynamically in DynamicUploadValidatorHook

ViewHelpers
===========

ImageRightsMessageViewHelper
----------------------------

This ViewHelper reads the owner property of checkfaluploads extension settings and implements the owner
into a localized string. That way you can build a text like "I give all image rights to jweiland.net".

.. code-block:: html

   <c:imageRightsMessage />

Or inline style:

.. code-block:: html

   {c:imageRightsMessage()}

If you want you can use your own translation of your own extension. In that case be sure you have added `%s` as
placeholder into your message of locallang.xml.

.. code-block:: html

   <c:imageRightsMessage languageKey="myOwnImageRightsLanguageKey" extensionName="myExtKey" />

