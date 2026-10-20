<?php


// Must stay `require`, not `require_once`: these files only register routes (no
// declarations), and PHPUnit boots a fresh Application/Router per test in the same
// process, so `require_once` would silently skip re-registering routes after the
// first test and break every subsequent request with a 404.
require __DIR__.'/../app/User/Infrastructure/Routes/api.php'; // NOSONAR
require __DIR__.'/../app/Task/Infrastructure/Routes/api.php'; // NOSONAR
