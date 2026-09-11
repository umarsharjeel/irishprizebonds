<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>How We Source and Verify Our Draw Results</h1>
		<p class="text-muted">Where every number on this site comes from, how often it's updated, and what happens when something can't be confirmed yet.</p>

		<h2>The source: Ireland State Savings' own published results</h2>
		<p>
			Every draw, prize tier, and winning bond number on this site traces back to Ireland State Savings' own official results system at <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a> &mdash; the same source the Post Office and their own checker use. We don't estimate or guess figures, and we don't take results from any other unofficial checker or results site.
		</p>

		<h2>We only publish a draw once it's genuinely confirmed</h2>
		<p>
			We don't guess draw dates ahead of time on a calendar pattern. Even though draws are normally exactly a week apart, bank holidays and other one-off shifts mean the actual date can move &mdash; so rather than assuming, we check for a newly published draw directly against the official results system, and a date only appears on this site once it's actually live there. The one exception is the "Next Draw" date shown on our <a href="<?php echo base_url(); ?>schedule/">schedule</a> page, which mirrors the date Ireland State Savings themselves publish in advance on their own site.
		</p>

		<h2>How results are imported</h2>
		<p>
			Once a draw is confirmed, we pull the winning bond numbers, prize values, and winner locations tier by tier, matching everything back to the official published data rather than re-keying or summarising it by hand. This runs automatically on a regular check throughout the day, so results typically appear here within hours of being published officially &mdash; though see the note below on occasional delays.
		</p>

		<h2>What we don't do</h2>
		<p>
			We don't publish provisional or estimated figures dressed up as final ones. A draw shown as "pending" on this site simply means it hasn't been fully published by Ireland State Savings yet &mdash; not that we have partial or unverified data sitting behind it. Any manual correction we ever make is checked directly against Ireland State Savings' own published data, never guessed or estimated.
		</p>

		<h2>When an update might lag</h2>
		<p>
			On rare occasions, the official site is briefly unreachable or temporarily blocks automated checks, which can delay a new draw or a location detail appearing here by a few hours, occasionally longer. Our import checks again automatically and normally catches up on its own &mdash; we'd simply rather show nothing for a while than show something we can't confirm is accurate.
		</p>

		<h2>Spot something that looks wrong?</h2>
		<p>
			If a result here doesn't match what you see on the official site, please <a href="<?php echo base_url(); ?>contact-us/">let us know</a> with the draw date and bond number &mdash; we're always looking to improve accuracy, and every report gets checked against the official source directly.
		</p>

		<h2>Read next</h2>
		<p>
			See our <a href="<?php echo base_url(); ?>about/">About</a> page for who runs this site, or <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for how the draw itself works.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
