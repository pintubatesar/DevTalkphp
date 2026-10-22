Patch #1: Parameterize login query (A05 Injection - SQLi)

Replaces unsafe SQL query execution in login.php with a prepared statement
and bound username and password parameters. Prevents SQL injection through
the login form and avoids exposing database query details in error messages.

Patch #2: Parameterize search query (A05 Injection - SQLi)

Replaces string interpolation in search.php with a prepared statement and
bound LIKE parameters. Prevents SQL injection through the topic search form
and avoids exposing database query details in error messages.
Fixes Lab4 vulnarability #4

Patch #3: Prevent profile IDOR

Binds profile bio updates to the authenticated user's session ID instead of
trusting the client-supplied user ID. Removes the tamperable user ID field and
only displays the bio update form on the signed-in user's own profile.
