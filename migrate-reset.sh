#!/usr/bin/env bash

drush pm-uninstall migrando -y; drush pm-uninstall migrate_tools -y; drush pm-uninstall migrate_plus -y; drush pm-uninstall migrate -y; drush pm-uninstall migrate_source_csv -y

drush en migrando migrate_source_csv -y
