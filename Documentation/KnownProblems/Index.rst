..  include:: /Includes.rst.txt


..  _known-problems:

==============
Known Problems
==============

No user in CLI mode
===================

We only assign an FE or BE user in frontend or backend context. CLI mode
always runs as the same single, generic user, so assigning it as a file's
creator or editor would not be meaningful.

..  _known-problems-no-fe-login:

No frontend user without a login
================================

A contact or job application form usually has no frontend user login at
all, so there is no session to read a user ID from. `checkfaluploads`
still requires the rights checkbox to be confirmed before such an
upload is accepted, but `fe_cruser_id` on `sys_file` stays empty in
that case. There is no user to assign, so this is expected behavior,
not a bug.

..  _known-problems-third-party-extensions:

Third-party extensions are not covered automatically
====================================================

`checkfaluploads` only protects what TYPO3 core itself handles: the
File List module, the ElementBrowser, and inline file/media fields in
FormEngine. Extensions that implement their own upload logic on top of
Extbase or EXT:form, powermail's file upload field being one example,
are not covered just by installing `checkfaluploads`. Their developers
have to wire in :ref:`FalUploadService or CheckFalUploadValidator
<developer-manual>` by hand.
