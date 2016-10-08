CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Spambot protects the user registration form from spammers and spambots by
verifying registration attempts against the Stop Forum Spam
(www.stopforumspam.com) online database.
It also adds some useful features to help deal with spam accounts.

This module works well for sites which require user registration
before posting is allowed (which is most forums).


RECOMMENDED MODULES
-------------------

 * User Stats (https://www.drupal.org/project/user_stats):
   Allow to use a bit more statistics of users by IP address.

 * Statistics (built-in core)
   Allow to use a bit more statistics of users by IP address.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-7
   for further information.


CONFIGURATION
-------------

 * Configure user permissions in Administration » People » Permissions:

   - Protected from spambot scans

     Users in roles with the "Protected from spambot scans" permission would not
     be scanned by cron.

 * Go to the '/admin/config/system/spambot' page and check additional settings.


MAINTAINERS
-----------

Current maintainers:
 * bengtan (bengtan) - https://www.drupal.org/u/bengtan
 * Michael Moritz (miiimooo) - https://www.drupal.org/u/miiimooo
 * Dmitry Kiselev (kala4ek) - https://www.drupal.org/u/kala4ek
