FROM exozet/xoz-one

ENV BASE_NAME_KEY=xoz.one \
    BASE_NAME_CRT=xoz.one \
    SUBDIR_NAME=ssl

ADD docker-entrypoint.sh /etc/xoz-one-copy/docker-entrypoint.sh

ENTRYPOINT /etc/xoz-one-copy/docker-entrypoint.sh
