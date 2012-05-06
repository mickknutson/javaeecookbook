<?php
/**
* Provides the locations of several system and user directories
* independent of the operating system used.
*
* Simpe example:
*     require_once 'System/Folders.php';
*     $sf = new System_Folders();
*     echo $sf->getHome();
*
* If you want the folders to be cached (not re-calculated on every
*  read), use System_Folders_Cached.
*
* PHP version 4
*
* @category System
* @package  System_Folders
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html LGPL
* @version  CVS: $Id: Folders.php 308683 2011-02-25 21:01:44Z cweiske $
* @link     http://pear.php.net/package/System_Folders
*/

require_once 'OS/Guess.php';

if (!defined('SYS_LINUX')) {
    define('SYS_LINUX', 'linux');
    define('SYS_WINDOWS', 'windows');
    define('SYS_MAC', 'darwin');
}

/**
* Provides the locations of several system and user directories
* independent of the operating system used.
*
* If a path does not exist or can't be found (error), NULL is returned.
*
* The class uses both $_ENV and $_SERVER to retrieve the environment
*  paths, as this seems to be different between php4 and 5.
*
* @category System
* @package  System_Folders
* @author   Christian Weiske <cweiske@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html LGPL
* @link     http://pear.php.net/package/System_Folders
*/
class System_Folders
{
    /**
    * The operating system on which
    * we work here
    * Gotten from OS_Guess::getSysname()
    *
    * Values (are lowercase):
    * - windows
    * - linux
    * - darwin
    *
    * Use the SYS_* constants to check it
    *
    * @var    string
    * @access protected
    */
    var $sys = 'unknown';

    /**
    * Known names for the application directory
    * in windows.
    *
    * @var    array
    * @access protected
    */
    var $arAppDataNames = array(
        'Application data',       //english
        'Anwendungsdaten',        //german
        'Toepassingsgegevens',    //dutch
        'Datos de programa',      //spanish
        'Dados de aplicativos',   //portugese
        'Data aplikací',          //czech
        'Programdata',            //norwegian
        'Henkilokohtainen',       //finnish
        'Donnees d\'applications',//french
        'Dati applicazioni',      //italian
        'Dane aplikacji',         //polish
    );


    /**
    * Known names for the my documents directory
    * on windows.
    *
    * @var    array
    * @access protected
    */
    var $arDocumentsWindows = array(
        'My Documents',   //english
        'Own Files',      //english?
        'Eigene Dateien', //german
        'Documenti',      //italian
    );


    /**
    * Known names for the my Desktop directory.
    *
    * @var    array
    * @access protected
    */
    var $arDesktop = array(
        'Desktop' //english, german, italian
    );



    /**
    * Known names for the my documents directory
    * on linux and mac.
    *
    * @var    array
    * @access protected
    */
    var $arDocumentsLinux = array(
        'Documents',    //english
        'Dokumente',    //german
        'Documenti',    //italian
    );


    /**
    * Known paths for the documents and settings directory
    * on windows.
    *
    * @var    array
    * @access protected
    */
    var $arDocsAndSettings = array(
        'C:\\Documents and Settings\\',     //english, italian
        'C:\\Dokumente und Einstellungen\\' //german
    );


    /**
    * Known paths for the programs directory on windows.
    *
    * @var    array
    * @access protected
    */
    var $arProgramsWindows = array(
        'C:\\Program Files\\',
        'C:\\Programs\\',
        'C:\\Programme\\',     //german
        'C:\\Programmi\\',     //italian
    );

    /**
    * Known names for the shared documents directory
    * on windows.
    * Although the explorer shows "Shared documents"
    * or "Gemeinsame Dokumente", the *real* directory
    * is only one of the ones here.
    *
    * @var    array
    * @access protected
    */
    var $arSharedDocumentsWindows = array(
        'Documents', //english
        'Dokumente', //german
        'Documenti', //italian
    );

    /**
    * Known paths for the windows directory.
    *
    * @var    array
    * @access protected
    */
    var $arWindowsDirs = array(
        'C:\\WINDOWS\\',
        'C:\\WINNT\\',
        'C:\\WIN98\\',
        'C:\\WIN95\\',
        'C:\\WIN2000\\',
        'C:\\WIN2K\\',
        'C:\\WINXP\\'
    );

    /**
    * COM object used in Windows to get
    * special folder locations via SpecialFolders()
    * described in
    * @link http://msdn.microsoft.com/library/default.asp?url=/library/en-us/script56/html/14761fa3-19be-4742-9f91-23b48cd9228f.asp
    *
    * Available folders:
    * AllUsersDesktop
    * AllUsersStartMenu
    * AllUsersPrograms
    * AllUsersStartup
    * Desktop
    * Favorites
    * Fonts
    * MyDocuments
    * NetHood
    * PrintHood
    * Programs
    * Recent
    * SendTo
    * StartMenu
    * Startup
    * Templates
    *
    * If this variable is NULL, it hasn't been created yet.
    * If it is FALSE, it cannot be created and used (e.g.
    *     because COM is not available)
    *
    * @var    COM
    * @access protected
    */
    var $objCom = null;



