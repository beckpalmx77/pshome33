RewriteEngine  on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([^\.]+)$ $11.php [NC,L]