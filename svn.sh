#!/bin/bash

cd ./svn/

if [ ! -d \.svn ]; then
  echo -e "\e[31mFor first push you should add repository\e[0m"
  echo -e "\e[31m\e[4msvn co https://plugins.svn.wordpress.org/chat-with-gpt ./svn/\e[0m"
    exit 0;
fi;

svn add ./assets/*
svn add ./branches/*
svn add ./tags/*
svn add ./trunk/*

if [[ -z $1 ]]; then
    echo -e "\e[31mRun in svn directory \e[4msvn ci -m 'MESSAGE' --username kubalskiy\e[0m"
    exit 0;
fi
svn ci -m "${1}"
