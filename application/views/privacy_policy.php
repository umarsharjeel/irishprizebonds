<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Privacy Policy</h1>
		<p class="text-muted">Last updated: <?php echo date('d F Y'); ?></p>

		<p>
			Irish Prize Bonds ("we", "us") operates this website as an independent, unofficial Prize Bond results
			checker. We are not affiliated with An Post, the Prize Bond Company, or the NTMA, and we don't handle
			bond purchases, redemptions, or prize claims — this policy covers only this website.
		</p>

		<h2>What we collect</h2>
		<p>We keep this to the minimum needed to run the site:</p>
		<ul>
			<li><strong>Contact form submissions</strong> — the name, email address and message you provide when you use our <a href="<?php echo base_url(); ?>contact-us/">Contact Us</a> page, along with your IP address and the time of submission (used for spam prevention).</li>
			<li><strong>Basic technical data</strong> — standard web server logs (IP address, browser type, pages requested). A session cookie is set only on the specific pages that need it (currently just our <a href="<?php echo base_url(); ?>contact-us/">Contact Us</a> form) — most pages on this site set no cookie at all.</li>
			<li><strong>Analytics</strong> — we use Google Analytics (GA4) to understand how the site is used. For visitors in the EEA, UK and Switzerland, analytics storage stays disabled until you make a choice in the consent message shown on your first visit; in other regions analytics runs by default. See "Cookies" below.</li>
			<li><strong>Advertising</strong> — we display ads through Google AdSense. Google and its partners may set cookies to serve and measure ads. In the EEA, UK and Switzerland this happens only with your consent, collected via Google's consent message.</li>
		</ul>
		<p>
			We do <strong>not</strong> collect any information about Prize Bonds you own, your identity as a bondholder,
			or any financial account details — we have no ability to access that information and don't ask for it.
		</p>

		<h2>Cookies</h2>
		<p>We use the following categories of cookies and similar storage:</p>
		<ul>
			<li><strong>Essential</strong> — a session cookie, set only on the pages that genuinely need it (e.g. submitting our Contact Us form), required for those features to work and not disableable without breaking them.</li>
			<li><strong>Analytics</strong> — set by Google Analytics (GA4) to measure aggregate site usage. In the EEA, UK and Switzerland these are not set until you consent through Google's consent message; in other regions they are set by default.</li>
			<li><strong>Advertising</strong> — set by Google AdSense and its partners to serve and measure ads. In the EEA, UK and Switzerland these are used only with your consent via Google's consent message.</li>
		</ul>
		<p>
			Visitors in the EEA, UK and Switzerland are shown a Google-certified consent message on their first visit
			and can review or withdraw that choice at any time from the settings in that message. You can also block or
			delete cookies through your browser settings.
		</p>

		<h2>Why we process this data</h2>
		<ul>
			<li>To respond to messages sent through our Contact Us form (legitimate interest).</li>
			<li>To keep the site secure and functioning correctly (legitimate interest / essential cookies).</li>
			<li>To understand and improve site usage, and to fund the site through advertising — with your consent where it is required (EEA, UK and Switzerland).</li>
		</ul>

		<h2>Who we share data with</h2>
		<p>
			We don't sell or rent your personal data. Usage and advertising data may be processed by Google (Analytics
			and AdSense) under Google's own privacy policy. We may also share data where required by law.
		</p>

		<h2>How long we keep data</h2>
		<p>
			Contact form submissions are kept only as long as needed to address your query and for a reasonable
			period afterward for our records, after which they may be deleted. You can request earlier deletion at
			any time — see "Your rights" below.
		</p>

		<h2>Your rights</h2>
		<p>
			If you're in the EU/EEA, you have rights under the GDPR, including the right to access, correct, or
			request deletion of your personal data, to object to or restrict its processing, and to lodge a complaint
			with a supervisory authority — in Ireland, the
			<a href="https://www.dataprotection.ie" target="_blank" rel="noopener">Data Protection Commission</a>.
			To exercise any of these rights, contact us via our <a href="<?php echo base_url(); ?>contact-us/">Contact Us</a> page.
		</p>
		<p>
			Visitors in the EEA, UK and Switzerland can review or withdraw consent for analytics and advertising
			cookies at any time through the settings in Google's consent message, and can block or clear cookies
			via their browser settings.
		</p>

		<h2>Children's privacy</h2>
		<p>This site is not directed at children, and we don't knowingly collect personal data from children.</p>

		<h2>Changes to this policy</h2>
		<p>We may update this policy from time to time; the "last updated" date above will reflect any changes.</p>

		<h2>Contact</h2>
		<p>Questions about this policy or your data? Reach us via our <a href="<?php echo base_url(); ?>contact-us/">Contact Us</a> page.</p>

		<p class="text-muted">
			This policy is provided in good faith to describe our actual data practices, but is general in nature and
			not a substitute for professional legal advice.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
