/**
 * Shared building blocks for extending TYPO3 core's DragUploader (@typo3/backend/drag-uploader.js)
 * with a confirmation modal, used by both drag-uploader-rights-check.js (File List module) and
 * form-engine-upload-rights-check.js (FormEngine inline file/media relation fields, e.g. the
 * "Select & upload files" button). Both surfaces need the exact same modal and the exact same
 * FormData injection into DragUploader's file_process AJAX requests, so it lives here once.
 */
import Modal from '@typo3/backend/modal.js';
import { SeverityEnum } from '@typo3/backend/enum/severity.js';

const CHECKBOX_ID = 'checkfaluploads-modal-user-has-rights';

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

export const openRightsModal = (onConfirmed) => {
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

// Patches XMLHttpRequest exactly once per page, regardless of how many callers ask for it, so
// DragUploader's own file_process POSTs carry the confirmed state as "userHasRights". isConfirmed
// is called lazily on every request instead of being captured once, so callers can flip their own
// confirmed flag at any time without re-patching.
export const patchFileProcessRequests = (isConfirmed) => {
  if (XMLHttpRequest.prototype.checkfaluploadsPatched) {
    return;
  }
  XMLHttpRequest.prototype.checkfaluploadsPatched = true;

  const originalOpen = XMLHttpRequest.prototype.open;
  const originalSend = XMLHttpRequest.prototype.send;

  XMLHttpRequest.prototype.open = function checkfaluploadsOpen(method, url, ...args) {
    this.checkfaluploadsRequestUrl = url;
    return originalOpen.call(this, method, url, ...args);
  };

  XMLHttpRequest.prototype.send = function checkfaluploadsSend(body) {
    const isFileProcessRequest = this.checkfaluploadsRequestUrl === TYPO3.settings.ajaxUrls.file_process;
    if (isFileProcessRequest && body instanceof FormData) {
      body.append('userHasRights', isConfirmed() ? '1' : '0');
    }
    return originalSend.call(this, body);
  };
};
