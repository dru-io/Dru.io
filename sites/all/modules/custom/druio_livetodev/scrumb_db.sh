#!/usr/bin/bash

# For using this script, you must create scrumb_db.cfg file in directory with
# this file, with this source:
# Original database name -> source.
# ORIGIN_DATABASE=
# ORIGIN_USERNAME=
# ORIGIN_PASSWORD=
# This database will be cleared and used for scrumbing data.
# STAGE_DATABASE=
# STAGE_USERNAME=
# STAGE_PASSWORD=

# Load config.
source ./scrumb_db.cfg
SETCOLOR_SUCCESS="echo -en \\033[1;32m"
SETCOLOR_FAILURE="echo -en \\033[1;31m"
SETCOLOR_NORMAL="echo -en \\033[0;39m"

# Generates random key for dump names. Protect from detecting.
RAND_ID=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 7 | head -n 1)
STAGE_DUMP_NAME="$STAGE_DATABASE-dump.sql.gz"
ORIGIN_DUMP_NAME="$RAND_ID-$ORIGIN_DATABASE-dump.sql.gz"

# db_query DB_TYPE QUERY NOTE_MESSAGE
# DB_TYUPE:
# - 1 origin
# - 2 staging
function db_query() {
  db_type=${1}
  query=${2}
  echo ${3}
  if [ "$db_type" = "1" ]; then
    local database="$ORIGIN_DATABASE"
    local username="$ORIGIN_USERNAME"
    local password="$ORIGIN_PASSWORD"
  else
    local database="$STAGE_DATABASE"
    local username="$STAGE_USERNAME"
    local password="$STAGE_PASSWORD"
  fi

  mysql $database -u $username -p$password -e "$query"
  set_status_message
}

# Sets message for last command.
function set_status_message {
  if [ $? -eq 0 ]; then
    $SETCOLOR_SUCCESS
    echo -n "$(tput hpa $(tput cols))$(tput cub 6)[OK]"
    $SETCOLOR_NORMAL
    echo
  else
    $SETCOLOR_FAILURE
    echo -n "$(tput hpa $(tput cols))$(tput cub 6)[fail]"
    $SETCOLOR_NORMAL
    echo
  fi
}

function drop_staging_db {
  local database="$STAGE_DATABASE"
  local username="$STAGE_USERNAME"
  local password="$STAGE_PASSWORD"

  TABLES=$(mysql -u $username -p$password $database -e 'show tables' | awk '{ print $1}' | grep -v '^Tables')
 
  # make sure tables exits
  if [ "$TABLES" == "" ]
  then
    echo "Note - No table found in $database database!"
    continue
  fi
   
  # let us do it
  for t in $TABLES
  do
    echo "Deleting $t table from $database database..."
    mysql -u $username -p$password $database -e "drop table $t"
  done
}

function dump_db() {
  db_type=${1}
  if [ "$db_type" = "1" ]; then
    local database="$ORIGIN_DATABASE"
    local username="$ORIGIN_USERNAME"
    local password="$ORIGIN_PASSWORD"
    local filename="$ORIGIN_DUMP_NAME"
  else
    local database="$STAGE_DATABASE"
    local username="$STAGE_USERNAME"
    local password="$STAGE_PASSWORD"
    local filename="$STAGE_DUMP_NAME"
  fi

  mysqldump -u $username -p$password $database | gzip > $filename
}

