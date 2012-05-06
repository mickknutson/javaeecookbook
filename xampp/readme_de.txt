###### ApacheFriends XAMPP Version 1.7.7 ######

  + Apache 2.2.21
  + MySQL 5.5.16 (Community Server)
  + PHP 5.3.8 (VC9 X86 32bit thread safe) + PEAR 
  + XAMPP Control Panel Version 2.5 von www.nat32.com
  + (BETA) XAMPP Control Panel Version 3.0.2 von Steffen Strueber (nicht in der USB & lite Version)
    vgl http://www.apachefriends.org/f/viewtopic.php?f=16&t=46743
  + XAMPP Security
  + OpenSSL 1.0.0e
  + phpMyAdmin 3.4.5
  + ADOdb 5.14
  + Mercury Mail Transport System v4.62 (nicht in der USB & lite Version)
  + FileZilla FTP Server 0.9.39 (nicht in der USB & lite Version)
  + Webalizer 2.23-04 (nicht in der USB & lite Version) 
  + Perl 5.10.1 (nicht in der USB & lite Version)
  + Mod_perl 2.0.4 (nicht in der USB & lite Version)
  + Tomcat 7.0.21 (nicht in der USB & lite Version)

--------------------------------------------------------------- 

* System-Voraussetzungen:
  
  + 64 MB RAM (EMPFOHLEN)
  + 350 MB freier Speicherplatz
  + Windows NT, 2000, 2003, XP (EMPFOHLEN), VISTA, Windows 7
  
* ACHTUNG!!!!

Wenn ihr Probleme mit der mysql Verbindung via php bzw. phpMyAdmin (pber die mysqlnd API) habt dann schaut bitte unbedingt hier: 
http://localhost/xampp/index.php


* SCHNELLINSTALLATION:

[HINWEIS: Auf die obersten Hirachie eines beliebigen Laufwerks bzw. auf dem Wechseldatenträger des USB-Sticks entpacken => E:\ oder W:\. Es entsteht E:\xampp oder W:\xampp. Für den USB-Stick nicht die "setup_xampp.bat" nutzen, um ihn auch transportabel nutzen zu können!]

Schritt 1: Das Setup mit der Datei "setup_xampp.bat" im XAMPP-Verzeichnis starten. Bemerkung: XAMPP macht selbst keine Einträge in die Windows Registry und setzt auch keine Systemvariablen.

Schritt 2: Starten Sie den Apache2 mit PHP5.x mit dem Control Panel (xampp-control.exe) oder wahlweise mit => \xampp\apache_start.bat. 
Stoppen Sie den Apache2 mit PHP5.x mit dem Control Panel (xampp-control.exe) oder wahlweise mit => \xampp\apache_stop.bat. 

Schritt 3: Starten Sie MySQL mit dem Control Panel (xampp-control.exe) oder wahlweise mit => \xampp\mysql_start.bat.
Stoppen Sie MySQL mit dem Control Panel (xampp-control.exe) oder wahlweise mit => \xampp\mysql_stop.bat.

Schritt 4: Öffne deinen Browser und gebe http://127.0.0.1 oder http://localhost ein. Danach gelangst du zu den zahlreichen ApacheFriends-Beispielen auf Ihrem lokalen Server.

Schritt 5: Das Root-Verzeichnis (Hauptdokumente) für HTTP (oft HTML) ist => C:\xampp\htdocs. PHP kann die Endungen  *.php, *.php3, *.php4, *.php5, *.phtml haben, *.shtml für SSI, *.cgi für CGI (z. B.: Perl).

Schritt 6: XAMPP DEINSTALLIEREN?
Einfach das "XAMPP"-Verzeichnis löschen. Vorher aber alle Server stoppen 
bzw. als Dienste deinstallieren.

---------------------------------------------------------------

* PASSWÖRTER:

1) MySQL:

   Benutzer: root
   Passwort:
   (also kein Passwort!)

2) FileZilla FTP:

   Benutzer: newuser
   Passwort: wampp 

   Benutzer: anonymous
   Passwort: some@mail.net

3) Mercury: 

   Postmaster: Postmaster (postmaster@localhost)
   Administrator: Admin (admin@localhost)

   TestUser: newuser  
   Passwort: wampp 

