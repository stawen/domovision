/***********************************
*********** FixTo  *****************
***********************************/

Global :
- gerer les objets de type Volet avec un slide bar - OK


Graphique journalier
- faire la page dynamique en fonction des parametres utilisateurs -OK
- Finir Up / Down
- gestion de la suppresion d'un groupe, supprimer l'asso avec - OK

Gestion equipement :
- modal, si type etat alors par defaut c'est un indicateur, mais attention avec les volets (a confirmer)
- a la suppression, gerer le delete cascade de la suppression - OK
- rajouter un parametre sur l'historique pour les eqt d'etat (toutes les 60 sec, ou  a chaque changement d'etat) => reflection : insert a chaque changement d'etat serait
    plus judicieux que toutes les minutes ?

Gestion des actions :
- Finir Up / Down
- finir maj asso, + suppression - OK

Visu du bus :
- faire un mode visu brut du bus

Configuration : 
- gestion de l'adresse Ip de la passerelle
- relancer le daemon global
- relancer les daemons sniffer - OK
- faire un alerte sur la page d'acceuil sur un des daemons est KO

Mise à jour :
- Tout à faire


---script install

apt-get update 
#apt-get dist-upgrade
apt-get install build-essential


wget ftp://ftp.gnu.org/gnu/pth/pth-2.0.7.tar.gz
tar zxvf pth-2.0.7.tar.gz
cd pth-2.0.7/
./configure
make
make install

##utiliser ceci au lieu de pthsem_2.0.8.tar.gz


cd /usr/src/pck 
tar xfv zlogger-1.5.0.tar.bz2 
cd zlogger-1.5.0 
./configure --with-plugins --enable-pth-plugins
make 
make install
ldconfig




# cd /usr/src/ 
tar zxvf eibnetmux-2.0.1.tar.gz 
cd eibnetmux-2.0.1 
./configure
make 
make install
ldconfig

cd /usr/src/ 
# wget "http://downloads.sourceforge.net/project/eibnetmux/Sample%20client%20applications/1.7.1/eibnetmuxclientsamples-1.7.1.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Feibnetmux%2Ffiles%2FSample%2520client%2520applications%2F1.7.1%2F&amp;ts=1361557458&amp;use_mirror=freefr" -O eibnetmuxclientsamples-1.7.1.tar 
tar xvf eibnetmuxclientsamples-1.7.1.tar 
cd eibnetmuxclientsamples-1.7.1

cp /opt/domovision/src/eibnetmuxclient-domovision/eibtrace/eibtrace.c /opt/domovision/src/eibnetmuxclientsamples-1.7.1/eibtrace/eibtrace.c
<A completer>
<A completer>

./configure 

make 

cp eibcommand/eibcommand /usr/local/bin/
cp eibread/eibread /usr/local/bin/
cp eibtrace/eibtrace /usr/local/bin/

apt-get install php5 php5-cli php5-mysql libapache2-mod-php5 apache2 mysql-server php-pear
pear install -f System_Daemon
pecl install inotify
# You should add "extension=inotify.so" to php.ini

