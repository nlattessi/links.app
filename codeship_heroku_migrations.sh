#!/bin/sh
# This codeship custom script depends on:
# - HEROKU_API_KEY env var from Heroku
# From http://tech.yunojuno.com/custom-codeship-deployment-scripts
set -e

export HEROKU_API_KEY="${HEROKU_API_KEY}"

APP_NAME="dry-shore-86449"
APP_URL="${APP_NAME}.herokuapp.com"

# Turn on Heroku maintenance mode
heroku maintenance:on --app ${APP_NAME}

# run migrations and update static content
heroku_run "php artisan migrate --force" ${APP_NAME}

# Turn off Heroku maintenance mode
heroku maintenance:off --app ${APP_NAME}

# check if the app is up and running
check_url "${APP_URL}"
