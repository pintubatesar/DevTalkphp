Patch #1: Parameterize login query (A05 Injection - SQLi)

Replaces unsafe SQL query execution in login.php with a prepared statement
and bound username and password parameters. Prevents SQL injection through
the login form and avoids exposing database query details in error messages.

Patch #2: Parameterize search query (A05 Injection - SQLi)

Replaces string interpolation in search.php with a prepared statement and
bound LIKE parameters. Prevents SQL injection through the topic search form
and avoids exposing database query details in error messages.
Fixes Lab4 vulnarability #3,4

Patch #3: Prevent profile IDOR

Binds profile bio updates to the authenticated user's session ID instead of
trusting the client-supplied user ID. Removes the tamperable user ID field and
only displays the bio update form on the signed-in user's own profile.
Fixes lab4 vulnarability #2

Patch #4: Prevent cross-site scripting (XSS)

Escapes user-controlled error messages, search terms, topic bodies, and
comment bodies before rendering them in HTML. Prevents reflected and stored
cross-site scripting while preserving the submitted content.
Fixes lab4 vulnarability #5

Patch #5: Prevent CSRF and unsafe state-changing GET requests

Adds session-backed anti-forgery tokens to state-changing forms and validates
them on the server. Converts topic, comment, and logout actions from GET to
POST-only requests, preventing cross-site request forgery for create, edit,
delete, profile, authentication, and logout actions.

Patch #6: Prevent server-side request forgery (SSRF)

Restricts server-side image fetching to public HTTP(S) hosts, rejects
credentials and non-standard ports, blocks private and reserved IP ranges,
does not follow redirects, and only returns resources detected as images.
Fixes lab4 vulnarability #1

Patch #7: Prevent security misconfiguration and exception disclosure

Disables display of PHP errors and startup errors while retaining server-side
error logging. Replaces registration database exception details with a generic
user-facing message and logs database errors for administrator troubleshooting.

Patch #8: Enforce content ownership authorization

Restricts comment deletion to the comment author or an administrator and
hides delete controls from users who do not own the comment. Topic editing and
deletion and profile bio updates remain restricted to the owning user or an
administrator.