<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>About Irish Prize Bonds</h1>
		<p>
			Irish Prize Bonds is an independent results checker and archive for Ireland's weekly Prize Bond draws.
			The official site shows you one draw at a time and moves on &mdash; we built this because there wasn't
			anywhere to see the bigger picture: every draw in one searchable place, a full history you can browse
			back through, and patterns &mdash; which counties win most, what your real odds look like &mdash; that
			only become visible once you're looking across many draws rather than just this week's.
		</p>

		<h2>What you'll find here</h2>
		<ul>
			<li><strong><a href="<?php echo base_url(); ?>search/checker/">Check Numbers</a> and <a href="<?php echo base_url(); ?>search/power/">Power Search</a></strong> &mdash; check a handful of bond numbers, or paste in an entire holding, against every draw we've recorded.</li>
			<li><strong><a href="<?php echo base_url(); ?>results/">Results</a> and the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a></strong> &mdash; every draw we track, individually, with the full prize breakdown and winning bond numbers.</li>
			<li><strong><a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a>, <a href="<?php echo base_url(); ?>stats/counties/">County Stats</a> and the <a href="<?php echo base_url(); ?>stats/odds/">Odds Calculator</a></strong> &mdash; the aggregate view the official site doesn't offer.</li>
			<li><strong><a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a>, our <a href="<?php echo base_url(); ?>glossary/">Glossary</a>, and other guides</strong> &mdash; plain-English explanations of the tiers, tax treatment, buying and cashing in, and what happens to Prize Bonds you inherit or bought for a child.</li>
		</ul>

		<h2>Our commitment to accuracy</h2>
		<p>
			Have feedback or spot an error in our data? We're always looking to improve accuracy — results are sourced
			from publicly available draw information. See <a href="<?php echo base_url(); ?>how-we-source-our-data/">How We Source and Verify Our Draw Results</a>
			for exactly how that works, or <a href="<?php echo base_url(); ?>contact-us/">get in touch</a> if something looks wrong.
		</p>

		<h2>Not the official site</h2>
		<p>
			<strong>We are not affiliated with, endorsed by, or connected to An Post, the Prize Bond Company, or the NTMA.</strong>
			We don't sell Prize Bonds and we don't process claims. For buying, cashing in, or claiming a prize, use the
			official site at <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a>.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
