..  include:: /Includes.rst.txt


..  _developer-manual:

================
Developer manual
================

Additional columns
==================

`checkfaluploads` adds two columns to the `sys_file` table:

**cruser_id**

`checkfaluploads` fills this column automatically in TYPO3 backend
context.

**fe_cruser_id**

This column is filled automatically with the current frontend user, as
long as you use TYPO3's official API for FAL files. In any other case,
you have to fill it yourself.

FalUploadService
================

`checkfaluploads` ships a small API you can use in your own extension to
check whether an uploaded file's user rights checkbox was marked in
frontend context. Add the checkbox to your Fluid template:

Checkbox via Fluid
------------------

..  code-block:: html

    <f:form.checkbox property="logo.0.rights"
                     id="logoRights"
                     class="form-check-input"
                     value="1" />

Somewhere in your Extbase extension, you should have an UploadTypeConverter.
Add the following lines:

..  code-block:: php

    if (
        ExtensionManagementUtility::isLoaded('checkfaluploads')
        && $error = GeneralUtility::makeInstance(FalUploadService::class)->checkFile($uploadedFile)
    ) {
        return $error;
    }

Checkbox via YAML EXT:form
--------------------------

..  code-block:: yaml

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
        # non-official property. Needed by DynamicUploadValidatorHook. Helps to identify the checkbox
        checkboxType: uploadRights
        # non-official property. Enter the identifier of the image/file upload
        referenceUploadIdentifier: image-1
      validators:
      # Do not add NotEmpty validator. It will be added dynamically in DynamicUploadValidatorHook

CheckFalUploadValidator
=======================

Since TYPO3 13.3, Extbase provides a native API for file upload handling via
the :php:`#[FileUpload]` attribute (see TYPO3 Changelog
`Feature-103511-IntroduceExtbaseFileUploadHandling`). This attribute does
not support custom validators though, so `checkfaluploads` ships a
ready-to-use :php:`\JWeiland\Checkfaluploads\Validation\Validator\CheckFalUploadValidator`
which must be added manually to a property's
:php:`\TYPO3\CMS\Extbase\Mvc\Controller\FileUploadConfiguration`. Like any
other file upload validator, it is only ever called by Extbase with an
:php:`\TYPO3\CMS\Core\Http\UploadedFile` instance as the validated value.

As described in the TYPO3 Changelog under "Modifying existing configuration",
this is done in the controller's :php:`initialize*Action()`:

..  code-block:: php

    public function initializeCreateAction(): void
    {
        $validator = $this->validatorResolver->createValidator(
            CheckFalUploadValidator::class,
            ['propertyPath' => 'topic.images'],
            $this->request,
        );

        $argument = $this->arguments->getArgument('topic');
        $configuration = $argument->getFileHandlingServiceConfiguration()
            ->getFileUploadConfigurationForProperty('images');
        $configuration?->addValidator($validator);
    }

How and where exactly the validator is registered is up to the consuming
extension. `initialize*Action()` is the way shown by the TYPO3 Changelog and
requires no further wiring, but nothing stops you from doing this in a PSR-14
event listener instead, if you prefer to keep your controllers slim.

The `$request` argument of :php:`ValidatorResolver::createValidator()` is
mandatory. It is the only way :php:`CheckFalUploadValidator` gets access to
the current request, which it needs to resolve the rights configuration -
and the current plugin namespace.

Where "propertyPath" points to, and why it does NOT need the plugin namespace
-----------------------------------------------------------------------------

This is the part most people get wrong on first try, because
:php:`$request->getParsedBody()` does **not** return an already-resolved
Extbase argument (as :php:`$request->getArgument('topic')` would). It returns
the raw, unfiltered HTTP POST body, i.e. exactly what PHP's :php:`$_POST`
superglobal would contain. Extbase's :php:`f:form` ViewHelper wraps every
field name in the current plugin's namespace (`tx_<extension>_<plugin>`):

..  code-block:: text

    1)  Fluid form field, rendered inside <f:form name="topic" ...>:

            <f:form.checkbox name="{object}[images][rights]" value="1"/>

    2)  Extbase prefixes every field name with the plugin namespace
        ("tx_pforum_forum"), so it ends up here, in the raw and unfiltered
        HTTP POST body == $request->getParsedBody():

            [
                'tx_pforum_forum' => [   // plugin namespace
                    'topic' => [          // Extbase argument name
                        'images' => [     // <- "propertyPath" points here,
                            'rights' => '1',    // NOT to "...images.rights"
                        ],
                    ],
                ],
            ]

