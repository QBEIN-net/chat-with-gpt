#!/bin/bash

if [[ -z $1 ]]; then
    echo -e "You should run as \033[4mbash release.sh version\033[0m"
    exit 0;
fi

VER=$1
#DT=`date +%Y-%m-%d`

#clear folders
rm -rf ./svn/trunk/*
rm -rf ./svn/tags/${VER}/*
rm -rf ./svn/assets/*

# create tar dir if need
test ! -d ./svn/assets && { mkdir -p ./svn/assets ; }
test ! -d ./svn/branches && { mkdir -p ./svn/branches ; }
test ! -d ./svn/trunk && { mkdir -p ./svn/trunk ; }
test ! -d ./svn/tags/${VER} && { mkdir -p ./svn/tags/${VER} ; }

#update readme && main file
sed -i -E "s/Stable tag: (.*)/Stable tag: ${VER}/" readme.txt
sed -i -E "s/ \* Version:     (.*)/ \* Version:     ${VER}/" bootstrap.php

#copy files
cp -vr ./asset ./svn/trunk/asset/
cp -vr ./asset ./svn/tags/${VER}/asset/

cp -vr ./controller ./svn/trunk/controller/
cp -vr ./controller ./svn/tags/${VER}/controller/

cp -vr ./languages ./svn/trunk/languages/
cp -vr ./languages ./svn/tags/${VER}/languages/

cp -vr ./vendor ./svn/trunk/vendor/
cp -vr ./vendor ./svn/tags/${VER}/vendor/

cp -vr ./views ./svn/trunk/views/
cp -vr ./views ./svn/tags/${VER}/views/

cp -v ./bootstrap.php ./svn/trunk/
cp -v ./bootstrap.php ./svn/tags/${VER}

cp -v ./readme.txt ./svn/trunk/
cp -v ./readme.txt ./svn/tags/${VER}

#copy common files

cp ./*.png ./svn/assets

#msg
echo -e "\e[31m!!! PLEASE UPDATE \033[4mchangelog !!!\e[0m"
