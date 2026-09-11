<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('STATESAVINGS_RESULTS_URL', 'https://www.statesavings.ie/api/recent-results/winners-table');
define('STATESAVINGS_RESULTS_PAGE_URL', 'https://www.statesavings.ie/prize-bonds/results');
define('STATESAVINGS_HOME_URL', 'https://www.statesavings.ie/prize-bonds');
define('STATESAVINGS_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36');

function statesavings_http_get($url)
{
	$ch = curl_init($url);
	curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERAGENT => STATESAVINGS_USER_AGENT,
		CURLOPT_TIMEOUT => 20,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_CAINFO => FCPATH . 'tools/cacert.pem',
	));
	$body = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($body === false || $http_code !== 200) {
		return null;
	}
	return $body;
}

/**
 * Fetch one page of the winners table for a draw date / prize tier.
 * $page is 1-indexed on our side; their API is 0-indexed, so we convert here.
 * Returns the raw HTML fragment, or null on failure.
 */
function statesavings_fetch_page($draw_date, $prize_value, $page)
{
	$url = STATESAVINGS_RESULTS_URL . '?' . http_build_query(array(
		'drawDate' => $draw_date,
		'page' => $page - 1,
		'prizeValue' => $prize_value,
		'location' => 'all',
		'sortBy' => 'prizevaluedesc',
		'search' => '',
	));
	return statesavings_http_get($url);
}

/**
 * Fetch the main results page, which includes the "Show winners from"
 * date-select listing every draw date statesavings.ie currently has
 * results for.
 */
function statesavings_fetch_results_page()
{
	return statesavings_http_get(STATESAVINGS_RESULTS_PAGE_URL);
}

/**
 * True if $html carries the "winners-total-count" field every genuine
 * statesavings.ie winners-table response includes — false for anything else
 * (a bot-challenge interstitial, a maintenance page, a markup change on
 * their end). Every parser below that reads a statesavings_fetch_page()
 * response shares this one check, rather than each re-implementing its own
 * "does this look real" test — see statesavings_parse_total_count()'s
 * history: only it had this check for a while, which meant a soft-blocked
 * response was still silently misread as "genuinely zero" by its sibling
 * parsers (empty tiers taken as proof no draw was held; empty rows taken as
 * proof a tier's last page was reached), with real, non-recoverable data
 * loss as the result — not just the stalled-detection symptom the
 * total-count case produces.
 */
function statesavings_looks_like_results_page($html)
{
	return (bool) preg_match('/winners-total-count"[^>]*value="\d+"/', $html);
}

/**
 * Returns the real winner count, or null if $html doesn't look like a genuine
 * results response at all. Deliberately distinct from returning 0: a missing
 * field means we don't actually know the count, whereas a present field
 * genuinely reporting 0 means "no draw on this date". Callers must treat
 * null as a failed fetch (retry later), not as a confirmed empty result —
 * conflating the two previously let a soft-blocked response get cached as
 * "no draw here", silently stalling auto-detection of the next real draw
 * until someone noticed and flipped auto_import on by hand.
 */
function statesavings_parse_total_count($html)
{
	if (preg_match('/winners-total-count"[^>]*value="(\d+)"/', $html, $m)) {
		return (int) $m[1];
	}
	return null;
}

/**
 * Returns the prize tiers on offer, or null if $html isn't a genuine
 * results page (see statesavings_looks_like_results_page()) — callers must
 * not treat null the same as "genuinely no tiers offered", since the latter
 * is what a real no-draw-held date looks like.
 */
function statesavings_parse_prize_options($html)
{
	if (!statesavings_looks_like_results_page($html)) {
		return null;
	}
	preg_match_all('/<option\s+(?:selected="selected"\s+)?value="([0-9.]+)">/', $html, $m);
	return array_map('floatval', $m[1]);
}

/**
 * Returns the winner rows on this page, or null if $html isn't a genuine
 * results page (see statesavings_looks_like_results_page()) — callers must
 * not treat null the same as "genuinely the end of this tier's pages", since
 * the latter is what a real last page looks like.
 */
function statesavings_parse_rows($html)
{
	if (!statesavings_looks_like_results_page($html)) {
		return null;
	}
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

/**
 * Fetches the main Prize Bonds product page, which carries a "Next Draw"
 * banner — confirmed via a Wayback Machine snapshot from 24 Dec 2024 (7 days
 * ahead of an irregular Christmas/New Year date shift) to correctly reflect
 * statesavings.ie's real internal draw schedule, not a naive weekly-Friday
 * guess. This is the one place they publish a genuinely future draw date.
 */
function statesavings_fetch_home_page()
{
	return statesavings_http_get(STATESAVINGS_HOME_URL);
}

/**
 * Extracts the "Next Draw" date from the homepage HTML (see
 * statesavings_fetch_home_page()) and returns it as Y-m-d, or null if the
 * markup isn't found or doesn't parse as a date.
 */
function statesavings_parse_next_draw_date($html)
{
	if (!preg_match('/m16-draw_details--next-draw date">\s*([^<]+?)\s*</', $html, $m)) {
		return null;
	}
	$timestamp = strtotime(trim($m[1]));
	if ($timestamp === false) {
		return null;
	}

	// Sanity bound, not just a truthy check: the banner text today is always
	// spelled-out ("Friday 4 September 2026"), which strtotime() parses
	// unambiguously — but if it were ever rendered as a numeric DD/MM/YYYY
	// date instead, strtotime() would silently misread it as US MM/DD/YYYY
	// for any day <= 12. A real "next draw" is always within the next few
	// weeks, so reject anything further out as a failed parse rather than
	// trusting a plausible-looking but wrong date.
	$date = date('Y-m-d', $timestamp);
	if ($date < date('Y-m-d') || $date > date('Y-m-d', strtotime('+60 days'))) {
		return null;
	}
	return $date;
}
