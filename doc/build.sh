#!/bin/bash

BASE=`dirname $0`

$BASE/../bin/carew build --base-dir=$BASE --web-dir=$BASE/web $@
