<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>About Irish Prize Bonds</h1>
		<p>
			Irish Prize Bonds is an independent results checker and archive for Ireland's weekly Prize Bond draws.
			The official site shows you one draw at a time and moves on &mdash; we built this because there wasn't
			anywhere to see the bigger picture: every draw we hold in one searchable place, a history you can browse
			back through, and patterns &mdash; which counties win most, what your real odds look like &mdash; that
			only become visible once you're looking across many draws rather than just this week's.
		</p>

		<?php if ($publisher['name'] !== ''): ?>
			<h2>Who runs this site</h2>
			<p>
				Irish Prize Bonds is run by <strong><?php echo htmlspecialchars($publisher['name']); ?></strong><?php echo $publisher['location'] !== '' ? ', ' . htmlspecialchars($publisher['location']) : ''; ?>.
				<?php if ($publisher['about'] !== ''): ?>
					<?php echo nl2br(htmlspecialchars($publisher['about'])); ?>
				<?php endif; ?>
			</p>
			<p>
				You can reach us through the <a href="<?php echo base_url(); ?>contact-us/">contact form</a><?php if ($publisher['email'] !== ''): ?>
				or at <a href="mailto:<?php echo htmlspecialchars($publisher['email']); ?>"><?php echo htmlspecialchars($publisher['email']); ?></a><?php endif; ?>.
			</p>
		<?php endif; ?>

		<h2>What you'll find here</h2>
		<ul>
			<li><strong><a href="<?php echo base_url(); ?>search/checker/">Check Numbers</a> and <a href="<?php echo base_url(); ?>search/power/">Power Search</a></strong> &mdash; check a handful of bond numbers, or paste in an entire holding, against every draw we've recorded.</li>
			<li><strong><a href="<?php echo base_url(); ?>results/">Results</a> and the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a></strong> &mdash; every draw we track, individually, with the full prize breakdown and winning bond numbers.</li>
			<li><strong><a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a>, <a href="<?php echo base_url(); ?>stats/counties/">County Stats</a>, <a href="<?php echo base_url(); ?>stats/prize-fund/">Prize Fund Trends</a> and the <a href="<?php echo base_url(); ?>stats/odds/">Odds Calculator</a></strong> &mdash; the aggregate view the official site doesn't offer.</li>
			<li><strong><a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a>, our <a href="<?php echo base_url(); ?>glossary/">Glossary</a>, and other guides</strong> &mdash; plain-English explanations of the tiers, tax treatment, buying and cashing in, and what happens to Prize Bonds you inherit or bought for a child.</li>
		</ul>

		<?php if ($facts && (int) $facts->draws > 0): ?>
			<h2>The site in numbers</h2>
			<p>
				Launched in August 2026. Today the archive holds <strong><?php echo number_format($facts->draws); ?> draws</strong>,
				from <?php echo date('j F Y', strtotime($facts->first_date)); ?> to <?php echo date('j F Y', strtotime($facts->last_date)); ?>,
				with <strong><?php echo number_format($facts->prizes); ?> winning bond numbers</strong> published and searchable.
				Each new draw is added after it has been fully published officially &mdash; see
				<a href="<?php echo base_url(); ?>how-we-source-our-data/">how we source and verify our draw results</a>.
			</p>
		<?php endif; ?>

		<h2>Accuracy and corrections</h2>
		<p>
			Results come from Ireland State Savings' own published draw results. We don't publish estimated or provisional
			figures: a draw shows as pending until it has been fully published officially. The full process is on
			<a href="<?php echo base_url(); ?>how-we-source-our-data/">How We Source and Verify Our Draw Results</a>.
		</p>
		<p>
			If a result here differs from the official one, or something looks wrong, please
			<a href="<?php echo base_url(); ?>contact-us/">tell us</a> with the draw date and, where relevant, the bond number.
			We check it against the official data and correct confirmed errors.
		</p>
		<p>
			The analysis on this site &mdash; the comparisons on each draw page, the county statistics and the prize fund trends &mdash;
			is calculated by us from the draws we track, and says so where it appears. The odds calculator is different: it uses
			reference figures from published press coverage and gives estimates, not official figures.
		</p>

		<h2>Independent and free to use</h2>
		<p>
			Irish Prize Bonds is free to use and is not paid for or influenced by An Post, the Prize Bond Company, the NTMA or
			Ireland State Savings. If advertising or sponsorship is ever added, it will be clearly labelled and kept separate from
			the results.
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
