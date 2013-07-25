================
PHPillow read me
================

PHPillow is an object orientated wrapper for CouchDB__.

__ http://couchdb.org/

Prerequisites
=============

The following prerequisites must be fulfilled to use PHPillow:

- A running CouchDB installation from current trunk.

  We already adapted the library to the current changes for 0.9, so that it
  won't work any more with the old release in version 0.8.1, and older.

  Versions prior to 0.3-alpha worked only with older versions of CouchDB.

- PHP 5.2

  The wrapped has been tested with the latest releases from the PHP 5.2 tree.
  It might also work with earlier versions of PHP, but this is not supported.
  Some features might only be available using LSB from PHP 5.3.

  Version prior to 0.3-alpha required PHP 5.3.

State
=====

The library will stay in an alpha state at least as long as CouchDB is still
alpha. We do not know about changes in API or storage mechanisms in CouchDB, so
that we can't guarantee that the PHPillow API will stay completely stable, but
we will try to keep backwards compatibility.

License
=======

For the license we have chosen the LGPL 3 as a license. This license gives you
a lot rights on the source code with limitations we know and have chosen
intentionally.

Contributing
============

The library has originally been developed as a part of Arbit__, and the commit
mails are still going to the same mailinglist_, same for discussions. If you
want to contribute to the library please send your patches to the mailinglist_,
or ask for commit access on either the mailinglist or IRC_.

__ http://tracker.arbitracker.org/phpillow

Mailinglist
-----------

The mailinglist for discussion on PHPillow is available at
list@arbitracker.org. You may subscribe by sending an email to
list-subscribe@arbitracker.org.

IRC
---

There is an IRC channel, where you may also discuss the library, submit patches
or request commit access, which is currently located in the EuIRC__:
irc://irc.euirc.net/#arbit

__ http://euirc.net

Coding and commit standards
---------------------------

The coding and commit are the same as for Arbit and are not up to discussion.
You can find them under docs/coding_guidelines.txt. Please follow our coding
standards when contributing to the library, otherwise your contributions may be
rejected because of these plain formal issues.

Usage
=====

There is a tutorial describing the basic usage of the library, which can be
found at docs/tutorial.txt and a speed test which is a working script using the
wrapper.

You may also find example documents and views in the document/ and view/
folders, which you may use as a starting point for you implementations.