    /**
    * Constructor; initializes the system variable.
    */
    function System_Folders()
    {
        $og = new OS_Guess();
        $this->sys = $og->getSysname();
    }//function System_Folders()



    /**
    * Adds a trailing slash to the given path if there is none.
    * Uses DIRECTORY_SEPARATOR, so it works with windows and *nix.
    *
    * @param string $strPath The path
    *
    * @return string The path with a trailing slash
    *
    * @access protected
    */
    function addTrailingSlash($strPath)
    {
        if ($strPath === null) {
            return $strPath;
        }
        if (substr($strPath, -1) !== DIRECTORY_SEPARATOR) {
            $strPath .= DIRECTORY_SEPARATOR;
        }
        return $strPath;
    }//function addTrailingSlash($strPath)



    /**
    * Directories in windows environment variables sometimes
    * have a double backslash, and this needs to be fixed.
    *
    * @param string $strPath The path
    *
    * @return string The fixed path
    *
    * @access protected
    */
    function fixWindowsPath($strPath)
    {
        if ($strPath === null) {
            return null;
        }
        return str_replace('\\\\', '\\', $strPath);
    }//function fixWindowsPath($strPath)



    /**
    * Loops through a list of given paths and checks
    * which of them are correct.
    *
    * @param array  $arPaths   Array with paths to test
    * @param string $strBase   Base directory that shall be prepended to all paths
    * @param string $strSuffix String appended to the directory path
    *
    * @return string The directory that exists. NULL if none of them matched.
    *
    * @access protected
    * @static
    */
    function tryPaths($arPaths, $strBase = '', $strSuffix = '')
    {
        foreach ($arPaths as $strName) {
            $strTmp = $strBase . $strName . $strSuffix;
            if (file_exists($strTmp) && is_dir($strTmp)) {
                return $strTmp;
            }
        }

        return null;
    }//function tryPaths($arPaths, $strBase = '', $strSuffix = '')



    /**
    * Loads the COM object into the $objCom variable.
    *
    * @return boolean True if it could be loaded, false if not
    *
    * @access protected
    */
    function loadCOM()
    {
        //prevent double-loading
        if ($this->objCom !== null) {
            return $this->objCom !== false;
        }

        if (!class_exists('COM')) {
            $this->objCom = false;
            return false;
        }
        $this->objCom = new COM('WScript.Shell');
        if (!$this->objCom) {
            $this->objCom = false;
            return false;
        }
        return true;
    }//function loadCOM()



    /**
    * Loads a windows path via COM using $objCom.
    *
    * @param string $strType See $objCom for allowed values.
    *
    * @return mixed False if no path could be obtained, string otherwise
    *
    * @access protected
    */
    function getCOMPath($strType)
    {
        if (!$this->loadCOM()) {
            return false;
        }
        $strPath = $this->objCom->SpecialFolders($strType);
        if (!$strPath || $strPath == '') {
            return false;
        } else {
            return $strPath;
        }
    }//function getCOMPath($strType)



    /**
    * Returns the All Users directory.
    * Works on windows only, returns NULL if not found.
    *
    * @return string The all users directory
    *
    * @access public
    */
    function getAllUsers()
    {
        if ($this->sys == SYS_WINDOWS) {
            $arEnv = $_SERVER + $_ENV;
            if (isset($arEnv['ALLUSERSPROFILE'])
                && is_dir($arEnv['ALLUSERSPROFILE'])
            ) {
                return $this->addTrailingSlash($arEnv['ALLUSERSPROFILE']);
            } else {
                $strDocsAndSettings = System_Folders::tryPaths(
                    $this->arDocsAndSettings
                );
                if ($strDocsAndSettings !== null) {
                    $strAll = $strDocsAndSettings . 'All Users';
                    if (is_dir($strAll)) {
                        return $this->addTrailingSlash($strAll);
                    }
                }
            }
        }
        return null;
    }//function getAllUsers()



