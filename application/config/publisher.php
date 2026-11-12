<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| WHO RUNS THE SITE
| -------------------------------------------------------------------------
| Shown in the "Who runs this site" section of the About page. The section only
| appears once publisher_name is filled in; any other value left empty is simply
| left out. Everything here is PUBLIC on the About page, so only add what you are
| comfortable publishing.
|
|   publisher_name      A person's name or a business name.
|   publisher_location  Optional: a city or country, e.g. 'Dublin, Ireland'.
|   publisher_about     Optional: one to three sentences on who you are and why
|                       you built the site (plain text, no HTML).
|   publisher_email     Optional: a public contact address. The contact form is
|                       always linked as well.
*/
$config['publisher_name']     = 'an independent publisher';
$config['publisher_location'] = '';
$config['publisher_about']    = 'Built as an independent project to make Prize Bond results easy to check and search. Results are imported from the official published draws after each weekly draw, and reported corrections are reviewed.';
$config['publisher_email']    = '';
