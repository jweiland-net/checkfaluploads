/**
 * Extends DragUploader's inline usage in FormEngine (TYPO3\CMS\Backend\Form\Container\
 * FilesControlContainer renders a ".t3js-drag-uploader" button for every inline file/media
 * relation field, e.g. the "Select & upload files" button on an "assets" field) with the same
 * "user has rights" confirmation modal the File List module gets - see
 * drag-uploader-rights-check.js and rights-check-modal.js, which this reuses.
 *
 * Unlike the File List trigger, this button has no separate "-trigger" class and no
 * data-dropzone-trigger attribute: DragUploader binds the click directly to the button itself
 * (TYPO3\CMS\Backend\Form\Container\FilesControlContainer::createControlUpload() has no Fluid
 * template either, only string-built markup with no PSR-14 event around it), so the guard here
 * targets ".t3js-drag-uploader" directly instead of a "-trigger" variant. A record edit form can
 * have several such buttons (one per file/media field), all created upfront in the initial markup
 * - no MutationObserver is needed since the click guard is delegated on document and therefore
 * covers all of them regardless.
 */
import DocumentService from '@typo3/core/document-service.js';
import { openRightsModal, patchFileProcessRequests } from '@jweiland/checkfaluploads/rights-check-modal.js';

const UPLOAD_TRIGGER_SELECTOR = '.t3js-drag-uploader';

// Confirmed once per page load - re-asking for every field/upload on the same record edit form
// would just train users to click it away without reading it.
let rightsConfirmed = false;

const relabelTrigger = (trigger) => {
  trigger.append(document.createTextNode('…'));
};

DocumentService.ready().then(() => {
  const triggers = document.querySelectorAll(UPLOAD_TRIGGER_SELECTOR);
  if (triggers.length === 0) {
    return;
  }

  triggers.forEach(relabelTrigger);
  patchFileProcessRequests(() => rightsConfirmed);

  // Capture phase runs before DragUploader's own (bubbling) listener on the same button, so it
  // can veto the click/drop until the modal is confirmed.
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

  // Scoped to DragUploader's own dropzone mask (created near data-dropzone-target once
  // DragUploader initializes) instead of the whole document: a record edit form can have
  // unrelated drag & drop of its own (e.g. reordering inline relations), which must stay
  // untouched.
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
