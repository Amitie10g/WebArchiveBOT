# WebArchiveBOT
PHP script to save external links to Internet Archive using the MediaWiki and Internet Archive Wayback Machine API.

This experimental tool looks for New Files uploaded to Wikimedia Commons (or any Wiki), extract the external links, and queries to Internet Archive by Wayback Machine to save them. This tools runs continuously as a daemon, and is monitored by Bigbrother.

Page with latest 50 files archived at https://tools.wmflabs.org/webarchivebot

## Instalaltion: 

* Run `git clone` to download the files:

    `git clone https://github.com/Amitie10g/WebArchiveBOT.git`

  For Hack version:

    `git clone -Hack https://github.com/Amitie10g/WebArchiveBOT.git`

* Copy "bin/config.php" to your scripts path (inside your home directory, usually $HOME/bin) and edit it.
* Copy "bin/WebArchiveBOT.sh" to your scripts path and edit it if necessary 
* Symlink "bin/main.php" and "bin/class.php" to your scripts path.

* Copy "www/.config.php" to your www directory and edit it.
* Symlink "www/index.php" and "www/doc" (optional) to your www directory.

* Ensure the `WebArchiveBOT.sh` has exec permissions.

* Ensure the DB path is properly set in both the frontend and backend configuration (absolute path!!!)
  
## Running

Just execute `WebArchiveBOT.sh`.

### Running in HHVM

Use the Hack version instead.

### Running in Windows

To run the backend script in Windows, create a batch script to execute php-cli calling the script:

    @echo off
    php main.php

## License

This program is licensed under the GNU Affero General Public License version 3. Contains parts of the Chris G's Bot classes library, licensed originally under the GNU General Public License version 2.
