#!/bin/bash

PROJECT_DIR=$(realpath $(dirname $0)/../)
cd $PROJECT_DIR
git pull origin master
rm -rf ./var/*
mkdir ./var/logs
./lib/limb/limb migrate_run
