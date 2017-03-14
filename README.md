wp-odm_solr
==========

ODI Internal Wordpress plugin for indexing created/updated WP contents automatically into a solr index

# Description

# Features

## Feature 1: Add related CKAN datasets to posts.

# Installation

1. Either download the files as zip or clone <code>git clone https://github.com/OpenDevelopmentMekong/wp-odm_solr.git</code> into the Wordpress plugins folder.
2. Install dependencies with composer (http://getcomposer.org) <code>composer install</code>
3. Activate the plugin through the 'Plugins' menu in WordPress

# Development

1. Install composer http://getcomposer.org/
2. Edit composer.json for adding/modifying dependencies versions
3. Install dependencies <code>composer install</code>

# Uses

* SolariumPHP https://github.com/solariumphp/solarium

# Testing

Tests are found on /tests and can be run with ```phpunit tests```

# Continuous deployment

Everytime code is pushed to the repository, travis will run the tests available on **/tests**. In case the code has been pushed to **master** branch and tests pass, the **_ci/deploy.sh** script will be called for deploying code in CKAN's DEV instance. Analog to this, and when code from **master** branch has been **tagged as release**, travis will deploy to CKAN's PROD instance automatically.

For the automatic deployment, the scripts on **_ci/** are responsible of downloading the odm-automation repository, decrypting the **odm_tech_rsa.enc** private key file ( encrypted using Travis-ci encryption mechanism) and triggering deployment in either DEV or PROD environment.

# Copyright and License

This material is copyright (c) 2014-2015 East-West Management Institute, Inc. (EWMI).

It is open and licensed under the GNU General Public License (GPL) v3.0 whose full text may be found at:

http://www.fsf.org/licensing/licenses/gpl-3.0.html
