#!/bin/bash


docker-compose down;
docker-compose up -d;

sleep 20

drush updb -y;
drush cim -y;
drush cr;
#docker exec -ti ilminutodrupal_solr make create core=default -f /usr/local/bin/actions.mk;
