/*
 * This file is part of the checkfaluploads project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * JavaScript RequireJS module called "TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser"
 */
define('TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser', ['jquery'], function ($) {
  $(document).ready(function () {
    let $checkBox = $("<input />").attr({
      "type": "checkbox", "name": "userHasRights", "id": "userHasRights", "value": "1"
    });
    let $labelAndInput = $("<label />").attr("for", "userHasRights").append($checkBox).append(
        ' ' + TYPO3.lang['dragUploader.fileRights.title'].replace('%s', TYPO3.settings.checkfaluploads.owner)
    );
    let $wrapper = $('<div class="checkbox" />').append($labelAndInput);
    $('#overwriteExistingFiles').closest('.checkbox').after($wrapper);
  });
});
