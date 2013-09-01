#!/bin/bash

BASE=`dirname $0`

$BASE/../carew carew:build --base-dir=$BASE --web-dir=$BASE/web $@
