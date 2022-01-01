FROM ubuntu:18.04
MAINTAINER Aloxaf

LABEL name="cdda-itembrowser"
LABEL version="0.1.0"

RUN sed -i -E "s#[^/]+.ubuntu.com#mirrors.aliyun.com#g" /etc/apt/sources.list \
    && apt-get update \
    && export DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai \
    && apt-get -y install php7.2 php7.2-mbstring php7.2-xml php7.2-curl php7.2-ds composer sudo unzip language-pack-zh-hans curl git python3 gettext libxapian30 zsh fish rsync doxygen cron vim \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && sed -i 's#/usr/lib#/cdda/src/public#g' /etc/apache2/conf-enabled/serve-cgi-bin.conf \
    && ln -s /etc/apache2/mods-available/cgi.load /etc/apache2/mods-enabled/cgi.load \
    && rm -rf /var/www/html \
    && ln -sf /cdda/src/public /var/www/html \
    && service apache2 start

RUN git clone https://hub.fastgit.org/Aloxaf/cdda-itembrowser /cdda \
    && cd /cdda \
    && cp src/.env.example src/.env \
    && cp /usr/bin/doxysearch.cgi src/public/cgi-bin/doxysearch.cgi \
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
    && composer -dsrc install \
    && php src/artisan key:generate \
    && chgrp -R www-data /cdda/src/storage/* \
    && chmod -R g+ws /cdda/src/storage/* # 执行完下一行命令似乎又要再执行一次这个? \
    && zsh update.zsh

EXPOSE 80

CMD ["apache2ctl", "start"]
