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
	<h1>Irish Prize Bond Results and Winners List</h1>
	<p class="lead-paragraph">
		The latest Irish Prize Bond draw results and winners list, updated after every weekly draw. Prize Bond draws are held
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
		<div class="btn-row">
			<a href="<?php echo base_url(); ?>results/view/<?php echo $latest->draw_date; ?>/" class="btn btn-primary">See full winners list</a>
			<a href="<?php echo base_url(); ?>search/checker/" class="btn btn-default">Check my numbers</a>
		</div>
	</div>

	<?php if (!empty($winners)): ?>
	<h2>Winners List &mdash; <?php echo date('j F Y', strtotime($latest->draw_date)); ?> Draw</h2>
	<p>
		The <?php echo count($winners); ?> prizes of &euro;1,000 and over in this draw, with the winning bond number and location.
		<?php $smaller = (int) $latest->total_prizes_count - count($winners); ?>
		<?php if ($smaller > 0): ?>
			The other <?php echo number_format($smaller); ?> prizes are smaller and are in the
			<a href="<?php echo base_url(); ?>results/view/<?php echo $latest->draw_date; ?>/">full winners list</a>,
			where you can search by bond number.
		<?php endif; ?>
	</p>
	<div class="table-wrap">
		<table class="data-table data-table-compact">
			<thead><tr><th>Prize</th><th>Winning Bond</th><th>Location</th></tr></thead>
			<tbody>
				<?php foreach ($winners as $w): ?>
					<tr>
						<td>&euro;<?php echo number_format($w->prize_value); ?></td>
						<td><?php echo htmlspecialchars($w->bond_number); ?></td>
						<td><?php echo $w->location ? htmlspecialchars($w->location) : '&mdash;'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<h2>Recent Draws</h2>
	<div class="table-wrap">
		<table class="data-table data-table-compact">
			<thead>
				<tr><th>Draw Date</th><th>Prize Fund</th><th>Prizes</th><th class="hide-xs"></th></tr>
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
						<td class="hide-xs"><a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/" class="btn btn-default btn-sm">View</a></td>
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
