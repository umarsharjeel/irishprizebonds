<?php
/**
 * Third-party <head> scripts. Included from website/header.php.
 *
 * Google Analytics (GA4). Consent for EEA/UK/Switzerland visitors is handled by
 * Google's certified CMP (AdSense > Privacy & messaging), which drives Google
 * Consent Mode automatically. The `consent default` block denies storage in
 * those regions until the CMP records a choice; everywhere else GA runs
 * normally with no cookie banner.
 */
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-0L79QEV5GT"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('consent', 'default', {
		ad_storage: 'denied',
		analytics_storage: 'denied',
		ad_user_data: 'denied',
		ad_personalization: 'denied',
		region: ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO','GB','CH'],
		wait_for_update: 500
	});
	gtag('js', new Date());
	gtag('config', 'G-0L79QEV5GT');
</script>
