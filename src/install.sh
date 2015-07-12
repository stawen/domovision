#!/bin/bash

INSTALL_DIR=/usr/local/bin
CONTEXT=${PWD/\/src}
pth="pth-2.0.7"
zlogger="zlogger-1.5.0"
eibnetmux="eibnetmux-2.0.1"
eibnetmuxSamples="eibnetmuxclientsamples-1.7.1"
eibnetmuxDomo="eibnetmuxclient-domovision"
log=$CONTEXT/install.log

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

function valid_ip()
{
    local  ip=$1
    local  stat=1

    if [[ $ip =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
        OIFS=$IFS
        IFS='.'
        ip=($ip)
        IFS=$OIFS
        [[ ${ip[0]} -le 255 && ${ip[1]} -le 255 \
            && ${ip[2]} -le 255 && ${ip[3]} -le 255 ]]
        stat=$?
    fi
    return $stat
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

    echo "|||||| -> Configuration                                                       ||||||"
    
    while true; do
        read -p "-> Etes-vous sur un Raspberry PI ? [Y/N] " yn
        case $yn in
            [Yy]* ) rpi=1;break;;
            [Nn]* ) rpi=0;break;;
            * ) echo "-> Please answer Y or N ! <-";;
        esac
    done
    
    
    while true; do
        read -p "-> Voulez-vous installer la suite apache + php + mysql ? [Y/N] " yn
        case $yn in
            [Yy]* ) lamp=1;break;;
            [Nn]* ) lamp=0;break;;
            * ) echo "-> Please answer Y or N ! <-";;
        esac
    done
    
    
    if [ $lamp == 1 ] 
    then
        while true; do
            read -p "-> Voulez-vous faire DOMOVISION votre site apache par defaut ? [Y/N] " yn
            case $yn in
                [Yy]* ) apacheDefault=1;break;;
                [Nn]* ) apacheDefault=0;break;;
                 * ) echo "-> Please answer Y or N ! <-";;
            esac
        done
    fi
    
        
    
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

    apt-get -qy install build-essential 

echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing $pth                                                     "
echo "||||||------------------------------------------------------------------------||||||"
    
    cd $CONTEXT/src/pck/
    tar zxf $pth".tar.gz"
    cd $pth
    ./configure     >> $log
    make            >> $log
    make install    >> $log
    ldconfig        >> $log
    
    cd $CONTEXT
    
    rm -drf $CONTEXT/src/pck/$pth
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing $zlogger                                                     "
echo "||||||------------------------------------------------------------------------||||||"
    
    cd $CONTEXT/src/pck/
    tar jxf $zlogger".tar.bz2"
    cd $zlogger
    ./configure --with-plugins --enable-pth-plugins >> $log
    make                                            >> $log
    make install                                    >> $log
    ldconfig                                        >> $log
    
    cd $CONTEXT
    
    rm -drf $CONTEXT/src/pck/$zlogger
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing $eibnetmux                                                     "
echo "||||||------------------------------------------------------------------------||||||"
    
    cd $CONTEXT/src/pck/
    tar zxf $eibnetmux".tar.gz"
    cd $eibnetmux
    ./configure     >> $log
    make            >> $log
    make install    >> $log
    ldconfig        >> $log
    
    cd $CONTEXT
    
    rm -drf $CONTEXT/src/pck/$eibnetmux    
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing eibnetmux Custom DOMOVISION                                    "
echo "||||||------------------------------------------------------------------------||||||"
    
    cd $CONTEXT/src/pck/
    tar zxf $eibnetmuxSamples".tar.gz"
    
    
    cp -f $CONTEXT/src/pck/$eibnetmuxDomo/eibtrace/eibtrace.c $CONTEXT/src/pck/$eibnetmuxSamples/eibtrace/eibtrace.c
    cp -f $CONTEXT/src/pck/$eibnetmuxDomo/eibcommand/eibcommand.c $CONTEXT/src/pck/$eibnetmuxSamples/eibcommand/eibcommand.c
    cp -f $CONTEXT/src/pck/$eibnetmuxDomo/eibread/eibread.c $CONTEXT/src/pck/$eibnetmuxSamples/eibread/eibread.c
    
    
    cd $eibnetmuxSamples
    ./configure     >> $log
    make            >> $log
    make install    >> $log
    
    cp -f $CONTEXT/src/pck/$eibnetmuxSamples/eibcommand/eibcommand /usr/local/bin/
    cp -f $CONTEXT/src/pck/$eibnetmuxSamples/eibread/eibread /usr/local/bin/
    cp -f $CONTEXT/src/pck/$eibnetmuxSamples/eibtrace/eibtrace /usr/local/bin/
    
    ldconfig >> $log
    
    cd $CONTEXT
    
    rm -drf $CONTEXT/src/pck/$eibnetmuxSamples
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Installing Mod PHP for dameon and inotify                                 "
echo "||||||------------------------------------------------------------------------||||||"    
    
    if [ $lamp == 1 ] 
    then
         echo "|||||| -> Installation Apache + Php5 + Mysql                         ||||||"
        apt-get -qqy install php5 php5-cli php5-mysql libapache2-mod-php5 apache2 mysql-server php-pear
    fi
    pear install -f System_Daemon   >> $log
    pecl install -f inotify         >> $log

echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Custom Logrotate                                "
echo "||||||------------------------------------------------------------------------||||||"   

    cp -f $CONTEXT/src/etc/php5/mods_available/30-inotify.ini /etc/php5/mods-available/
    cp -f $CONTEXT/src/etc/logrotate/apache2 /etc/logrotate.d/
    cp -f $CONTEXT/src/etc/logrotate/eibtrace /etc/logrotate.d/
    cp -f $CONTEXT/src/etc/logrotate/mysql-server /etc/logrotate.d/
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Creation partition en RAM                                                "
echo "||||||------------------------------------------------------------------------||||||"     
 
    if [ $rpi == 1 ] 
    then
        echo "|||||| -> /var/log -> en Ram                                                  ||||||"
        echo   "tmpfs           /var/log         tmpfs nodev,nosuid,size=50M 0 0" >> /etc/fstab  
    fi
    
    mkdir -p $CONTEXT/core/tmp
    chmod 777 $CONTEXT/core/tmp
    echo   "tmpfs           $CONTEXT/core/tmp         tmpfs nodev,nosuid,size=50M 0 0" >> /etc/fstab 

echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Update Sudoers (restart daemon from web interface)                        "
echo "||||||------------------------------------------------------------------------||||||"      

    
    echo "###############################################" >> /etc/sudoers
    echo "####                DOMOVISION              ###" >> /etc/sudoers
    echo "###############################################" >> /etc/sudoers
    echo "# User alias specification"                      >> /etc/sudoers
    echo "User_Alias DOMO=www-data"                        >> /etc/sudoers
    echo "# Cmnd alias specification"                      >> /etc/sudoers
    echo "Cmnd_Alias KNX_DAEMON=/etc/init.d/knx-daemon,$CONTEXT/bin/daemon/knx-sniffer" >> /etc/sudoers
    echo "DOMO ALL=(ALL) NOPASSWD:KNX_DAEMON"              >> /etc/sudoers    
    
echo "||||||------------------------------------------------------------------------||||||"
echo "|||||| -> Install Daemon script                        "
echo "||||||------------------------------------------------------------------------||||||"      
    
echo "|||||| -> Knx-bus                                                             ||||||"
    
    echo "PATH=/sbin:/usr/sbin:/bin:/usr/bin:$CONTEXT/core:/usr/local/bin" > /etc/default/knx-bus
    
    while true; do
        read -p "-> KNX GateWay IP ? [ex 192.168.0.23] " ip
        if valid_ip $ip
        then 
            break;
        else
            echo "$ip is not a valide ip !!"
        fi
    done
    
    
    echo "GATEWAY_KNX='$ip'" >> /etc/default/knx-bus
    
echo "|||||| -> Knx-daemon                                                          ||||||"

    echo "PATH=/sbin:/usr/sbin:/bin:/usr/bin:$CONTEXT/core:/usr/local/bin:$CONTEXT/bin/daemon" > /etc/default/knx-daemon
    echo "PATH_DAEMON=$CONTEXT/bin/daemon" >> /etc/default/knx-daemon

echo "|||||| -> Knx-sniffer                                                         ||||||"
    
    echo "PATH=/sbin:/usr/sbin:/bin:/usr/bin:$CONTEXT/core:/usr/local/bin" > /etc/default/knx-sniffer
    echo "CONTEXT=$CONTEXT" >> /etc/default/knx-sniffer

echo "|||||| -> Knx-trace                                                           ||||||"
    
    echo "PATH=/sbin:/usr/sbin:/bin:/usr/bin:$CONTEXT/core:/usr/local/bin" > /etc/default/knx-trace
    echo "CONTEXT=$CONTEXT" >> /etc/default/knx-trace

echo "|||||| -> Knx-tracking                                                        ||||||"
    
    echo "PATH=/sbin:/usr/sbin:/bin:/usr/bin:$CONTEXT/core:/usr/local/bin" > /etc/default/knx-tracking
    echo "CONTEXT=$CONTEXT" >> /etc/default/knx-tracking