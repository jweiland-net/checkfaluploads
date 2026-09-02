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
 */
import DocumentService from '@typo3/core/document-service.js';
import Modal from '@typo3/backend/modal.js';
import { SeverityEnum } from '@typo3/backend/enum/severity.js';

const DROPZONE_SELECTOR = '.t3js-drag-uploader';
const UPLOAD_TRIGGER_SELECTOR = '.t3js-drag-uploader-trigger';
const CHECKBOX_ID = 'checkfaluploads-modal-user-has-rights';

// Confirmed once per page load - re-asking on every single upload inside the same visit
// would just train users to click it away without reading it.
let rightsConfirmed = false;

const relabelTrigger = (trigger) => {
  trigger.append(document.createTextNode('…'));
};

const buildModalContent = () => {
  const wrapper = document.createElement('div');
  wrapper.classList.add('form-check');
  wrapper.innerHTML = `
    <input class="form-check-input" type="checkbox" id="${CHECKBOX_ID}">
    <label class="form-check-label" for="${CHECKBOX_ID}"></label>
  `;
  wrapper.querySelector('label').textContent = TYPO3.lang['checkfaluploads.dragUploader.fileRights.title'] || '';
  return wrapper;
};

const openRightsModal = (onConfirmed) => {
  const content = buildModalContent();
  const checkbox = content.querySelector(`#${CHECKBOX_ID}`);

  Modal.advanced({
    title: TYPO3.lang['checkfaluploads.dragUploader.modalTitle'],
    content,
    severity: SeverityEnum.notice,
    buttons: [
      {
        text: TYPO3.lang['checkfaluploads.dragUploader.modalCancel'],
        btnClass: 'btn-default',
        name: 'cancel',
        trigger: (event, modalInstance) => modalInstance.hideModal(),
      },
      {
        text: TYPO3.lang['checkfaluploads.dragUploader.modalConfirm'],
        btnClass: 'btn-primary',
        name: 'confirm',
        trigger: (event, modalInstance) => {
          if (!checkbox.checked) {
            return;
          }
          rightsConfirmed = true;
          modalInstance.hideModal();
          onConfirmed();
        },
      },
    ],
    callback: (currentModal) => {
      const confirmButton = currentModal.querySelector('button[name="confirm"]');
      confirmButton.disabled = true;
      checkbox.addEventListener('change', () => {
        confirmButton.disabled = !checkbox.checked;
      });
    },
  });
};

DocumentService.ready().then(() => {
  const dropzone = document.querySelector(DROPZONE_SELECTOR);
  if (dropzone === null) {
    return;
  }

  document.querySelectorAll(UPLOAD_TRIGGER_SELECTOR).forEach(relabelTrigger);

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
      trigger.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
    });
  }, true);

  document.addEventListener('drop', (event) => {
    if (rightsConfirmed) {
      return;
    }
    event.preventDefault();
    event.stopPropagation();
    const { dataTransfer, target } = event;
    openRightsModal(() => {
      target.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer }));
    });
  }, true);

  const originalOpen = XMLHttpRequest.prototype.open;
  const originalSend = XMLHttpRequest.prototype.send;

  XMLHttpRequest.prototype.open = function checkfaluploadsOpen(method, url, ...args) {
    this.checkfaluploadsRequestUrl = url;
    return originalOpen.call(this, method, url, ...args);
  };

  XMLHttpRequest.prototype.send = function checkfaluploadsSend(body) {
    const isFileProcessRequest = this.checkfaluploadsRequestUrl === TYPO3.settings.ajaxUrls.file_process;
    if (isFileProcessRequest && body instanceof FormData) {
      body.append('userHasRights', rightsConfirmed ? '1' : '0');
    }
    return originalSend.call(this, body);
  };
});
