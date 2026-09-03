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