function scrubbing {
  echo "Try to clear stage db: $STAGE_DATABASE"
  drop_staging_db
  set_status_message
  
  echo "Create dump from live db: $ORIGIN_DATABASE"
  dump_db 1
  set_status_message

  echo "Extract live db and import it to staging..."
  gunzip -c $ORIGIN_DUMP_NAME | mysql -u $STAGE_USERNAME -p$STAGE_PASSWORD $STAGE_DATABASE
  set_status_message

  echo "Remove live db dump"
  rm -rf $ORIGIN_DUMP_NAME

  echo "Scrub all e-mail addresses"
  db_query "2" "UPDATE users SET mail=CONCAT('user', uid, '@example.com') WHERE uid > 0;"
  db_query "2" "UPDATE users SET init=CONCAT('user', uid, '@example.com') WHERE uid > 0;"

  echo "Set passwords for all users: password"
  db_query "2" "UPDATE users SET pass = '$S$DRCNhSmUcBocz5yUpHf4TjgBi33sExWG2o1KB0AN7vVLVo66m3IO'"

  echo "Set for all users default avatar"
  db_query "2" "UPDATE users SET picture = 0"

  echo "Disable modules for dev: hybridauth, mandrill"
  db_query "2" "DELETE FROM system WHERE name IN ('hybridauth', 'mandrill_template', 'mandrill_reports', 'mandrill');"

  echo "Clean after HybridAuth"
  db_query "2" "DELETE FROM hybridauth_session"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='hybridauth_provider_Facebook_keys_id'"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='hybridauth_provider_Facebook_keys_secret'"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='hybridauth_provider_Google_keys_id'"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='hybridauth_provider_Google_keys_secret'"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='hybridauth_provider_Twitter_keys_key'"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='hybridauth_provider_Twitter_keys_secret'"

  echo "Remove mandrill API key"
  db_query "2" "UPDATE variable SET value='s:4:\"fake\";' WHERE name='mandrill_api_key'"

  echo "Remove all caches"
  db_query "2" "TRUNCATE cache"
  db_query "2" "TRUNCATE cache_admin_menu"
  db_query "2" "TRUNCATE cache_block"
  db_query "2" "TRUNCATE cache_bootstrap"
  db_query "2" "TRUNCATE cache_eck"
  db_query "2" "TRUNCATE cache_field"
  db_query "2" "TRUNCATE cache_filter"
  db_query "2" "TRUNCATE cache_form"
  db_query "2" "TRUNCATE cache_image"
  db_query "2" "TRUNCATE cache_l10n_update"
  db_query "2" "TRUNCATE cache_libraries"
  db_query "2" "TRUNCATE cache_menu"
  db_query "2" "TRUNCATE cache_page"
  db_query "2" "TRUNCATE cache_path"
  db_query "2" "TRUNCATE cache_path_breadcrumbs"
  db_query "2" "TRUNCATE cache_rules"
  db_query "2" "TRUNCATE cache_search_api_solr"
  db_query "2" "TRUNCATE cache_token"
  db_query "2" "TRUNCATE cache_update"
  db_query "2" "TRUNCATE cache_views"
  db_query "2" "TRUNCATE cache_views_data"

  echo "Remove sessions"
  db_query "2" "TRUNCATE sessions"

  echo "Remove watchdog"
  db_query "2" "TRUNCATE watchdog"

  echo "Remove history"
  db_query "2" "TRUNCATE history"

  echo "Remove flood"
  db_query "2" "TRUNCATE flood"

  echo "Remove Solr server data"
  db_query "2" "UPDATE search_api_index SET server=NULL WHERE id='2'"
  db_query "2" "DELETE FROM search_api_server WHERE id='6'"

  echo "Dump scrubbed bd"
  dump_db 2
  set_status_message
}

echo "Loaded information from config file."
echo "ORIGIN:"
echo "Database - $ORIGIN_DATABASE"
echo "Username - $ORIGIN_USERNAME"
echo "Password - *****"
echo "STAGE:"
echo "Database - $STAGE_DATABASE"
echo "Username - $STAGE_USERNAME"
echo "Password - *****"

# Confirm.
echo -n "Is it correct? This operation cannot be undone. (n/y)? "

read confirm
case "$confirm" in
  y|Y) echo "Let's do it..."
    scrubbing
    ;;
  n|N) echo "O'kay, exit"
    exit 0
    ;;
  *) echo "Not confirmed. If you want to continue, you must enter Y."
    exit 0
    ;;
esac
