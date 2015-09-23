#!/bin/sh
echo "INIT DH Version"

SITEPATH="$HOME/domains/$SETTINGS_DOMAIN"

cd $SITEPATH

echo "sync to docroot"
rsync -av $GITLC_DEPLOY_DIR/ $SITEPATH/

wget http://dru.io/sites/default/files/database.sql.gz
gunzip database.sql.gz

/usr/bin/drush site-install standard -y --root=$SITEPATH --account-name=$SETTINGS_ACCOUNT_NAME --account-mail=$SETTINGS_ACCOUNT_MAIL --uri=http://$GITLC_DOMAINNAME --site-name="$SETTINGS_SITE_NAME" --site-mail=$SETTINGS_SITE_MAIL --db-url=mysql://$GITLC_DATABASE_USER:$GITLC_DATABASE_PASS@localhost/$GITLC_DATABASE
mysql -u$GITLC_DATABASE_USER -p$GITLC_DATABASE_PASS $GITLC_DATABASE < database.sql

echo "Please check http://$GITLC_DOMAINNAME"