4) WEBDAV: 

   Benutzer: xampp-dav-unsecure
   Password: ppmax2011 

---------------------------------------------------------------

* NUR FÜR NT-SYSTEME! (NT4 | Windows 2000 | Windows XP | Windows 2003):

- \xampp\apache\apache_installservice.bat 
  ===> Installiert den Apache 2 als Dienst

- \xampp\apache\apache_uninstallservice.bat 
  ===> Deinstalliert den Apache 2 als Dienst

- \xampp\mysql\mysql_installservice.bat 
  ===> Installiert MySQL als Dienst

- \xampp\mysql\mysql_uninstallservice.bat 
  ===> Deinstalliert MySQL als Dienst

==> Nach allen De- / Installationen der Dienste, System unbedingt neustarten! 

---------------------------------------------------------------

* DAS THEMA SICHERHEIT:

Wie schon an anderer Stelle erwähnt ist XAMPP nicht für den Produktionseinsatz gedacht, sondern nur für Entwickler in Entwicklungsumgebungen. Das hat zur Folge, dass XAMPP absichtlich nicht restriktiv sondern im Gegenteil sehr offen vorkonfiguriert ist. Für einen Entwickler ist das ideal, da er so keine Grenzen vom System vorgeschrieben bekommt.
Für einen Produktionseinsatz ist das allerdings überhaupt nicht geeignet!
Hier eine Liste, der Dinge, die an XAMPP absichtlich (!) unsicher sind:

- Der MySQL-Administrator (root) hat kein Passwort.
- Der MySQL-Daemon ist übers Netzwerk erreichbar.
- phpMyAdmin ist über das Netzwerk erreichbar.
- In dem XAMPP-Demo-Seiten (die man unter http://localhost/ findet) gibt es den Punkt "Sicherheitscheck".
  Dort kann man sich den aktuellen Sicherheitszustand seiner XAMPP-Installation anzeigen lassen.

Will man XAMPP in einem Netzwerk betreiben, so dass der XAMPP-Server auch von anderen erreichbar ist, dann sollte man unbedingt die folgende URL aufrufen, mit dem man diese Unsicherheiten einschränken kann:

	http://localhost/security/

Hier kann das Root-Passwort für MySQL und phpMyAdmin und auch ein Verzeichnisschutz für die 
XAMPP-Seiten eingerichtet werden.

---------------------------------------------------------------

* MYSQL-Hinweise:

(1) Um den MySQL-Daemon zu starten bitte Doppelklick auf \xampp\mysql_start.bat.
Der MySQL Server startet dann im Konsolen-Modus. Das dazu gehörige Konsolenfenster muss offen bleiben (!!) Zum Stop bitte die mysql_stop.bat benutzen!

(2) Wer MySQL als Dienst unter NT / 2000 / XP benutzen möchte, muss unbedingt (!) vorher die "my" bzw."my.ini unter C:\ (also C:\my.ini) implementieren. Danach die "mysql_installservice.bat" im Ordner "mysql" aktivieren. Dienste funktionieren generell NICHT unter Windows Home-Versionen. 

(3) Der MySQL-Server startet ohne Passwort für MySQl-Administrator "root".
Für eine Zugriff in PHP sähe das also aus:

	mysql_connect("localhost", "root", "");

Ein Passwort für "root" könnt ihr über den MySQL-Admin in der Eingabeaufforderung
setzen. Z. B.: 

	C:\xampp\mysql\bin\mysqladmin.exe -u root -p geheim

Wichtig: Nach dem Einsetzen eines neuen Passwortes für Root muss auch phpMyAdmin informiert werden! Das geschieht über die Datei "config.inc.php"; zu finden als C:\xampp\phpmyadmin\config.inc.php. Dort also folgenden 
Zeilen editieren:
   
	$cfg['Servers'][$i]['user']            = 'root';   // MySQL User
	$cfg['Servers'][$i]['auth_type']       = 'http';   // HTTP-Authentifzierung

So wird zuerst das "root"-Passwort vom MySQL-Server abgefragt, bevor phpMyAdmin zugreifen darf.
    
---------------------------------------------------------------	
    
		Have a lot of fun! | Viel Spaß! | Bonne Chance!
