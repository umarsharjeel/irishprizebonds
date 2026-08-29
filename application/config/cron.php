<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Secret token required as ?key=... on the /cron/import_results endpoint.
// Keep this out of version control / don't share it publicly.
$config['cron_secret'] = '8005a47f47e3b5ed6282790d69ba4f2a03f0a0f1bce60a1f';

// How many statesavings.ie page requests to make per cron hit.
$config['cron_import_pages_per_run'] = 15;

// Seconds to sleep between each page request.
$config['cron_import_throttle_seconds'] = 3;
