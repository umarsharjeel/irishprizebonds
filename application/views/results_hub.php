<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<script type="application/ld+json">
<?php echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'BreadcrumbList',
	'itemListElement' => array(
		array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => base_url()),
		array('@type' => 'ListItem', 'position' => 2, 'name' => 'Results', 'item' => base_url('results/')),
	),
), JSON_UNESCAPED_SLASHES); ?>
</script>

<div class="container">
	<h1>Irish Prize Bond Results</h1>
	<p class="lead-paragraph">
		The latest Irish Prize Bond draw results, updated after every weekly draw. Prize Bond draws are held
		every Friday, with an extra &euro;500,000 jackpot on the last draw of each month. Use the
		<a href="<?php echo base_url(); ?>search/checker/">number checker</a> to see if any of your bonds have won
		across every draw we track, not just the most recent one.
	</p>

	<div class="card">
		<h2>Latest Draw &mdash; <a href="<?php echo base_url(); ?>results/view/<?php echo $latest->draw_date; ?>/"><?php echo date('l, j F Y', strtotime($latest->draw_date)); ?></a>
			<?php if ($latest->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
		</h2>
		<div class="stat-grid" style="grid-template-columns: repeat(2,1fr);">
			<div class="stat-tile">
				<div class="value">&euro;<?php echo number_format($latest->total_prize_fund); ?></div>
				<div class="label">Total Prize Fund</div>
			</div>
			<div class="stat-tile">
				<div class="value"><?php echo number_format($latest->total_prizes_count); ?></div>
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
		<a href="<?php echo base_url(); ?>results/view/<?php echo $latest->draw_date; ?>/" class="btn btn-primary">See full winners list</a>
		<a href="<?php echo base_url(); ?>search/checker/" class="btn btn-default">Check my numbers</a>
	</div>

	<h2>Recent Draws</h2>
	<div class="table-wrap">
		<table class="data-table">
			<thead>
				<tr><th>Draw Date</th><th>Prize Fund</th><th>Prizes</th><th></th></tr>
			</thead>
			<tbody>
				<?php foreach ($recent as $d): ?>
					<tr>
						<td>
							<a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/"><?php echo date('d M Y', strtotime($d->draw_date)); ?></a>
							<?php if ($d->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
						</td>
						<td>&euro;<?php echo number_format($d->total_prize_fund); ?></td>
						<td><?php echo number_format($d->total_prizes_count); ?></td>
						<td><a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/" class="btn btn-default btn-sm">View</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="text-muted">
		Looking for an older draw? Browse every draw we track in the
		<a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>, check upcoming dates on the
		<a href="<?php echo base_url(); ?>schedule/">Draw Dates</a> page, or paste a long list of numbers into
		<a href="<?php echo base_url(); ?>search/power/">Power Search</a>.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
