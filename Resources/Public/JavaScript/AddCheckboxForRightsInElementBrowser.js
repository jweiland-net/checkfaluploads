/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Stefan Froemken <sfroemken@jweiland.net>
 *  All rights reserved
 *
 *  Released under GNU/GPL2+ (see license file in the main directory)
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  This copyright notice MUST APPEAR in all copies of this script
 *
 ***************************************************************/
/**
 * JavaScript RequireJS module called "TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser"
 *
 */
define('TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser', ['jquery'], function($) {
	var $checkBox = $("<input />").attr({
		"type": "checkbox",
		"name": "userHasRights",
		"id": "userHasRights",
		"value": "1"
	});
	var $label = $("<label />").attr("for", "userHasRights").text(TYPO3.l10n.localize('dragUploader.iHaveTheRights'));
	var $wrapper = $("<div />").append($checkBox).append($label);
	$("div#c-override").after($wrapper);
});