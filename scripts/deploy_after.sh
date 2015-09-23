#!/bin/sh
SITEPATH="$GITLC_DOCROOT"

cd $SITEPATH

drush cc all
