<?php
/**
 * Fetches Prize Bond winners for a given draw date from statesavings.ie's
 * internal (unauthenticated, undocumented) results widget endpoint, and
 * writes them to a CSV compatible with the dashboard's Import Winners tool
 * (/dashboard/import_winners).
 *
 * By default only pulls the non-bottom prize tiers (the ones worth manually
 * checking) — the lowest tier (typically thousands of small-value winners)
 * is skipped unless --all is passed, since fully pulling it means hundreds
 * of requests against a live third-party endpoint not built for bulk use.
 * Requests are throttled between pages regardless.
 *
 * Usage:
 *   php tools/import_from_statesavings.php 2026-08-21
 *   php tools/import_from_statesavings.php 2026-08-21 --all
 *   php tools/import_from_statesavings.php 2026-08-21 --throttle=1.5
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$args = $argv;
array_shift($args);

$draw_date = null;
$include_all = false;
$throttle = 1.0;

foreach ($args as $arg) {
    if ($arg === '--all') {
        $include_all = true;
    } elseif (preg_match('/^--throttle=([0-9.]+)$/', $arg, $m)) {
        $throttle = (float) $m[1];
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $arg)) {
        $draw_date = $arg;
    }
}

if (!$draw_date) {
    fwrite(STDERR, "Usage: php import_from_statesavings.php YYYY-MM-DD [--all] [--throttle=SECONDS]\n");
    exit(1);
}

define('RESULTS_BASE_URL', 'https://www.statesavings.ie/api/recent-results/winners-table');
define('RESULTS_USER_AGENT', 'IrelandPrizeBondsDashboard/1.0 (data import tool; manual/low-volume use)');

function fetch_page($draw_date, $prize_value, $page)
{
    $url = RESULTS_BASE_URL . '?' . http_build_query(array(
        'drawDate' => $draw_date,
        'page' => $page - 1, // their pager is zero-indexed even though we track pages as 1-indexed internally
        'prizeValue' => $prize_value,
        'location' => 'all',
        'sortBy' => 'prizevaluedesc',
        'search' => '',
    ));

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => RESULTS_USER_AGENT,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CAINFO => __DIR__ . '/cacert.pem',
    ));
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($body === false || $http_code !== 200) {
        fwrite(STDERR, "Request failed (HTTP $http_code) $url : $err\n");
        return null;
    }
    return $body;
}

function parse_total_count($html)
{
    if (preg_match('/winners-total-count"[^>]*value="(\d+)"/', $html, $m)) {
        return (int) $m[1];
    }
    return 0;
}

function parse_prize_options($html)
{
    preg_match_all('/<option\s+(?:selected="selected"\s+)?value="([0-9.]+)">/', $html, $m);
    return array_map('floatval', $m[1]);
}

function parse_rows($html)
{
    $rows = array();
    if (!preg_match_all('/<div class="tr">\s*<div class="td">(.*?)<\/div>\s*<div class="td">(.*?)<\/div>\s*<div class="td">(.*?)<\/div>\s*<\/div>/s', $html, $matches, PREG_SET_ORDER)) {
        return $rows;
    }
    foreach ($matches as $m) {
        $bond_number = trim(strip_tags($m[2]));
        $location = trim(strip_tags($m[3]));
        $rows[] = array($bond_number, $location);
    }
    return $rows;
}

echo "Fetching draw $draw_date from statesavings.ie ...\n";

$first = fetch_page($draw_date, 'all', 1);
if ($first === null) {
    fwrite(STDERR, "Could not reach statesavings.ie. Aborting.\n");
    exit(1);
}

$tiers = parse_prize_options($first);
if (empty($tiers)) {
    fwrite(STDERR, "No prize tiers found for $draw_date - check the date has a published draw.\n");
    exit(1);
}
rsort($tiers);
$bottom_tier = min($tiers);

$tiers_to_fetch = $include_all ? $tiers : array_filter($tiers, function ($t) use ($bottom_tier) { return $t != $bottom_tier; });

echo "Tiers found: " . implode(', ', array_map(function($t){ return number_format($t, 2); }, $tiers)) . "\n";
echo "Fetching: " . implode(', ', array_map(function($t){ return number_format($t, 2); }, $tiers_to_fetch)) . ($include_all ? '' : ' (bottom tier ' . number_format($bottom_tier, 2) . ' skipped - pass --all to include it)') . "\n\n";

$all_rows = array();
$request_count = 0;

foreach ($tiers_to_fetch as $tier) {
    $page = 1;
    $html = fetch_page($draw_date, $tier, $page);
    $request_count++;
    if ($html === null) continue;

    $total = parse_total_count($html);
    $total_pages = max(1, (int) ceil($total / 10));
    echo "Tier " . number_format($tier, 2) . ": $total winner(s) across $total_pages page(s)\n";

    while (true) {
        foreach (parse_rows($html) as $row) {
            $all_rows[] = array($row[0], $tier, $row[1]);
        }
        if ($page >= $total_pages) break;
        $page++;
        usleep((int) ($throttle * 1000000));
        $html = fetch_page($draw_date, $tier, $page);
        $request_count++;
        if ($html === null) break;
    }
    usleep((int) ($throttle * 1000000));
}

if (empty($all_rows)) {
    fwrite(STDERR, "No rows collected. Nothing written.\n");
    exit(1);
}

$out_dir = __DIR__ . '/../database/imports';
if (!is_dir($out_dir)) {
    mkdir($out_dir, 0755, true);
}
$out_file = $out_dir . '/winners_' . $draw_date . ($include_all ? '_all' : '_top_tiers') . '.csv';

$fh = fopen($out_file, 'w');
fputcsv($fh, array('bond_number', 'prize_value', 'location'));
foreach ($all_rows as $row) {
    fputcsv($fh, $row);
}
fclose($fh);

echo "\nDone. $request_count request(s) made, " . count($all_rows) . " row(s) written to:\n$out_file\n";
echo "Upload this file at /dashboard/import_winners for the " . $draw_date . " draw.\n";
