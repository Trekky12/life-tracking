# Life-Tracking Dashboard

## Features

### General
* multi-language support
* multi-user support
  * profile image
  * change password
  * stay logged-in
* progressive web app
  * using web push api for notifications
* internal notifications
* activity log

### Modules
* track your location with tasker
* track income and spendings
  * manage income/spendings categories
  * automatically add recurring income/spendings in different intervals
  * automatically assign a category to income/spendings
  * manage monthly budgets
  * income/spendings and budget statistics
  * monthly statistics via mail
* track fuel consumption for multiple cars with multiple users 
  * statistics of fuel consumption and km/year
* kanban like boards 
  * stacks
  * cards with labels, date, description, assigned users
* manage external data of crawlers/scrapers
  * customize data fields of datasets 
  * show link list
  * filter by new or new and updated entries
* split bills with other users in individual groups
  * support for foreign currencies with adjustable exchange rate and exchange fee
  * optional: automatically add a finance entry for a splitted bill
* trip planning
  * add events, flights, car rentals, accommodations, train rides and car drives and show them on a map
  * filter by date
* timesheets
  * track start/end time for individual projects
  * export timesheets to Excel
  * end-to-end-encrypted notices/files on sheets
* workouts
  * add exercises to trainig plans and track training sessions
* recipes
  * create/edit recipes
  * cookbooks
  * mealplans
  * shoppinglists

More information can be found in the help file ([en](/docs/help-en.md)/[de](/docs/help-de.md)).

## Installation

* the application requires the class 'IntlDateFormatter'
  * installation is described at http://php.net/manual/en/intl.installation.php
* the web-root of your domain need to point to the ``public`` directory
* copy the file ``settings.example.php`` in the folder ``src`` and rename it to ``settings.php``
* create a new database
* insert your database credentials in ``settings.php``
* insert the default location and i18n settings in ``settings.php``
* install the required composer dependencies with ``composer install``
* open ``http://<your-domain>`` and create the database tables
* you can login at ``http://<your-domain>`` with the default user ``admin`` and password ``admin``
* create a cronjob which calls ``http://<your-domain>/cron`` every minute or run the console command ``cron`` with ``php bin/console.php cron``

## Notes
* when using push notifications min. PHP 7.1 with GMP is needed

# Development
* ``composer`` is used for the PHP dependencies
* ``gulp`` is used to minify the javascript and create the css from sass
  * ``gulp uglify`` to minify the javascript at ``js/*.js``
  * ``gulp sass`` to create the css
* ``npm`` is used for the JavaScript dependencies
  * After updating the javascript dependencies with npm the dependencies can be copied to the static folder with ``gulp copy``
  * The screenshots for the help page and docs can be created with ``npm run make-screenshots``
* PHPUnit is used to run the testsuite ``lifetracking``
  * ``'vendor/bin/phpunit' --configuration phpunit.xml --testsuite 'lifetracking'``
