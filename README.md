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

## Installation

* the application requires the class 'IntlDateFormatter'
  * installation is described at http://php.net/manual/en/intl.installation.php
* the web-root of your domain need to point to the ``public`` directory
* create a new database and import the file ``database.sql`` in the ``db`` directory
* import the file ``data.prod.sql`` in the ``db`` directory
* copy the file ``settings.example.php`` in the folder ``src`` and rename it to ``settings.php``
* insert your database credentials in ``settings.php``
* insert the default location and i18n settings in ``settings.php``
* install the required composer dependencies with ``composer install``
* you can login at ``http://<your-domain>`` with the default user ``admin`` and password ``admin``
* create a cronjob which calls ``http://<your-domain>/cron`` every hour or run the console command ``cron`` with ``php bin/console.php cron``


## Notes
* when using push notifications PHP 7.1 with GMP is needed


More information available on http://www.haegi.org
