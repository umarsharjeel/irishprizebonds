<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('STATESAVINGS_RESULTS_URL', 'https://www.statesavings.ie/api/recent-results/winners-table');
define('STATESAVINGS_RESULTS_PAGE_URL', 'https://www.statesavings.ie/prize-bonds/results');
define('STATESAVINGS_USER_AGENT', 'IrelandPrizeBondsDashboard/1.0 (data import tool; low-volume automated use)');

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

function statesavings_parse_total_count($html)
{
	if (preg_match('/winners-total-count"[^>]*value="(\d+)"/', $html, $m)) {
		return (int) $m[1];
	}
	return 0;
}

function statesavings_parse_prize_options($html)
{
	preg_match_all('/<option\s+(?:selected="selected"\s+)?value="([0-9.]+)">/', $html, $m);
	return array_map('floatval', $m[1]);
}

function statesavings_parse_rows($html)
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
