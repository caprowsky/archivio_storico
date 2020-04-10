#!/bin/bash

pacchetto=$1

if [ $pacchetto = "core" ]; then
   composer update drupal/core webflo/drupal-core-require-dev --with-dependencies
   exit
fi

composer update --with-dependencies $pacchetto
