#!/bin/bash

echo -e "Fixing permissions..."

usermod -o --uid ${OWNER_UID} www-data
groupmod -o --gid ${OWNER_GID} www-data

chown -R www-data:www-data /var/www/html/

if ! command -v wget &>/dev/null; then
  echo -e "Install Wget"

  apt-get update
  apt-get install wget -y
fi

if ! command -v composer &>/dev/null; then
  echo -e "Install Composer"

  wget -O composer-setup.php https://getcomposer.org/installer
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm composer-setup.php
  composer self-update
fi

if ! command -v wp &>/dev/null; then
  echo -e "Install WP CLI"
  curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  mv wp-cli.phar /usr/local/bin/wp

fi


apt-get update && apt-get install -y default-mysql-client



docker-entrypoint.sh apache2-foreground
