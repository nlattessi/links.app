#!/bin/sh
# This codeship custom script depends on:
# - API_KEY env var from Heroku
set -e

export HEROKU_API_KEY="${API_KEY}"

# default app name
APP_NAME="dry-shore-86449"

APP_URL="${APP_NAME}.herokuapp.com"

# Turn on Heroku maintenance mode
heroku maintenance:on --app ${APP_NAME}

# run migrations and update static content
heroku_run "php artisan migrate" ${APP_NAME}

# Turn off Heroku maintenance mode
heroku maintenance:off --app ${APP_NAME}

# check if the app is up and running
check_url "${APP_URL}"
