<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Are Irish Prize Bonds Worth It?</h1>
		<p class="text-muted">A balanced look at the trade-off Prize Bonds actually offer &mdash; not a recommendation, just the facts laid out plainly.</p>

		<div class="alert alert-info">
			This is general information, not financial advice. It doesn't take your personal circumstances into account.
			For advice tailored to you, speak to a qualified financial advisor.
		</div>

		<h2>The core trade-off</h2>
		<p>
			An ordinary deposit account pays a known interest rate: put in &euro;10,000, and you know roughly what you'll earn.
			Prize Bonds work differently &mdash; you earn <em>nothing</em> in guaranteed interest. Instead, the interest that
			would have been paid across every bond in the country is pooled into a prize fund, and a random selection of bond
			numbers wins it each week. Your capital is never at risk and can be cashed in at any time (subject to the
			<a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">3-month rule</a> on new bonds) &mdash; but in any
			given year, you could win nothing at all, or you could win considerably more than any deposit account would ever pay.
		</p>

		<h2>The tax-free angle</h2>
		<p>
			The one place Prize Bonds have a clear, quantifiable edge is tax. Interest from an ordinary Irish deposit account
			is subject to <strong>Deposit Interest Retention Tax (DIRT) at 33%</strong> &mdash; so for every &euro;100 of
			interest a bank account earns, roughly &euro;33 goes straight to Revenue before you see it. Prize Bond winnings
			are entirely exempt from DIRT, Income Tax, PRSI, and Capital Gains Tax: whatever you win, you keep in full.
			The catch is that this only matters if you actually win something &mdash; a 33% saving on a prize of &euro;0 is
			still &euro;0.
		</p>

		<h2>What the odds really mean</h2>
		<p>
			Odds of any single bond winning a specific prize in a specific week are long &mdash; commonly cited at around
			1 in 141 million for the top weekly prize on a &euro;25 minimum holding. But odds improve with a larger holding,
			and your odds of winning <em>something</em> (including a smaller &euro;75 prize) across a full year of weekly
			draws are meaningfully better than any single-draw figure suggests. See our
			<a href="<?php echo base_url(); ?>stats/odds/">odds calculator</a> for an estimate based on your own holding size,
			and <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the full mechanics.
		</p>

		<?php
			$has_data = !empty($stats) && (int) $stats->draw_count > 0;
		?>
		<?php if ($has_data): ?>
		<h2>What our own tracked data shows</h2>
		<p>
			Prize Bonds have been running since 1957 (see our <a href="<?php echo base_url(); ?>history/">History</a> page
			for that full story), but our own automated draw-by-draw archive is younger &mdash; it grows by itself every
			week as new draws happen. Rather than just repeating official marketing figures, here's what we've actually
			recorded so far from <?php echo number_format($stats->draw_count); ?> draw<?php echo $stats->draw_count == 1 ? '' : 's'; ?>
			on this site, covering <?php echo date('F Y', strtotime($stats->first_date)); ?>
			to <?php echo date('F Y', strtotime($stats->last_date)); ?>:
		</p>
		<div class="stat-grid">
			<div class="stat-tile">
				<div class="value"><?php echo number_format($stats->draw_count); ?></div>
				<div class="label">Draws Tracked</div>
			</div>
			<div class="stat-tile">
				<div class="value">&euro;<?php echo number_format($stats->total_fund); ?></div>
				<div class="label">Total Prize Money Awarded</div>
			</div>
			<div class="stat-tile">
				<div class="value"><?php echo number_format($stats->total_prizes); ?></div>
				<div class="label">Individual Prizes Paid</div>
			</div>
			<div class="stat-tile">
				<div class="value"><?php echo number_format($winner_count); ?></div>
				<div class="label">Unique Winning Bond Numbers</div>
			</div>
		</div>
		<p>
			Even over that relatively short window, real money is clearly going out every single week, spread across
			thousands of individual bond numbers &mdash; not just the headline &euro;100,000 or &euro;500,000 jackpot
			prizes. These figures will keep growing as our archive does; check back for an ever-longer track record.
			Browse the full <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a> or
			<a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a> page to see it draw by draw.
		</p>
		<?php endif; ?>

		<h2>Who tends to find Prize Bonds worthwhile</h2>
		<div class="grid-2">
			<div class="card">
				<h3>Often a good fit</h3>
				<ul>
					<li>Savers who want their capital 100% safe and accessible, with no risk of loss.</li>
					<li>Higher-rate taxpayers, for whom the 33% DIRT saving on any prize is largest in relative terms.</li>
					<li>Anyone holding a large lump sum they don't need guaranteed growth from, who'd enjoy the "flutter" of a chance at a big prize.</li>
					<li>Money already earmarked to sit untouched for years, where occasional small prizes can accumulate over time.</li>
				</ul>
			</div>
			<div class="card">
				<h3>Often a weaker fit</h3>
				<ul>
					<li>Anyone who needs a predictable, guaranteed return &mdash; Prize Bonds can pay nothing in a given year.</li>
					<li>Small holdings near the &euro;25 minimum, where the odds of winning anything in a year are genuinely low.</li>
					<li>Short-term savings you might need back within 3 months of buying, due to the minimum holding period on new bonds.</li>
					<li>Anyone whose priority is maximising average long-run return rather than the chance of a windfall.</li>
				</ul>
			</div>
		</div>

		<h2>Alternatives worth knowing about</h2>
		<p>
			Prize Bonds are one of several Irish State Savings products, and they're the only one built around random prizes.
			If a guaranteed, fixed return matters more to you than the chance of winning, State Savings also offers Savings
			Certificates, Savings Bonds, and Instalment Savings &mdash; each with a set interest rate over a fixed term instead
			of a prize draw. See <a href="https://www.statesavings.ie" target="_blank" rel="noopener">statesavings.ie</a> for
			current rates and terms on those products.
		</p>

		<h2>Read next</h2>
		<p>
			<a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> covers the draw mechanics in full,
			<a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to Buy and Cash In</a> walks through the practical
			steps, and our <a href="<?php echo base_url(); ?>faq/">FAQ</a> answers other common questions.
		</p>

		<p class="text-muted">
			DIRT rate confirmed at 33% (Revenue.ie) and Prize Bond tax treatment, minimum/maximum holdings, and the 3-month
			rule confirmed via Ireland State Savings official help articles (statesavings.ie), accessed <?php echo date('F Y'); ?>.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
