# Life-Tracking Dashboard

## Features

* track your location with tasker
* track income and spendings
* manage income/spendings categories
* automatically add monthly income/spendings
* track fuel consumption for multiple cars with multiple users
* income/spendings and fuel statistics
** monthly statistics via mail
* multi-language support
* multi-user support
** profile image
** change password
* kanban like boards 
** stacks
** cards with labels, date, description, assigned users

## Installation

* the web-root of your domain need to point to the ``public`` directory
* create a new database and import the file ``database.sql`` in the ``db`` directory
* copy the file ``settings.example.php`` in the folder ``src`` and rename it to ``settings.php``
* insert your database credentials in ``settings.php``
* insert the default location and i18n settings in ``settings.php``
* install the required composer dependencies with ``composer install``
* you can login with the default user ``admin`` and password ``admin``
* create a cronjob which calls ``http://<your-domain>/cron`` every hour


More information available on http://www.haegi.org