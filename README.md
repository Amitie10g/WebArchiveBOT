#WebArchiveBOT
PHP script to save external links to Internet Archive.

This experimental tool looks for New Files uploaded to Wikimedia Commons (or any Wiki), extract the links, and queries to Internet Archive by Wayback Machine to save them.
Configure a CRON job to run this script periodicaly.

Page with latest 100 files archived at https://tools.wmflabs.org/webarchivebot (tool is currently paused)

What is included:
* /bin The executable and libraries. For security reasons, keep it outside the public_html!
 * WebArchiveBOT.sh The (bootstarp) script. You should use it and fill it with the appopiate data
 * cli.php The The main script
 * class.php The classes library
 
* /public_html The files intended to be exposed to Internet (optional)
 * index.php The PHP/HTML script to display the latest pages parsed and links archived
 * doc/ The documentation generated with phpDocumentator (optional but useful)