    /**
    * Returns the path to the application data directory.
    * This is the directory in which applications save their
    * settings.
    *
    * On Windows, this is an own directory called "Application data",
    * on *nix, the home directory is used.
    * MacOS X has two application settings directories:
    *  - $HOME/Library/Preferences/<pref_file> for normal settings
    *  - $HOME/Library/Application Support/<app_name>/ for data files that
    *         that the application needs to store
    * This method returns the latter, as it works for both storing just
    *  prefs and files in an application specific subdir. That's not 100%
    *  correct for apple, but it will work.
    *
    * @return string  The application data directory
    *
    * @access public
    */
    function getAppData()
    {
        $strAppData = null;
        if ($this->sys == SYS_WINDOWS) {
            /**
            *   win2k/xp: user_dir/names
            *   win98: C:\windows\Anwendungsdaten|appdata
            */
            $strHome = $this->getHome();
            $strAppData = System_Folders::tryPaths($this->arAppDataNames, $strHome);
            if ($strAppData == null) {
                /**
                *   We didn't find it in the user directory,
                *   so check the windows dir - win9x had the
                *   data there
                */
                $strWindows = $this->getWindows();
                $strAppData = System_Folders::tryPaths(
                    $this->arAppDataNames, $strWindows
                );
            }//appdata still null
        } else {
            $strAppData = $this->getHome();
        }

        return $this->addTrailingSlash($strAppData);
    }//function getAppData()



    /**
    * Returns the path to the user's desktop.
    *
    * @return string The user's desktop
    *
    * @access public
    */
    function getDesktop()
    {
        $strDesktop   = null;

        if ($this->sys == SYS_WINDOWS) {
            $strDesktop = $this->getCOMPath('Desktop');
            if ($strDesktop !== false && file_exists($strDesktop)) {
                return $this->addTrailingSlash($this->fixWindowsPath($strDesktop));
            }
        }

        $strHome      = $this->getHome();
        if ($strHome === null) {
            return null;
        }

        $strDesktop = System_Folders::tryPaths($this->arDesktop, $strHome);

        return $this->addTrailingSlash($strDesktop);
    }//function getDesktop()



    /**
    * Returns the path to the user's documents directory.
    * (normally below the home folder)
    *
    * @return string The "documents" directory
    *
    * @access public
    */
    function getDocuments()
    {
        $strDocuments = null;

        if ($this->sys == SYS_WINDOWS) {
            $strDocuments = $this->getCOMPath('MyDocuments');
            if ($strDocuments !== false && file_exists($strDocuments)) {
                return $this->addTrailingSlash($this->fixWindowsPath($strDocuments));
            }
            $arKnownNames = $this->arDocumentsWindows;
        } else {
            $arKnownNames = $this->arDocumentsLinux;
        }

        $strHome = $this->getHome();
        if ($strHome === null) {
            return null;
        }
        $strDocuments = System_Folders::tryPaths($arKnownNames, $strHome);

        return $this->addTrailingSlash($strDocuments);
    }//function getDocuments()



    /**
    * Returns the path to the user's home directory.
    *
    * @return string The user's home directory
    *
    * @access public
    */
    function getHome()
    {
        $strHome = null;
        if ($this->sys == SYS_LINUX) {
            if (isset($_ENV['HOME'])) {
                //environment variable set
                $strHome = $_ENV['HOME'];
            } else {
                //env not set, so try the default directory
                $strUser = $this->getUserName();
                if ($strUser == 'root') {
                    $strHome = '/root';
                } else if ($strUser !== null) {
                    $strHome = '/home/' . $strUser;
                }
            }
        } else if ($this->sys == SYS_MAC) {
            if (isset($_ENV['HOME'])) {
                //environment variable set
                $strHome = $_ENV['HOME'];
            } else {
                //env not set, so try the default directory
                $strUser = $this->getUserName();
                if ($strUser !== null) {
                    $strHome = '/Users/' . $strUser;
                }
            }
        } else if ($this->sys == SYS_WINDOWS) {
            $arEnv = $_SERVER + $_ENV;
            if (isset($arEnv['USERPROFILE'])) {
                $strHome = $arEnv['USERPROFILE'];
            } else if (isset($arEnv['HOMEPATH']) && isset($arEnv['HOMEDRIVE'])) {
                $strHome = $arEnv['HOMEDRIVE'] . $arEnv['HOMEPATH'];
            } else {
                //guess it...
                $strUser = $this->getUserName();
                if ($strUser !== null) {
                    //It seems as if the german version of windows is the only
                    //one that translated "docs and settings". All other languages
                    //use the english name
                    $strHome = System_Folders::tryPaths(
                        $this->arDocsAndSettings
                    ) . $strUser;
                }
            }
            $strHome = $this->fixWindowsPath($strHome);
        }//windows

        return $this->addTrailingSlash($strHome);
    }//function getHome()



