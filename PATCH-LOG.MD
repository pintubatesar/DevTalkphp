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

Yes. DevTalk's terminal received `GET /api/me`, and it returned **401 Unauthorized**.
CORS controls whether a browser exposes the response to JavaScript from another
origin; it does not prevent the HTTP request from reaching the server or replace
authentication and authorization.

## Lab 8, Part B: Session cookies and JWT credentials

### Session cookie

The `PHPSESSID` cookie for `http://localhost:8000` is **HttpOnly: true**,
**Secure: false**, and **SameSite: Lax**. `document.cookie` does not contain
`PHPSESSID` because the HttpOnly flag prevents JavaScript on the page from
reading it.

The Messages sign-in stores the API JWT in `localStorage` under
`devtalk_token`. `signIn()` stores the token returned by `POST /api/login`, and
`api()` reads it and sends it as `Authorization: Bearer <token>`.

The JWT header contains `alg: HS256` and `typ: JWT`. The payload contains:

- `iss`: the issuer, `devtalk`
- `sub`: the numeric user ID, `2` for Alice
- `username`: Alice's account name
- `email`: Alice's email address
- `role`: Alice's application role (`user`)
- `iat`: the issuance time
- `exp`: the expiration time

`sub`, `username`, and `email` are personal or identifying information. The
observed token was valid for 28,800 seconds (8 hours), from `iat` to `exp`.
The payload is only base64url encoded, not encrypted, so anyone holding the
token can read its claims. A third-party JWT decoder should not receive a real
token because it is an authentication bearer credential: pasted data could be
logged or retained, and a promise that decoding happens in-browser does not
make the token safe to disclose.

Changing the payload's `role` to `admin` produced the API response:
`{"error":"Invalid or expired token."}`. The server rejected the changed
payload at the signature comparison in [src/api/jwt.php](/C:/Users/pintu/Downloads/DevTalkphp/src/api/jwt.php:64),
where `hash_equals()` verifies that the signature matches the header and payload.

For the XSS credential comparison, `localStorage.getItem('devtalk_token')`
exposes the JWT to injected JavaScript, while `document.cookie` does not expose
the HttpOnly `PHPSESSID`. A copied token successfully called
`GET /api/messages` from the terminal with only the bearer header and no cookie.
After **Sign out of Messages** removed the token from `localStorage`, the same
copied token still returned Alice's messages. Browser sign-out only removes the
client copy: the stateless JWT has no server-side revocation and remains valid
until its `exp` time.