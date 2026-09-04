<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<h1>&#127942; Prize Bond Winners List</h1>
	<p class="lead-paragraph">
		A running list of Irish Prize Bond winners &mdash; every &euro;50,000 top prize and &euro;500,000 monthly
		jackpot winner across all the draws we track, with the draw date and winning location. The official site
		never aggregates winners across draws like this &mdash; this page is where the numbers add up: how many
		life-changing prizes have actually been won, and what they've totalled. For a specific draw's full winners
		list, open that date in the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>.
	</p>

	<div class="stat-grid" style="grid-template-columns: repeat(2,1fr); max-width:420px;">
		<div class="stat-tile">
			<div class="value"><?php echo number_format($total_rows); ?></div>
			<div class="label">Big Wins Recorded</div>
		</div>
		<div class="stat-tile">
			<div class="value">&euro;<?php echo number_format($total_value); ?></div>
			<div class="label">Total Paid Out</div>
		</div>
	</div>

	<div class="table-wrap">
		<table class="data-table">
			<thead>
				<tr><th>Draw Date</th><th>Prize</th><th>Bond Number</th><th>Location</th></tr>
			</thead>
			<tbody>
				<?php if (empty($winners)): ?>
					<tr><td colspan="4">No big winners recorded yet.</td></tr>
				<?php endif; ?>
				<?php foreach ($winners as $w): ?>
					<tr>
						<td><a href="<?php echo base_url(); ?>results/view/<?php echo $w->draw_date; ?>/"><?php echo date('d M Y', strtotime($w->draw_date)); ?></a></td>
						<td>
							&euro;<?php echo number_format($w->prize_value); ?>
							<?php if ($w->prize_value >= 500000): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
						</td>
						<td><?php echo htmlspecialchars($w->bond_number); ?></td>
						<td><?php echo $w->location ? htmlspecialchars($w->location) : '&mdash;'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php if ($total_pages > 1): ?>
	<nav>
		<ul class="pagination">
			<?php if ($page > 1): ?><li><a href="?page=<?php echo $page - 1; ?>">&laquo;</a></li><?php endif; ?>
			<?php
			$start = max(1, $page - 3);
			$end = min($total_pages, $page + 3);
			for ($p = $start; $p <= $end; $p++):
			?>
				<li class="<?php echo $p == $page ? 'active' : ''; ?>"><a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a></li>
			<?php endfor; ?>
			<?php if ($page < $total_pages): ?><li><a href="?page=<?php echo $page + 1; ?>">&raquo;</a></li><?php endif; ?>
		</ul>
	</nav>
	<?php endif; ?>

	<p class="text-muted">
		See where the big prizes have landed with <a href="<?php echo base_url(); ?>stats/counties/">County Stats</a>,
		or find out your own odds with the <a href="<?php echo base_url(); ?>stats/odds/">Odds Calculator</a>.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
