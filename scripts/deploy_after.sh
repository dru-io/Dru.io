#!/bin/sh
SITEPATH="$GITLC_DOCROOT"

cd $SITEPATH
echo "clean cache"

drush cc all

echo "Cache cleaned"
