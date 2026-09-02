/**
 * TYPO3 core's DragUploader (@typo3/backend/drag-uploader.js) has no built-in way to require a
 * confirmation from the uploading user before a file is accepted. EXT:checkfaluploads adds
 * exactly that missing feature by putting a confirmation step in front of DragUploader's native
 * upload UI, without XClassing or replacing any core class:
 * - relabels the upload trigger(s) with an ellipsis, signalling that a dialog follows
 *   (same convention as e.g. the "Select columns" button)
 * - gates the first upload trigger click / file drop per page load behind a confirmation modal,
 *   shown only when the user actually wants to upload, never permanently on the page
 * - once confirmed, replays the original click / drop so DragUploader's own upload flow runs
 *   completely unmodified afterwards
 * - injects the confirmed state as "userHasRights" into every FormData DragUploader posts to
 *   the file_process AJAX route, so EXT:checkfaluploads' UserMarkedCheckboxForRightsEventListener
 *   can validate it server-side, exactly like it already does for the classic upload form.
 *
 * Built here in JS (instead of a Fluid template override) because the File List module's "id"
 * request parameter is a folder identifier, not a page id, so TYPO3's page.tsconfig based
 * template override never applies to it - see DragUploaderRightsCheckMiddleware.
 *
 * The modal and the file_process FormData injection are shared with
 * form-engine-upload-rights-check.js - see rights-check-modal.js.
 */
import DocumentService from '@typo3/core/document-service.js';
import { openRightsModal, patchFileProcessRequests } from '@jweiland/checkfaluploads/rights-check-modal.js';

const DROPZONE_SELECTOR = '.t3js-drag-uploader';
const UPLOAD_TRIGGER_SELECTOR = '.t3js-drag-uploader-trigger';

// Confirmed once per page load - re-asking on every single upload inside the same visit
// would just train users to click it away without reading it.
let rightsConfirmed = false;

const relabelTrigger = (trigger) => {
  trigger.append(document.createTextNode('…'));
};

DocumentService.ready().then(() => {
  const dropzone = document.querySelector(DROPZONE_SELECTOR);
  if (dropzone === null) {
    return;
  }

  document.querySelectorAll(UPLOAD_TRIGGER_SELECTOR).forEach(relabelTrigger);
  patchFileProcessRequests(() => rightsConfirmed);

  // Capture phase runs before DragUploader's own (bubbling) listeners on the same elements,
  // so it can veto the click/drop until the modal is confirmed.
  document.addEventListener('click', (event) => {
    if (rightsConfirmed || !(event.target instanceof Element)) {
      return;
    }
    const trigger = event.target.closest(UPLOAD_TRIGGER_SELECTOR);
    if (trigger === null) {
      return;
    }
    event.preventDefault();
    event.stopPropagation();
    openRightsModal(() => {
      rightsConfirmed = true;
      trigger.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
    });
  }, true);

  // Scoped to DragUploader's own dropzone mask, not the whole document, so unrelated drag & drop
  // elsewhere on the page (e.g. sorting) is never intercepted.
  document.addEventListener('drop', (event) => {
    if (rightsConfirmed || !(event.target instanceof Element)) {
      return;
    }
    if (event.target.closest('.dropzone-mask') === null) {
      return;
    }
    event.preventDefault();
    event.stopPropagation();
    const { dataTransfer, target } = event;
    openRightsModal(() => {
      rightsConfirmed = true;
      target.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer }));
    });
  }, true);
});
