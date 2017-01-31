#!/bin/bash

#update pacman database in arch linux
pacman -Sy

#install apache and php
pacman -S --noconfirm --needed apache
pacman -S --noconfirm --needed php
pacman -S --noconfirm --needed php-apache

pacman -S --noconfirm --needed sqlite
pacman -S --noconfirm --needed php-sqlite
pacman -S --noconfirm --needed php-mcrypt

pacman -S --noconfirm --needed tar
pacman -S --noconfirm --needed openssl

pacman -S --noconfirm --needed mono
pacman -S --noconfirm --needed pkg-config

pacman -S --noconfirm --needed freeradius
ln -s -f /etc/raddb /etc/freeradius

#copy everything to /etc
mkdir -p /etc/SimpleRadius
cp -r * /etc/SimpleRadius/
cp -r ./factory_default/* /etc/SimpleRadius/
chown -R http:http /etc/SimpleRadius
chmod -R 700 /etc/SimpleRadius

#copy the php files
mkdir -p /var/www/SimpleRadius
cp -r ./html/* /var/www/SimpleRadius/
chown -R root:root /var/www/SimpleRadius
chmod 755 /var/www/SimpleRadius
find /var/www/SimpleRadius -type f -exec chmod 644 {}\;


#setup apache config and php
#setup php first so all extensions are enabled
cp ./installation/arch_linux/php/php.ini /etc/php/php.ini
mkdir /run/httpd
cp -r ./installation/arch_linux/apache2/conf/* /etc/httpd/conf/
#apachectl start

#setup freeradius
freeradius_version=`pacman -Q freeradius | cut -d' ' -f2 | cut -d'.' -f1,2`
cp ./system_configs/etc/freeradius/${freeradius_version}/eap /etc/freeradius/mods-enabled/eap
cp ./system_configs/etc/freeradius/${freeradius_version}/eap /etc/freeradius/mods-available/eap
cp ./system_configs/etc/freeradius/${freeradius_version}/radiusd.conf /etc/freeradius/radiusd.conf
chown -R radiusd.radiusd /etc/raddb
#openssl dhparam -out /etc/freeradius/certs/dh 2048
chown -R radiusd.radiusd /run/radiusd


#setup the script
pacman -S --noconfirm --needed sudo
if [ `grep ^http /etc/sudoers | grep 'ALL = NOPASSWD: /etc/SimpleRadius/scripts/simple_radius.php' | wc -l` -eq "0" ]; then
  echo "http	ALL = NOPASSWD: /etc/SimpleRadius/scripts/simple_radius.php" >> /etc/sudoers
fi
chown http:http /etc/SimpleRadius/scripts/simple_radius.php
chmod 700 /etc/SimpleRadius/scripts/simple_radius.php


#then setup the rest such as certs, etc
/etc/SimpleRadius/scripts/simple_radius.php Factory_Reset