    /**
    * Returns the path to the programs directory.
    * This is the dir where all programs are installed
    * normally.
    *
    * On windows, it's mostly "C:\Programs\", on linux,
    * the /opt/ directory is returned.
    *
    * @return string The programs directory
    *
    * @access public
    */
    function getPrograms()
    {
        $strPrograms = null;

        if ($this->sys == SYS_LINUX) {
            if (file_exists('/opt/') && is_dir('/opt/')) {
                $strPrograms = '/opt/';
            }
        } else if ($this->sys == SYS_MAC) {
            $strPrograms = '/Applications/';
        } else if ($this->sys == SYS_WINDOWS) {
            $strPrograms = $this->getCOMPath('Programs');
            if ($strPrograms === false || !file_exists($strPrograms)) {
                $arEnv = $_SERVER + $_ENV;
                if (isset($arEnv['ProgramFiles'])) {
                    $strPrograms = $arEnv['ProgramFiles'];
                } else {
                    //guess it
                    $strPrograms = System_Folders::tryPaths(
                        $this->arProgramsWindows
                    );
                }//guess it
            }
            $strPrograms = $this->fixWindowsPath($strPrograms);
        }//windows

        return $this->addTrailingSlash($strPrograms);
    }//function getPrograms()



    /**
    * Returns the path to the directory for temporary files.
    *
    * @return string The temporary directory
    *
    * @access public
    */
    function getTemp()
    {
        $strTemp = null;

        if ($this->sys == SYS_LINUX || $this->sys == SYS_MAC) {
            if (file_exists('/tmp/') && is_dir('/tmp/')) {
                $strTemp = '/tmp/';
            }
        } else if ($this->sys == SYS_WINDOWS) {
            $arEnv = $_SERVER + $_ENV;
            if (isset($arEnv['TEMP'])) {
                $strTemp = $arEnv['TEMP'];
            } else if (isset($arEnv['TMP'])) {
                $strTemp = $arEnv['TMP'];
            } else {
                //guess it
                $strTemp = System_Folders::tryPaths(
                    $this->arWindowsDirs, '', '\\Temp'
                );
            }//no env variable
            $strTemp = $this->fixWindowsPath($strTemp);
        }//windows

        return $this->addTrailingSlash($strTemp);
    }//function getTemp()



    /**
    * Returns the path to the shared documents directory.
    *
    * Supports windows only (at least for now) as no other
    * operating system seems to have such a folder.
    * Returns NULL on failure (not windows or not found).
    *
    * @return string The shared documents dir
    *
    * @access public
    */
    function getSharedDocuments()
    {
        $strShared = null;
        if ($this->sys == SYS_WINDOWS) {
            $strAll = $this->getAllUsers();
            if ($strAll !== null) {
                $strShared = System_Folders::tryPaths(
                    $this->arSharedDocumentsWindows, $strAll
                );
            }
        }

        return $this->addTrailingSlash($strShared);
    }//function getSharedDocuments()



    /**
    * Returns the name of the guesses system.
    * Can be compared with SYS_* constants.
    *
    * @return string The detected system
    *
    * @access public
    */
    function getSys()
    {
        return $this->sys;
    }//function getSys()



    /**
    * Returns the name for the user
    * under which name the program runs.
    *
    * This function returns the *system user name*,
    * not the name with forename and surname
    * On unix, this would be e.g. 'fbar' or so for
    * the user 'Foo Bar'
    *
    * This method is used my most other methods, so
    * recognizing the user name is really important.
    * Be sure to check this method if the others fail.
    *
    * @return string The user name
    *
    * @access public
    */
    function getUserName()
    {
        $strUser = null;
        if ($this->sys == SYS_LINUX
            || $this->sys == SYS_MAC
        ) {
            if (isset($_ENV['USER'])) {
                $strUser = $_ENV['USER'];
            } else {
                $strUser = trim(`whoami`);
            }
        } else if ($this->sys == SYS_WINDOWS) {
            $arEnv = $_SERVER + $_ENV;
            if (isset($arEnv['USERNAME'])) {
                $strUser = $arEnv['USERNAME'];
            }
        }
        return $strUser;
    }//function getUserName()



    /**
    * Returns the windows directory (if any).
    * NULL is returned if the system is not Windows.
    *
    * @return string The windows directory, NULL if not on windows
    *
    * @access public
    */
    function getWindows()
    {
        if ($this->sys != SYS_WINDOWS) {
            return null;
        }

        $strWindows = null;
        $arEnv = $_SERVER + $_ENV;
        if (isset($arEnv['SystemRoot'])) {
            $strWindows = $arEnv['SystemRoot'];
        } else if (isset($arEnv['windir'])) {
            $strWindows = $arEnv['windir'];
        } else {
            $strWindows = System_Folders::tryPaths($this->arWindowsDirs);
        }//no env variable

        return $this->addTrailingSlash($this->fixWindowsPath($strWindows));
    }//function getWindows()

}//class System_Folders
?>
