<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="hero">
	<div class="container">
		<h1>Irish Prize Bonds Checker</h1>
		<p class="lead">Check your Prize Bond numbers against every draw, track the latest results as they are published, and browse the full draw history — free, fast, and mobile-friendly.</p>
		<div class="actions">
			<a href="<?php echo base_url(); ?>search/checker/" class="btn btn-primary">Check My Numbers</a>
			<a href="<?php echo base_url(); ?>results/" class="btn btn-secondary">Latest Results</a>
			<a href="<?php echo base_url(); ?>results/archive/" class="btn btn-secondary">Draw Archive</a>
		</div>
	</div>
</div>

<div class="container">

	<div class="grid-2">
		<div class="card">
			<h2>Last Draw</h2>
			<?php if ($last_draw): ?>
				<p>
					<a href="<?php echo base_url(); ?>results/view/<?php echo $last_draw->draw_date; ?>/">
						<?php echo date('d F Y', strtotime($last_draw->draw_date)); ?>
					</a>
					<?php if ($last_draw->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
				</p>
				<?php if ($last_draw->published): ?>
					<div class="stat-grid" style="grid-template-columns: repeat(2,1fr);">
						<div class="stat-tile">
							<div class="value">&euro;<?php echo number_format($last_draw->total_prize_fund); ?></div>
							<div class="label">Total Prize Fund</div>
						</div>
						<div class="stat-tile">
							<div class="value"><?php echo number_format($last_draw->total_prizes_count); ?></div>
							<div class="label">Total Prizes</div>
						</div>
					</div>
					<?php if (!empty($top_tiers)): ?>
					<div class="table-wrap">
						<table class="data-table">
							<thead><tr><th>Prize Value</th><th>Winners</th></tr></thead>
							<tbody>
								<?php foreach ($top_tiers as $t): ?>
									<tr>
										<td>&euro;<?php echo number_format($t->prize_value, 2); ?></td>
										<td><?php echo number_format($t->prize_count); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php endif; ?>
					<a href="<?php echo base_url(); ?>results/view/<?php echo $last_draw->draw_date; ?>/" class="btn btn-default">See Full Results</a>
				<?php else: ?>
					<p class="text-muted">Results are pending and will be updated soon.</p>
					<a href="<?php echo base_url(); ?>results/view/<?php echo $last_draw->draw_date; ?>/" class="btn btn-default">View Status</a>
				<?php endif; ?>
			<?php else: ?>
				<p class="text-muted">No draws yet.</p>
			<?php endif; ?>
		</div>

		<div class="card">
			<h2>Next Draw</h2>
			<?php if ($next_draw): ?>
				<p>
					<a href="<?php echo base_url(); ?>results/view/<?php echo $next_draw->draw_date; ?>/">
						<?php echo date('l, d F Y', strtotime($next_draw->draw_date)); ?>
					</a>
					<?php if ($next_draw->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
				</p>
				<p class="text-muted"><?php echo $next_draw->is_jackpot ? 'This is the monthly jackpot draw, with a top prize of €500,000 on top of the usual weekly prizes.' : 'Regular weekly draw — top prize €50,000.'; ?></p>
				<a href="<?php echo base_url(); ?>results/view/<?php echo $next_draw->draw_date; ?>/" class="btn btn-default">Next Draw Details</a>
			<?php else: ?>
				<p class="text-muted">No upcoming draw scheduled yet.</p>
			<?php endif; ?>
		</div>
	</div>

	<h2>About This Site</h2>
	<p>
		Irish Prize Bonds is a free, independent checker and results tracker for Ireland's Prize Bond draws. Every
		bond you hold has its own unique number and its own chance to win in the <a href="<?php echo base_url(); ?>schedule/">weekly draw</a>,
		with an extra &euro;500,000 jackpot on the last draw of each month. Rather than making you dig through one
		draw at a time, we track results across every draw we cover, so you can search your numbers in one place,
		browse the <a href="<?php echo base_url(); ?>results/archive/">full draw archive</a>, and see statistics —
		like which <a href="<?php echo base_url(); ?>stats/counties/">county wins most often</a> — that a single-draw
		lookup can't show you.
	</p>
	<p>
		New here? Start with <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for a full
		explanation of prize tiers, tax treatment and how winnings are paid, or jump straight to the
		<a href="<?php echo base_url(); ?>faq/">FAQ</a> for quick answers. This site is not affiliated with An Post,
		the Prize Bond Company, or the NTMA — see our <a href="<?php echo base_url(); ?>about/">About</a> page for details.
	</p>

	<h2>Tools</h2>
	<div class="stat-grid">
		<a href="<?php echo base_url(); ?>stats/winners/" class="card" style="text-decoration:none;">
			<h3>&#127942; Big Winners</h3>
			<p class="text-muted" style="margin-bottom:0;">Every jackpot and €50,000+ winner, all in one place.</p>
		</a>
		<a href="<?php echo base_url(); ?>stats/counties/" class="card" style="text-decoration:none;">
			<h3>&#128506; County Stats</h3>
			<p class="text-muted" style="margin-bottom:0;">Which Irish county wins the most, across every draw we track.</p>
		</a>
		<a href="<?php echo base_url(); ?>stats/odds/" class="card" style="text-decoration:none;">
			<h3>&#128202; Odds Calculator</h3>
			<p class="text-muted" style="margin-bottom:0;">See your approximate odds of winning based on your holding.</p>
		</a>
		<a href="<?php echo base_url(); ?>how-it-works/" class="card" style="text-decoration:none;">
			<h3>&#128218; How It Works</h3>
			<p class="text-muted" style="margin-bottom:0;">Prize tiers, draw schedule, tax treatment and more explained.</p>
		</a>
	</div>

</div>

<?php $this->load->view('website/footer'); ?>
