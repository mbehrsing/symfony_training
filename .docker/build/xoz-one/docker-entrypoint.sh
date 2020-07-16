#!/bin/sh

TARGET_DIR="/etc/xoz-one/"
if [ ! -z "$SUBDIR_NAME" ];
then
    TARGET_DIR="/etc/xoz-one/${SUBDIR_NAME}/"
    mkdir -p "/etc/xoz-one/${SUBDIR_NAME}"
fi


cp -rf /etc/xoz-one-copy/ssl/xoz.one.key "${TARGET_DIR}${BASE_NAME_KEY}.key"
cp -rf /etc/xoz-one-copy/ssl/xoz.one.crt "${TARGET_DIR}${BASE_NAME_CRT}.crt"


tail -f /dev/null