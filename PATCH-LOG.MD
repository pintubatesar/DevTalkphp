Patch #3: Parameterize login query (A05 Injection - SQLi)

Replaces unsafe SQL query execution in login.php with a prepared statement
and bound username and password parameters. Prevents SQL injection through
the login form and avoids exposing database query details in error messages.