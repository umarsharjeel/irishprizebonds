<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="grid-2" style="align-items:start;">
		<h1 style="margin-bottom:4px;"><?php echo $month_label; ?> Prize Bond Results</h1>
		<div style="text-align:right;">
			<?php if ($prev_month): ?><a href="<?php echo base_url(); ?>results/month/<?php echo $prev_month; ?>">&laquo; <?php echo date('F Y', strtotime($prev_month . '-01')); ?></a><?php endif; ?>
			<?php if ($prev_month && $next_month): ?> &nbsp;|&nbsp; <?php endif; ?>
			<?php if ($next_month): ?><a href="<?php echo base_url(); ?>results/month/<?php echo $next_month; ?>"><?php echo date('F Y', strtotime($next_month . '-01')); ?> &raquo;</a><?php endif; ?>
		</div>
	</div>

	<p class="text-muted">Every Irish Prize Bond draw held in <?php echo $month_label; ?>, with prize funds and winner counts. Click any draw for the full winners list.</p>

	<div class="stat-grid" style="grid-template-columns: repeat(3,1fr); max-width:600px;">
		<div class="stat-tile">
			<div class="value"><?php echo count($draws); ?></div>
			<div class="label">Draws Held</div>
		</div>
		<div class="stat-tile">
			<div class="value">&euro;<?php echo number_format($total_fund); ?></div>
			<div class="label">Total Paid Out</div>
		</div>
		<div class="stat-tile">
			<div class="value"><?php echo number_format($total_prizes); ?></div>
			<div class="label">Total Prizes</div>
		</div>
	</div>

	<div class="table-wrap">
		<table class="data-table">
			<thead>
				<tr><th>Draw Date</th><th>Prize Fund</th><th>Prizes</th><th></th></tr>
			</thead>
			<tbody>
				<?php foreach ($draws as $d): ?>
					<tr>
						<td>
							<a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>"><?php echo date('d F Y', strtotime($d->draw_date)); ?></a>
							<?php if ($d->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
						</td>
						<td>&euro;<?php echo number_format($d->total_prize_fund); ?></td>
						<td><?php echo number_format($d->total_prizes_count); ?></td>
						<td><a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>" class="btn btn-default btn-sm">View</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="text-muted">
		Looking for a different month? Browse the full <a href="<?php echo base_url(); ?>results/archive">Draw Archive</a>,
		or check your own numbers with our <a href="<?php echo base_url(); ?>search/checker">Prize Bond Checker</a>.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
