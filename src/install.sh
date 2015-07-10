#!/bin/bash

INSTALL_DIR=/usr/local/bin
CONTEXT=${PWD/\/src}
pth="pth-2.0.7"
zlogger="zlogger-1.5.0"
eibnetmux="eibnetmux-2.0.1"
eibnetmuxSamples="eibnetmuxclientsamples-1.7.1"
eibnetmuxDomo="eibnetmuxclient-domobision"

daemon_eibnetmux="knx-daemon"

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
	echo "Superuser (root) priviledges are required to install DOMOVISION"
	echo "Please do 'sudo -s' first"
	exit 1
fi




echo "||||||------------------------------------------------------------------------||||||"
echo "||||||                                DOMOVISION                              ||||||"
echo "||||||                Interaction et supervision de votre bus Knx             ||||||"
echo "||||||                                                                        ||||||"
echo "|||||| Author : Stawen DRONEK                                                 ||||||"
echo "||||||------------------------------------------------------------------------||||||"


    
# Check if eibnetmux was already installed /usr/local/bin/eibnetmux 
echo "|||||| -> Check if DOMOVISION was already installed                           ||||||"

if [ -e /etc/init.d/$daemon_eibnetmux ]
then
    echo "|||||| Previous DOMOVISION installation found............Stop dameon in progress"
	/etc/init.d/$daemon_eibnetmux stop

else
	echo "|||||| DOMOVISION new installation ! :)"
fi

echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing additional libraries                                     ||||||"

#apt-get -qy install build-essential 

echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing $pth                                                     "
echo "||||||------------------------------------------------------------------------||||||"
    
    cd $CONTEXT"/src/pck/"
    tar zxf $pth".tar.gz"
    cd $pth
    ./configure
    make
    make install
    ldconfig
    
    cd $CONTEXT
    
    rm -drf $CONTEXT"/src/pck/"$pth
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing $zlogger                                                     "
echo "||||||------------------------------------------------------------------------||||||"
    
    cd $CONTEXT"/src/pck/"
    tar zxf $zlogger".tar.gz"
    cd $zlogger
    ./configure --with-plugins --enable-pth-plugins
    make
    make install
    ldconfig
    
    cd $CONTEXT
    
    rm -drf $CONTEXT"/src/pck/"$zlogger