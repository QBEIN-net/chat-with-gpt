#!/bin/bash
/usr/bin/uglifyjs asset/common.js -o asset/common.min.js
/home/kpuk/.npm-global/bin/csso -i asset/chat.css -o asset/chat.min.css