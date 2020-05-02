#!/bin/bash


docker-compose down;
docker-compose -f docker-compose.yml -f docker-compose-dev.yml up -d;

sleep 20
docker exec -ti studenti_solr make create core=default -f /usr/local/bin/actions.mk

sleep 20
drush updb -y;
drush cim -y;

drush cr;
drush search-api-clear;
drush search-api-index;


