<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo isset($title) ? $title : 'Irish Prize Bonds'; ?></title>
	<meta name="description" content="<?php echo isset($description) ? htmlspecialchars($description) : ''; ?>">
	<meta content="width=device-width, initial-scale=1" name="viewport" />
	<?php
	// current_url() omits the trailing slash, but .htaccess 301-redirects every
	// non-slash URL to the slash version — so the canonical must include it,
	// otherwise it points at a URL that immediately redirects elsewhere.
	$canonical_url = rtrim(current_url(), '/') . '/';
	?>
	<link rel="canonical" href="<?php echo $canonical_url; ?>">
	<link href="<?php echo base_url(); ?>assets/css/site.css?v=<?php echo filemtime(APPPATH . '../assets/css/site.css'); ?>" rel="stylesheet" type="text/css" />

	<meta property="og:type" content="website">
	<meta property="og:site_name" content="Irish Prize Bonds">
	<meta property="og:title" content="<?php echo isset($title) ? htmlspecialchars($title) : 'Irish Prize Bonds'; ?>">
	<meta property="og:description" content="<?php echo isset($description) ? htmlspecialchars($description) : ''; ?>">
	<meta property="og:url" content="<?php echo $canonical_url; ?>">
	<meta property="og:image" content="<?php echo base_url(); ?>android-chrome-512x512.png">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="<?php echo isset($title) ? htmlspecialchars($title) : 'Irish Prize Bonds'; ?>">
	<meta name="twitter:description" content="<?php echo isset($description) ? htmlspecialchars($description) : ''; ?>">
	<meta name="twitter:image" content="<?php echo base_url(); ?>android-chrome-512x512.png">

	<link rel="icon" type="image/svg+xml" href="<?php echo base_url(); ?>assets/images/logo-mark.svg">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>favicon-16x16.png">
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>apple-touch-icon.png">
	<link rel="manifest" href="<?php echo base_url(); ?>site.webmanifest">
	<meta name="theme-color" content="#0B6E4F">

	<script type="application/ld+json">
	<?php echo json_encode(array(
		'@context' => 'https://schema.org',
		'@type' => 'WebSite',
		'name' => 'Irish Prize Bonds',
		'url' => base_url(),
		'description' => 'Independent, unofficial Irish Prize Bond results checker, draw archive, and statistics.',
	), JSON_UNESCAPED_SLASHES); ?>
	</script>
</head>
<body>
	<header class="site-header">
		<div class="container">
			<a href="<?php echo base_url(); ?>" class="brand">
				<img src="<?php echo base_url(); ?>assets/images/logo-mark.svg" alt="" width="28" height="28" class="brand-mark">
				Irish Prize Bonds
			</a>
			<input type="checkbox" id="nav-toggle-checkbox" class="nav-toggle-checkbox">
			<label for="nav-toggle-checkbox" class="nav-toggle" aria-label="Toggle menu">&#9776;</label>
			<nav class="primary-nav">
				<a href="<?php echo base_url(); ?>">Home</a>

				<div class="nav-dropdown">
					<a href="<?php echo base_url(); ?>results/archive/">Results &#9662;</a>
					<div class="nav-dropdown-menu">
						<a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>
						<a href="<?php echo base_url(); ?>schedule/">Draw Dates</a>
						<a href="<?php echo base_url(); ?>search/checker/">Check Numbers</a>
						<a href="<?php echo base_url(); ?>search/power/">Power Search</a>
					</div>
				</div>

				<div class="nav-dropdown">
					<a href="<?php echo base_url(); ?>stats/winners/">Insights &#9662;</a>
					<div class="nav-dropdown-menu">
						<a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a>
						<a href="<?php echo base_url(); ?>stats/counties/">County Stats</a>
						<a href="<?php echo base_url(); ?>stats/odds/">Odds Calculator</a>
					</div>
				</div>

				<div class="nav-dropdown">
					<a href="<?php echo base_url(); ?>how-it-works/">Learn &#9662;</a>
					<div class="nav-dropdown-menu">
						<a href="<?php echo base_url(); ?>how-it-works/">How It Works</a>
						<a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to Buy &amp; Cash In</a>
						<a href="<?php echo base_url(); ?>are-prize-bonds-worth-it/">Are They Worth It?</a>
						<a href="<?php echo base_url(); ?>history/">History</a>
						<a href="<?php echo base_url(); ?>faq/">FAQ</a>
						<a href="<?php echo base_url(); ?>about/">About</a>
						<a href="<?php echo base_url(); ?>contact-us/">Contact Us</a>
					</div>
				</div>
			</nav>
		</div>
	</header>
	<main>
