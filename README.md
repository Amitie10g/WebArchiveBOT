# WebArchiveBOT
PHP script to save external links to Internet Archive using the MediaWiki and Internet Archive Wayback Machine API, thanks to the Chris G's botclasses library.

This experimental tool looks for New Files uploaded to Wikimedia Commons (or any Wiki), extract the external links, and queries to Internet Archive by Wayback Machine to save them. This tool runs continuously as a daemon, and is monitored by Bigbrother in ToolForge.

Page with latest 50 files archived at https://tools.wmflabs.org/webarchivebot

## What's new

* Added support for Kubernetes.
* Dropped support for Postgres and SQLITE (however, PDO is still used).
* File search by the File ID instead of the title, for faster retriving from the DB (the file ID is got by querying MediaWiki).
* Title and URLs are now stored in VARCHAR and TEXT (JSON) format respectively.

## Requirements

* PHP 7.0 with PDO enabled, including the drivers to be used.

## Installation: 

* Run `git clone` to download the files:

    `git clone https://github.com/Amitie10g/WebArchiveBOT.git`

* Copy "bin/config.php" to your scripts path (inside your home directory, usually $HOME/bin) and edit it.
* Symlink "bin/main.php", "bin/class.php" and "bin/WebArchiveBOT.sh" to your scripts path.

* Copy "www/.config.php" to your www directory and edit it.
* Symlink "www/index.php" and "www/doc" (optional) to your www directory.

* Ensure the `WebArchiveBOT.sh` has exec permissions.

* Ensure the DB path is properly set in both the frontend and backend configuration (absolute path!!!) (when using SQLite).
  
## Running

To run the bot (for testing): `./WebArchiveBOT.sh`

To deploy the container and run inside it: `./WebArchiveBOT.sh <docker_image_location>` 

## License

This program is licensed under the GNU Affero General Public License version 3. Contains parts of the Chris G's botlasses library, licensed originally under the GNU General Public License version 2.