`CheckFalUploadValidator` resolves the plugin namespace itself, via
:php:`\TYPO3\CMS\Extbase\Service\ExtensionService::getPluginNamespace()`
(built as :php:`'tx_' . strtolower($extensionName . '_' . $pluginName)`), and
prepends it to "propertyPath" before resolving the value:

..  code-block:: text

    3)  CheckFalUploadValidator reads it back out via:

            $propertyPath = str_replace(
                '.',
                '/',
                $pluginNamespace . '.' . $this->options['propertyPath'],
                // 'tx_pforum_forum'   .   'topic.images'
            );
            // === 'tx_pforum_forum/topic/images'

            ArrayUtility::getValueByPath($parsedBody, $propertyPath)
            === ['rights' => '1']

    4)  ['rights' => '1'] is passed as $rightsConfiguration into
        FalUploadService::checkFile(), which reads
        $rightsConfiguration[$fieldName] internally. Pointing "propertyPath"
        at "...images.rights" instead would hand over the plain string '1'
        rather than an array, and fatal with a TypeError.

Extension developers therefore only ever configure the simple, plugin-agnostic
part of the path ("topic.images", "post.images", ...) - the plugin namespace
differs per extension/plugin and would otherwise have to be duplicated,
error-prone, in every extension using this validator.

Available options
-----------------

All of the following options are passed as the second argument of
:php:`ValidatorResolver::createValidator()`. `fieldName`, `langKey` and
`extensionName` are forwarded 1:1 to the matching parameters of
:php:`FalUploadService::checkFile()`.

`propertyPath`
    Required. Path in Extbase notation (divided by "."), without the plugin
    namespace, pointing to the array that holds the `rights` key, e.g.
    `topic.images` or `post.images` (see above).

`fieldName`
    Optional, default `rights`. Key within that array that must be set and
    not empty for the upload to be allowed. Configurable on purpose: another
    extension's form might already use `rights` for something unrelated at
    that same array level, so a different name avoids a collision, e.g.
    :php:`['propertyPath' => 'topic.images', 'fieldName' => 'uploadRights']`.

`langKey`
    Optional, default `error.uploadFile.missingRights`. Language key (LLL) of
    the validation error message shown when `fieldName` is missing or empty.

`extensionName`
    Optional, default `checkfaluploads`. Extension key used to resolve the
    language file for `langKey` and the other error messages of
    :php:`FalUploadService::checkFile()`.

TYPO3's native file upload handling already strips the actual uploaded file
content from the parsed body, so in practice only the small `rights` array
shown above remains there. Since Extbase supports uploading multiple files
for a single property with :html:`<f:form.upload property="images"
multiple="1" />`, one checkbox is enough to confirm the rights for all
uploaded files at once - no per-file checkbox / index is needed. If `rights`
is set and not empty, the upload is allowed; otherwise
:php:`FalUploadService::checkFile()` returns an
:php:`\TYPO3\CMS\Extbase\Error\Error`, which :php:`CheckFalUploadValidator`
turns into a validation error via :php:`$this->addError()`, so Extbase
actually rejects the upload.

ViewHelpers
===========

ImageRightsMessageViewHelper
----------------------------

This ViewHelper reads the owner property from `checkfaluploads` extension
settings and inserts it into a localized string. That way you can build a
text like "I give all image rights to jweiland.net".

Declare the ViewHelper namespace in your template first:

..  code-block:: html

    <html xmlns:cfu="http://typo3.org/ns/JWeiland/Checkfaluploads/ViewHelpers"
          data-namespace-typo3-fluid="true">

Then use the ViewHelper:

..  code-block:: html

    <cfu:imageRightsMessage />

Or inline style:

..  code-block:: html

    {cfu:imageRightsMessage()}

You can also use your own translation from your own extension. In that
case, make sure your message in `locallang.xlf` contains the `%s`
placeholder.

..  code-block:: html

    <cfu:imageRightsMessage languageKey="myOwnImageRightsLanguageKey" extensionName="myExtKey" />
