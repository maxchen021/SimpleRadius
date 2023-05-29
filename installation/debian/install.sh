#!/bin/bash

set -ex

export TZ='America/New_York'
export DEBIAN_FRONTEND=noninteractive

apt-get update
apt-get -y upgrade

apt-get -y install vim
echo "set mouse-=a" > ~/.vimrc

#install apache and php
apt-get -y install apache2

apt-get -y install lsb-release ca-certificates curl
curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
apt-get update
apt-get -y install php8.2
apt-get -y install sqlite3
apt-get -y install php8.2-sqlite3 php8.2-mcrypt

a2enmod ssl
a2enmod rewrite

cp /etc/php/8.2/apache2/php.ini /etc/php/8.2/apache2/php.ini.orig
echo "extension=sqlite3" >> /etc/php/8.2/apache2/php.ini
echo "extension=mcrypt.so" >> /etc/php/8.2/apache2/php.ini

#copy everything to /etc
mkdir /etc/SimpleRadius
cp -r * /etc/SimpleRadius/
cp -r ./factory_default/* /etc/SimpleRadius/
chown -R www-data:www-data /etc/SimpleRadius
chmod -R 700 /etc/SimpleRadius

#copy the php files
mkdir -p /var/www/SimpleRadius
cp -r ./html/* /var/www/SimpleRadius/
chown -R www-data:www-data /var/www/SimpleRadius
chmod 755 /var/www/SimpleRadius
find /var/www/SimpleRadius -type f -exec chmod 644 {} \;



#setup apache config
rm /etc/apache2/sites-enabled/*
cp ./installation/debian/apache2/conf/SimpleRadius/*.conf /etc/apache2/sites-enabled/
#apachectl restart

#install and setup freeradius
apt-get -y install freeradius
freeradius_version="3.0"
cp ./system_configs/etc/freeradius/${freeradius_version}/radiusd.conf /etc/freeradius/${freeradius_version}/radiusd.conf
chown freerad:freerad /etc/freeradius/${freeradius_version}/radiusd.conf
chmod 640 /etc/freeradius/${freeradius_version}/radiusd.conf

#setup the script
apt-get -y install sudo
echo "www-data	ALL = NOPASSWD: /etc/SimpleRadius/scripts/simple_radius.php" >> /etc/sudoers
chown www-data:www-data /etc/SimpleRadius/scripts/simple_radius.php
chmod 700 /etc/SimpleRadius/scripts/simple_radius.php

#then setup the rest such as certs, etc
/etc/SimpleRadius/scripts/simple_radius.php Factory_Reset


