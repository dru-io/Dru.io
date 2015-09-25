#!/bin/sh
echo "INIT DH Version"

#setup site dir here
SITEPATH="$HOME/domains/$SETTINGS_DOMAIN"

cd $SITEPATH

#sync repo to site dir. Will fix it soon. I hope.

rsync -av $GITLC_DEPLOY_DIR/ $SITEPATH/

# Download database
wget http://dru.io/sites/default/files/database.sql.gz
gunzip database.sql.gz

# I am lazy here. Just don't want to fill up settings.php manually.
/usr/bin/drush site-install standard -y --root=$SITEPATH --account-name=$SETTINGS_ACCOUNT_NAME --account-mail=$SETTINGS_ACCOUNT_MAIL --uri=http://$GITLC_DOMAINNAME --site-name="$SETTINGS_SITE_NAME" --site-mail=$SETTINGS_SITE_MAIL --db-url=mysql://$GITLC_DATABASE_USER:$GITLC_DATABASE_PASS@localhost/$GITLC_DATABASE

# Actually upload last database
mysql -u$GITLC_DATABASE_USER -p$GITLC_DATABASE_PASS $GITLC_DATABASE < database.sql

echo "Please check http://$GITLC_DOMAINNAME"
