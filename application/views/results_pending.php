<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<?php
$type_label = $draw->is_jackpot ? 'jackpot' : 'regular';
$top_prize = $draw->is_jackpot ? 500000 : 100000;
$date_long = date('l, d F Y', strtotime($draw->draw_date));
$n_type = ($type_stats && $type_stats->n) ? (int) $type_stats->n : 0;
?>

<div class="container">
	<div class="content-page-body">
		<h1>
			<?php echo $is_future ? 'Upcoming Draw' : 'Draw Result'; ?> &mdash; <?php echo date('d F Y', strtotime($draw->draw_date)); ?>
			<?php if ($draw->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
		</h1>

		<?php if ($is_future): ?>
			<div class="alert alert-info">
				This Prize Bond draw is scheduled for <strong><?php echo $date_long; ?></strong>.
				Results will be published here once the draw has taken place.
			</div>
		<?php else: ?>
			<div class="alert alert-warning">
				This draw has taken place &mdash; results are pending and this page will be updated soon.
			</div>
		<?php endif; ?>

		<h2>About this draw</h2>
		<p>
			<?php if ($is_future): ?>
				<?php
				if ($days_until > 1) {
					$when = $days_until . ' days away';
				} elseif ($days_until === 1) {
					$when = 'tomorrow';
				} else {
					$when = 'today';
				}
				?>
				This is a <strong><?php echo $type_label; ?> draw</strong>, <?php echo $when; ?>.
			<?php else: ?>
				This was a <strong><?php echo $type_label; ?> draw</strong>.
			<?php endif; ?>
			<?php if ($draw->is_jackpot): ?>
				Jackpot draws carry the usual weekly prize tiers plus one extra &euro;<?php echo number_format($top_prize); ?> top prize,
				and fall on the last draw of the calendar month.
			<?php elseif ($new_era): ?>
				Regular draws have a &euro;<?php echo number_format($top_prize); ?> top prize, with smaller prizes in tiers
				down to &euro;100.
			<?php endif; ?>
			<?php if (!$is_friday): ?>
				It falls on a <?php echo date('l', strtotime($draw->draw_date)); ?> rather than the usual Friday &mdash;
				see <a href="<?php echo base_url(); ?>schedule/">why draw dates occasionally move</a>.
			<?php endif; ?>
			<?php if ($is_future): ?>
				Every bond you hold is entered into each draw automatically.
			<?php endif; ?>
		</p>

		<?php if ($last_same_type): ?>
			<h2>What the last <?php echo $type_label; ?> draw paid</h2>
			<p>
				The most recent <?php echo $type_label; ?> draw we have results for was
				<a href="<?php echo base_url(); ?>results/view/<?php echo $last_same_type->draw_date; ?>/"><?php echo date('j F Y', strtotime($last_same_type->draw_date)); ?></a>:
				<?php echo number_format($last_same_type->total_prizes_count); ?> prizes worth &euro;<?php echo number_format($last_same_type->total_prize_fund); ?> in total.
				<?php if (!empty($last_same_type_tiers)): ?>Its largest prize tiers were:<?php endif; ?>
			</p>
			<?php if (!empty($last_same_type_tiers)): ?>
			<div class="table-wrap">
				<table class="data-table">
					<thead><tr><th>Prize Value</th><th>Winners</th></tr></thead>
					<tbody>
						<?php foreach ($last_same_type_tiers as $t): ?>
							<tr>
								<td>&euro;<?php echo number_format($t->prize_value, 2); ?></td>
								<td><?php echo number_format($t->prize_count); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ($n_type >= 3): ?>
			<h2>How <?php echo $type_label; ?> draws have paid out so far</h2>
			<p>
				Across the <?php echo $n_type; ?> <?php echo $type_label; ?> draws we have published <?php echo $era_label; ?>, the prize fund has
				<?php if ($type_stats->min_fund == $type_stats->max_fund): ?>
					been &euro;<?php echo number_format($type_stats->avg_fund); ?> every time,
				<?php else: ?>
					averaged &euro;<?php echo number_format($type_stats->avg_fund); ?> and ranged from
					&euro;<?php echo number_format($type_stats->min_fund); ?> to &euro;<?php echo number_format($type_stats->max_fund); ?>,
				<?php endif; ?>
				with about <?php echo number_format($type_stats->avg_prizes); ?> prizes awarded per draw.
				The fund is recalculated each month from net Prize Bond sales, so it moves from month to month rather than
				staying fixed &mdash; expect this draw to land in a similar range rather than an exact figure.
			</p>
		<?php endif; ?>

		<?php if ($year_stats && $year_stats->n): ?>
			<h2><?php echo $year; ?> so far</h2>
			<p>
				<?php echo number_format($year_stats->n); ?> draws have been published for <?php echo $year; ?>, awarding
				<?php echo number_format($year_stats->prizes); ?> prizes worth &euro;<?php echo number_format($year_stats->fund); ?> in total.
				See the <a href="<?php echo base_url(); ?>stats/winners/">biggest winners</a> and the
				<a href="<?php echo base_url(); ?>stats/counties/">county breakdown</a> for where those prizes went.
			</p>
		<?php endif; ?>

		<?php if (!$is_future): ?>
			<h2>Why this result isn't here yet</h2>
			<p>
				A draw shows as pending until Ireland State Savings has fully published its results. Our importer checks for
				new results automatically throughout the day, so they usually appear within hours of the official release,
				occasionally longer if the official site is briefly unavailable. Nothing provisional or estimated is shown
				in the meantime.
				<a href="<?php echo base_url(); ?>how-we-source-our-data/">How we source and verify results</a> explains the process.
			</p>
		<?php endif; ?>

		<?php if (!empty($recent)): ?>
			<h2>Latest published results</h2>
			<div class="table-wrap">
				<table class="data-table">
					<thead><tr><th>Draw Date</th><th>Prize Fund</th><th>Prizes</th></tr></thead>
					<tbody>
						<?php foreach ($recent as $d): ?>
							<tr>
								<td>
									<a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/"><?php echo date('d M Y', strtotime($d->draw_date)); ?></a>
									<?php if ($d->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
								</td>
								<td>&euro;<?php echo number_format($d->total_prize_fund); ?></td>
								<td><?php echo number_format($d->total_prizes_count); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<h2>While you wait</h2>
		<ul>
			<li>Check your bond numbers against every draw we have published with the <a href="<?php echo base_url(); ?>search/checker/">number checker</a>, or paste a long list into <a href="<?php echo base_url(); ?>search/power/">Power Search</a>.</li>
			<li>Wondering what a win would involve? <a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to buy and cash in</a> covers claiming and repayment, and <a href="<?php echo base_url(); ?>stats/odds/">the odds calculator</a> estimates your chances.</li>
			<li>See all draw dates on the <a href="<?php echo base_url(); ?>schedule/">draw schedule</a>.</li>
		</ul>

		<div class="btn-row">
			<a href="<?php echo base_url(); ?>results/" class="btn btn-primary">See the latest results</a>
			<a href="<?php echo base_url(); ?>results/archive/" class="btn btn-default">Browse the draw archive</a>
		</div>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
