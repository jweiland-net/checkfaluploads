/**
 * TYPO3 core's classic upload form (TYPO3\CMS\Backend\View\FolderUtilityRenderer::uploadForm(),
 * rendered inside the "file" element browser popup - e.g. FormEngine's "Select & upload files" /
 * "Add media file" buttons, or the RTE link wizard) has no built-in way to require a confirmation
 * from the uploading user before a file is accepted, same gap DragUploader has - see
 * drag-uploader-rights-check.js. This module adds it without XClassing FolderUtilityRenderer: it
 * injects a real "userHasRights" checkbox into the form, so its value is simply submitted along
 * with the rest of the classic multipart POST - EXT:checkfaluploads' own
 * UserMarkedCheckboxForRightsEventListener validates it server-side exactly like everywhere else.
 *
 * The popup swaps its whole body via AJAX when navigating into a different folder (see
 * @typo3/filelist/browse-files.js loadContent()), replacing the form with a fresh one. A
 * MutationObserver re-runs the injection whenever that happens.
 */
import DocumentService from '@typo3/core/document-service.js';
import Notification from '@typo3/backend/notification.js';

const FILE_INPUT_SELECTOR = 'input[type="file"][name^="upload_"]';
const CHECKBOX_ID = 'checkfaluploads-element-browser-user-has-rights';

const addCheckboxToForm = (fileInput) => {
  const form = fileInput.closest('form');
  if (form === null || form.querySelector(`#${CHECKBOX_ID}`) !== null) {
    return;
  }

  const wrapper = document.createElement('div');
  wrapper.classList.add('col-12');
  wrapper.innerHTML = `
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="userHasRights" value="1" id="${CHECKBOX_ID}">
      <label class="form-check-label" for="${CHECKBOX_ID}"></label>
    </div>
  `;
  wrapper.querySelector('label').textContent = TYPO3.lang['checkfaluploads.dragUploader.fileRights.title'] || '';

  const fileInputRow = fileInput.closest('.col-12') || fileInput.closest('.row') || form;
  fileInputRow.before(wrapper);

  form.addEventListener('submit', (event) => {
    const checkbox = form.querySelector(`#${CHECKBOX_ID}`);
    if (checkbox !== null && !checkbox.checked) {
      event.preventDefault();
      Notification.warning(
        TYPO3.lang['checkfaluploads.missingRights.title'],
        TYPO3.lang['checkfaluploads.missingRights.message'],
      );
    }
  });
};

const scanForUploadForms = () => {
  document.querySelectorAll(FILE_INPUT_SELECTOR).forEach(addCheckboxToForm);
};

DocumentService.ready().then(() => {
  scanForUploadForms();

  const container = document.querySelector('.element-browser-body');
  if (container === null) {
    return;
  }
  new MutationObserver(scanForUploadForms).observe(container, { childList: true, subtree: true });
});
