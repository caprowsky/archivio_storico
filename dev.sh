#!/bin/bash

comando=$1
directory=/home/ale/docker/archiviostorico
project=dev


if [ $comando = "ps" ]; then
    cd $directory
    docker-compose ps
fi

if [ $comando = "up" ]; then
    cd $directory
    docker-compose -f docker-compose.yml -f docker-compose-${project}.yml up -d
fi

if [ $comando = "stop" ]; then
    cd $directory
    docker-compose stop
fi

if [ $comando = "restart" ]; then
    cd $directory
    docker-compose stop
    docker-compose -f docker-compose.yml -f docker-compose-${project}.yml up -d
fi

if [ $comando = "down" ]; then
    cd $directory
    docker-compose down
fi

if [ $comando = "pull" ]; then
    cd $directory
    docker-compose pull
fi


if [ $comando = "exec" ]; then
    cd $directory
    docker-compose exec php /bin/bash
fi