#!/bin/bash

BASE=`dirname $0`/..

$BASE/bin/carew build --base-dir=$BASE/doc --web-dir=$BASE/doc/web $@
