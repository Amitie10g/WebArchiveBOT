#WebArchiveBOT
This experimental tool looks for New Files uploaded to Wikimedia Commons (or any Wiki), extract the links, and queries to Internet Archive by Wayback Machine to save them.

What is included:
* /bin The executable and libraries
 * WebArchiveBOT.sh The (bootstarp) script. You should use it and fill it with the appopiate data
 * cli.php The The main script
 * class.php The classes library
 
* /public_html The files intended to be exposed to Internet (optional)
 * index.php The PHP/HTML script to display the latest pages parsed and links archived
 * doc/ The documentation generated with phpDocumentator (optional but useful)