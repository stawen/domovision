#!/bin/bash

INSTALL_DIR=/usr/local/bin
TEMP_DIR=`mktemp -d /tmp/eibd.XXXXXX`
EIBD_bin=$INSTALL_DIR/eibd
pthsem_url="http://downloads.sourceforge.net/project/bcusdk/pthsem/pthsem_2.0.8.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fbcusdk%2Ffiles%2Fpthsem%2F&ts=1414933358&use_mirror=freefr"
bcusdk_url="http://downloads.sourceforge.net/project/bcusdk/bcusdk/bcusdk_0.0.5.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fbcusdk%2F&ts=1414933504&use_mirror=freefr"

check_run()  {
    "$@"
    local status=$?
    if [ $status -ne 0 ]; then
        echo "error with $1" >&2
	exit
    fi
    return $status
}

# Check for root priviledges
if [ $(id -u) != 0 ]
then
	echo "Superuser (root) priviledges are required to install eibd"
	echo "Please do 'sudo -s' first"
	exit 1
fi

# Check if eibd was already installed /usr/local/bin/eibd 
upgrade_eibd="no"
if [ -e $EIBD ]
then
	upgrade_eibd="yes"
	
	echo "Previous eibd installation found"
	if [ -e /etc/init.d/eibd ]
	then
		/etc/init.d/eibd stop
	fi
else
	echo "eibd new installation"
fi

echo "*****************************************************************************************************"
echo "*                                Installing additional libraries                                    *"
echo "*****************************************************************************************************"
apt-get -qy install build-essential 


if [ "$(cat /etc/eibd/pthsem_VERSION)" != "v2.0.8" ]
then

echo "*****************************************************************************************************"
echo "*                              Installing PTHSEM V2.0.8 libraries                                   *"
echo "*****************************************************************************************************"
echo "Getting pthsem..."
cd $TEMP_DIR
check_run wget -q $pthsem_url -O - | tar -zx

cd pthsem-2.0.8

echo "Compiliing pthsem..." 
check_run ./configure --with-mctx-mth=sjlj --with-mctx-dsp=ssjlj --with-mctx-stk=sas --disable-shared

check_run make
check_run sudo make install

export LD_LIBRARY_PATH="/usr/local/lib"
sudo ldconfig 
mkdir -p /etc/eibd
echo "v2.0.8" > /etc/eibd/pthsem_VERSION
fi

if [ "$(cat /etc/eibd/bcusdk_VERSION)" != "v0.0.5" ] 
then
echo "*****************************************************************************************************"
echo "*                              Installing BCUSDK V0.0.5 libraries                                   *"
echo "*****************************************************************************************************"
echo "Getting bcusdk..."
cd $TEMP_DIR
check_run wget -q $bcusdk_url -O - | tar -zx
cd bcusdk-0.0.5 

echo "Compiliing bcusdk..."
check_run ./configure --without-pth-test --enable-onlyeibd --enable-eibnetip --enable-eibnetiptunnel --enable-eibnetipserver --enable-groupcache --enable-usb
check_run make
check_run sudo make install

echo "v0.0.5" > /etc/eibd/bcusdk_VERSION
fi


chmod gu+xr /etc/init.d/eibd

# Add eibd.log to logrotate
echo '/var/log/eibd.log {
        daily
        size=10M
        rotate 4
        compress
        nodelaycompress
        missingok
        notifempty
}' > /etc/logrotate.d/eibd

# Add eibd to autostart
#echo "Adding eibd to autostart"
#update-rc.d eibd defaults


# Make sure to save changes
sync
#echo
#echo Avant de démarrer eibd, veuillez configurer /etc/default/eibd
#echo
#echo "EIBD_BACKEND=<ip|ipt|usb|bcu1>"
#echo " - si ipt EIBD_PORT_IPT=<IP du routeur>"
#echo " - si ip EIBD_PORT_IP=<IP de la passerelle>"
#echo " - si usb  EIBD_URL=<usb port>"
#echo " - si bcu1 EIBD_PORT_SERIAL=<port>"
sudo echo "# Configuration demarrage /etc/init.d/eibd " > /etc/default/eibd
sudo echo "DAEMON_ARGS=\"--daemon=/var/log/eibd.log --pid-file=/var/run/eibd.pid -D -S -T --listen-tcp\"" >> /etc/default/eibd
#echo "Choisir le type de connexion ip|ipt|usb|bcu1"
#read ConnectType;
#if test "$ConnectType" = "ip"; then
#	sudo echo "EIBD_BACKEND=\"ip\"" >> /etc/default/eibd
#	echo "Saisir l'IP de la passerelle"
#	read ConnectPort;
#	if test "x$ConnectPort" = x; then
#		echo
#	else
#		sudo echo "EIBD_PORT_IP=\"$ConnectPort\"" >> /etc/default/eibd
#	fi 
#elif test "$ConnectType" = "ipt"; then
#	sudo echo "EIBD_BACKEND=\"ipt\"" >> /etc/default/eibd
#	echo "Saisir l'IP du routeur"
#	read ConnectPort;
#	if test "x$ConnectPort" = x; then
#		echo
#	else
#		sudo echo "EIBD_PORT_IPT=\"$ConnectPort\"" >> /etc/default/eibd
#	fi 
#elif test "$ConnectType" = "usb"; then
#	sudo echo "EIBD_BACKEND=\"usb\"" >> /etc/default/eibd
#	echo "Saisir l'adresse du port USB"
#	read ConnectPort;
#	if test "x$ConnectPort" = x; then
#		echo
#	else
#		sudo echo "EIBD_URL=\"$ConnectPort\"" >> /etc/default/eibd
#	fi 
#elif test "$ConnectType" = "bcu1"; then
#	sudo echo "EIBD_BACKEND=\"bcu1\"" >> /etc/default/eibd
#	echo "Saisir l'adresse du port serie"
#	read ConnectPort;
#	if test "x$ConnectPort" = x; then
#		echo
#	else
#		sudo echo "EIBD_PORT_SERIAL=\"$ConnectPort\"" >> /etc/default/eibd
#	fi 
#fi 
#echo "Entrez le nom du compte User (lance Eibd)"
#read login;
#if test "x$login" = x; then
#	echo
#else
#	sudo echo "USER=$login" >> /etc/default/eibd
#fi    
sudo update-rc.d eibd defaults
echo " " > /var/log/eibd.log
sudo chmod 777 /var/log/eibd.log
/etc/init.d/eibd start
echo
echo "Thank you for using eibd!"
