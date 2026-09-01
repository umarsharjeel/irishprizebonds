<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<h1>Draw Archive</h1>
	<p class="lead-paragraph">
		Every Irish Prize Bond draw we track, in one place — something the official results page doesn't offer,
		since it only ever shows one draw at a time. Click any date below for that draw's full winners list, or
		jump to a monthly overview to see a whole month's draws side by side.
	</p>

	<?php
	$months = array();
	$archive_total_fund = 0;
	$archive_total_prizes = 0;
	foreach ($draws as $d) {
		if (!$d->published) continue;
		$ym = date('Y-m', strtotime($d->draw_date));
		if (!isset($months[$ym])) $months[$ym] = 0;
		$months[$ym]++;
		$archive_total_fund += $d->total_prize_fund;
		$archive_total_prizes += $d->total_prizes_count;
	}
	?>

	<div class="stat-grid stat-grid-3">
		<div class="stat-tile">
			<div class="value"><?php echo count($months); ?></div>
			<div class="label">Months Tracked</div>
		</div>
		<div class="stat-tile">
			<div class="value">&euro;<?php echo number_format($archive_total_fund); ?></div>
			<div class="label">Total Paid Out</div>
		</div>
		<div class="stat-tile">
			<div class="value"><?php echo number_format($archive_total_prizes); ?></div>
			<div class="label">Total Prizes</div>
		</div>
	</div>

	<?php if (!empty($months)): ?>
	<p>
		<?php foreach ($months as $ym => $count): ?>
			<a href="<?php echo base_url(); ?>results/month/<?php echo $ym; ?>/" class="btn btn-default btn-sm" style="margin:0 6px 6px 0; display:inline-block;"><?php echo date('M Y', strtotime($ym . '-01')); ?></a>
		<?php endforeach; ?>
	</p>
	<?php endif; ?>

	<div class="table-wrap">
		<table class="data-table">
			<thead>
				<tr>
					<th>Draw Date</th>
					<th>Prize Fund</th>
					<th>Prizes</th>
					<th>Status</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($draws)): ?>
					<tr><td colspan="5">No draws yet.</td></tr>
				<?php endif; ?>
				<?php $today = date('Y-m-d'); ?>
				<?php foreach ($draws as $d): ?>
					<tr>
						<td>
							<a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/"><?php echo date('d M Y', strtotime($d->draw_date)); ?></a>
							<?php if ($d->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
						</td>
						<?php if ($d->published): ?>
							<td>&euro;<?php echo number_format($d->total_prize_fund); ?></td>
							<td><?php echo number_format($d->total_prizes_count); ?></td>
							<td><span class="badge badge-success">Published</span></td>
						<?php else: ?>
							<td>&mdash;</td>
							<td>&mdash;</td>
							<td>
								<?php if ($d->draw_date > $today): ?>
									<span class="badge badge-info">Upcoming</span>
								<?php else: ?>
									<span class="badge badge-warning">Pending</span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td><a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/" class="btn btn-default btn-sm">View</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
