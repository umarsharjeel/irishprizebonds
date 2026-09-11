<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Prize Bond Draw Dates: When Is the Next Draw?</h1>

		<p>
			Irish Prize Bond draws are held <strong>every week</strong>. On the <strong>last draw of each calendar month</strong>,
			an additional &euro;500,000 jackpot prize is added on top of the usual weekly prize tiers &mdash; every other draw
			that month is a regular draw with a &euro;100,000 top prize.
		</p>

		<h2>Next Draw</h2>
		<?php if ($next_draw): ?>
			<p>
				The next Prize Bond draw is <strong><?php echo date('l, d F Y', strtotime($next_draw->draw_date)); ?></strong>
				<?php if ($next_draw->is_jackpot): ?>
					&mdash; a <strong>jackpot draw</strong>, with a &euro;500,000 top prize on top of the usual tiers.
				<?php else: ?>
					&mdash; a regular draw, with a &euro;100,000 top prize.
				<?php endif; ?>
				Confirmed straight from statesavings.ie's own published schedule.
			</p>
			<p>
				<a href="<?php echo base_url(); ?>results/view/<?php echo $next_draw->draw_date; ?>/" class="btn btn-primary">View this draw</a>
			</p>
		<?php else: ?>
			<p class="text-muted">We don't have a confirmed next draw date right now — check back shortly, or see the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a> for the latest results.</p>
		<?php endif; ?>

		<h2>How the schedule works</h2>
		<ul>
			<li><strong>Weekly draws:</strong> every Friday, prizes range from &euro;100 up to &euro;100,000.</li>
			<li><strong>Monthly jackpot:</strong> the last Friday of each month adds a &euro;500,000 top prize &mdash; 12 jackpot draws a year, one per calendar month.</li>
			<li><strong>Prize fund:</strong> recalculated at the end of every month based on net Prize Bond sales, which is why the number of smaller prizes varies slightly month to month.</li>
		</ul>

		<h2>When a draw date moves</h2>
		<p>
			Draws almost always fall on a Friday, but a bank holiday around Christmas, New Year, or Easter
			(Good Friday itself is a bank holiday) can shift a specific week's draw by a day or two either way. We don't
			guess these shifts in advance &mdash; the <a href="<?php echo base_url(); ?>results/">Latest Results</a> and
			archive only ever show a draw once statesavings.ie has actually confirmed it, so the date here is never a
			calendar assumption.
		</p>

		<h2>Why "pending" instead of a result</h2>
		<p>
			A draw shown as pending simply means it's been held but the full results aren't published yet &mdash; usually
			resolved within a day. See <a href="<?php echo base_url(); ?>how-we-source-our-data/">How We Source and Verify Our Draw Results</a>
			for exactly how results get from statesavings.ie onto this site.
		</p>

		<p>
			See the full <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a> for past results, or read
			<a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the complete picture.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
