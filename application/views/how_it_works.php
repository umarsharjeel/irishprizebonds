<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>How Irish Prize Bonds Work</h1>
		<p class="text-muted">A plain-English guide — how prizes are decided, what the tiers are, and what to expect.</p>

		<h2>The basics</h2>
		<p>
			A Prize Bond is a savings product issued by the Prize Bond Company on behalf of the Irish government (via the NTMA and An Post).
			Instead of earning interest, every bond you hold is automatically entered into a weekly draw for a chance to win a tax-free cash prize.
			You can buy from as little as &euro;25 (4 bonds) up to a maximum of &euro;250,000 (40,000 bonds) per person, and your original money
			stays yours &mdash; you can cash bonds in at any time, whether or not they've ever won, subject to one rule: newly purchased bonds
			must be held for a <strong>minimum of 3 months (90 days)</strong> before they can be cashed in. That waiting period applies to
			encashment only, not to draw entry, and it doesn't apply to bonds you receive automatically through reinvested prize winnings.
			See our <a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to Buy and Cash In</a> guide for the full process.
		</p>

		<h2>The weekly draw</h2>
		<p>A draw is held every week — see the full <a href="<?php echo base_url(); ?>schedule/">draw schedule</a> for upcoming dates. Since the prize fund was increased with effect from 1 September 2026, the prize tiers in a typical week are:</p>
		<div class="table-wrap" style="max-width:480px;">
			<table class="data-table">
				<thead><tr><th>Prize</th><th>Number of Winners</th></tr></thead>
				<tbody>
					<tr><td>&euro;100,000</td><td>1</td></tr>
					<tr><td>&euro;1,000</td><td>50</td></tr>
					<tr><td>&euro;100</td><td>Several thousand (varies monthly)</td></tr>
				</tbody>
			</table>
		</div>
		<p>
			The number of &euro;100 prizes isn't fixed &mdash; the overall prize fund is recalculated at the end of every month based
			on the total value of Prize Bonds in circulation and net sales that month, and the bulk of that fund is paid out as the large
			number of &euro;100 prizes. Based on the current level of Prize Bonds outstanding, around 10,000 prizes are expected to be
			awarded every week.
		</p>

		<h2>The monthly jackpot</h2>
		<p>
			On the <strong>last draw of every calendar month</strong>, one extra prize is added on top of the usual weekly tiers:
			a <strong>&euro;500,000 jackpot</strong>. Everything else about that draw works the same way &mdash; it's still one
			bond number, drawn at random, from every eligible bond in circulation.
		</p>

		<h2>How your numbers are checked</h2>
		<p>
			Each individual bond you own has its own unique number &mdash; a short letter prefix followed by a six-digit number
			(for example <code>AHU176759</code>). If you hold multiple bonds, you have multiple numbers, and each one is entered
			separately into every draw. You can check any of your numbers on our <a href="<?php echo base_url(); ?>search/checker/">Check Numbers</a>
			or <a href="<?php echo base_url(); ?>search/power/">Power Search</a> pages.
		</p>

		<h2>Odds of winning</h2>
		<p>
			Because the total pool of bonds in circulation is large, the odds of any single bond winning a specific prize in a
			specific week are long. Irish press coverage has cited odds in the region of 1 in 141 million for a &euro;25 minimum
			holding to win the top prize in a given draw, improving to roughly 1 in 350,000 for a &euro;10,000 holding &mdash;
			but more bonds means more numbers in the draw, so your overall odds of winning <em>something</em> (including a &euro;75 prize)
			over a year are considerably better than any single-prize figure suggests. Try our
			<a href="<?php echo base_url(); ?>stats/odds/">odds calculator</a> for an estimate based on your own holding.
		</p>

		<h2>Tax treatment</h2>
		<p>
			Prize Bond winnings are entirely tax-free in Ireland &mdash; exempt from DIRT, Income Tax, PRSI and Capital Gains Tax.
			What you win is what you keep.
		</p>

		<h2>Getting paid</h2>
		<p>
			If one of your bonds wins, you can have the prize paid directly to your bank account, or automatically reinvested
			into new Prize Bonds in your name. If you haven't registered bank details, prizes are reinvested by default.
		</p>

		<h2>A bit of history</h2>
		<p>
			Prize Bonds have been part of Irish state savings since 1957, making them one of the longest-running savings
			products in the country. The scheme is run by the Prize Bond Company on behalf of the Minister for Finance,
			with An Post handling day-to-day administration through Ireland State Savings. Read the
			<a href="<?php echo base_url(); ?>history/">full history</a> for the complete timeline.
		</p>

		<h2>Buying, cashing in, and more</h2>
		<p>
			This site is a results checker only &mdash; we don't sell bonds or process claims. All buying, registration, and cashing-in
			happens through the official site at <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a>
			or your local Post Office. We've laid out exactly how that process works, step by step, in our own
			<a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to Buy and Cash In</a> guide, and if you're weighing Prize Bonds
			against an ordinary savings account, see <a href="<?php echo base_url(); ?>are-prize-bonds-worth-it/">Are Prize Bonds Worth It?</a>
			See also our <a href="<?php echo base_url(); ?>faq/">FAQ</a> for more specific questions.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
