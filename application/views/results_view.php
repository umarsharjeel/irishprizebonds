<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<script type="application/ld+json">
<?php echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'BreadcrumbList',
	'itemListElement' => array(
		array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => base_url()),
		array('@type' => 'ListItem', 'position' => 2, 'name' => 'Results', 'item' => base_url('results/')),
		array('@type' => 'ListItem', 'position' => 3, 'name' => date('j F Y', strtotime($draw->draw_date)) . ' Draw', 'item' => base_url('results/view/' . $draw->draw_date . '/')),
	),
), JSON_UNESCAPED_SLASHES); ?>
</script>

<div class="container">

	<div class="grid-2" style="align-items:start;">
		<h1 style="margin-bottom:4px;">
			Draw Result &mdash; <?php echo date('d F Y', strtotime($draw->draw_date)); ?>
			<?php if ($draw->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
		</h1>
		<div style="text-align:right;">
			<?php if ($prev_draw): ?><a href="<?php echo base_url(); ?>results/view/<?php echo $prev_draw->draw_date; ?>/">&laquo; Previous Draw</a><?php endif; ?>
			<?php if ($prev_draw && $next_draw): ?> &nbsp;|&nbsp; <?php endif; ?>
			<?php if ($next_draw): ?><a href="<?php echo base_url(); ?>results/view/<?php echo $next_draw->draw_date; ?>/">Next Draw &raquo;</a><?php endif; ?>
		</div>
	</div>

	<?php if (!empty($summary)): ?>
		<p class="lead-paragraph"><?php echo $summary; ?></p>
	<?php endif; ?>

	<div class="stat-grid" style="grid-template-columns: repeat(2,1fr); max-width:420px;">
		<div class="stat-tile">
			<div class="value">&euro;<?php echo number_format($draw->total_prize_fund); ?></div>
			<div class="label">Total Prize Fund</div>
		</div>
		<div class="stat-tile">
			<div class="value"><?php echo number_format($draw->total_prizes_count); ?></div>
			<div class="label">Total Prizes</div>
		</div>
	</div>

	<h2>Prize Breakdown</h2>
	<div class="table-wrap" style="max-width:500px;">
		<table class="data-table">
			<thead>
				<tr><th>Prize Value</th><th>Number of Prizes</th><th>Value</th></tr>
			</thead>
			<tbody>
				<?php foreach ($tiers as $t): ?>
					<tr>
						<td>&euro;<?php echo number_format($t->prize_value, 2); ?></td>
						<td><?php echo number_format($t->prize_count); ?></td>
						<td>&euro;<?php echo number_format($t->prize_value * $t->prize_count, 2); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<p class="text-muted">Not sure what these prize tiers mean? Read <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a>, or try our <a href="<?php echo base_url(); ?>stats/odds/">Odds Calculator</a> to see your own chances.</p>

	<h2>Winners</h2>

	<form method="get" class="form-inline-fields">
		<div class="form-row">
			<label for="filter-q">Search bond number</label>
			<input type="text" name="q" id="filter-q" placeholder="e.g. AHU176759" value="<?php echo htmlspecialchars($search); ?>">
		</div>
		<div class="form-row">
			<label for="filter-location">Location</label>
			<select name="location" id="filter-location">
				<option value="0">All Locations</option>
				<?php foreach ($locations as $l): ?>
					<option value="<?php echo $l->id; ?>" <?php echo ($location_id == $l->id) ? 'selected' : ''; ?>><?php echo $l->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="form-row">
			<label for="filter-sort">Sort</label>
			<select name="sort" id="filter-sort">
				<option value="prize_desc" <?php echo $sort == 'prize_desc' ? 'selected' : ''; ?>>Prize Value High - Low</option>
				<option value="prize_asc" <?php echo $sort == 'prize_asc' ? 'selected' : ''; ?>>Prize Value Low - High</option>
				<option value="bond" <?php echo $sort == 'bond' ? 'selected' : ''; ?>>Bond Number</option>
				<option value="location" <?php echo $sort == 'location' ? 'selected' : ''; ?>>Location A - Z</option>
			</select>
		</div>
		<div class="form-row">
			<button type="submit" class="btn btn-primary">Apply</button>
		</div>
		<div class="form-row">
			<a href="<?php echo base_url(); ?>results/view/<?php echo $draw->draw_date; ?>/" class="btn btn-default">Reset</a>
		</div>
	</form>

	<p class="text-muted"><?php echo number_format($total_rows); ?> prizes shown</p>

	<div class="table-wrap">
		<table class="data-table">
			<thead>
				<tr><th>Prize Value</th><th>Winning Prize Bond</th><th>Location</th></tr>
			</thead>
			<tbody>
				<?php if (empty($winners)): ?>
					<tr><td colspan="3">No winners match your filters.</td></tr>
				<?php endif; ?>
				<?php foreach ($winners as $w): ?>
					<tr>
						<td>&euro;<?php echo number_format($w->prize_value, 2); ?></td>
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
			<?php
			$qs = function($p) use ($search, $location_id, $sort) {
				return http_build_query(array('q' => $search, 'location' => $location_id, 'sort' => $sort, 'page' => $p));
			};
			?>
			<?php if ($page > 1): ?><li><a href="?<?php echo $qs($page - 1); ?>">&laquo;</a></li><?php endif; ?>
			<?php
			$start = max(1, $page - 3);
			$end = min($total_pages, $page + 3);
			for ($p = $start; $p <= $end; $p++):
			?>
				<li class="<?php echo $p == $page ? 'active' : ''; ?>"><a href="?<?php echo $qs($p); ?>"><?php echo $p; ?></a></li>
			<?php endfor; ?>
			<?php if ($page < $total_pages): ?><li><a href="?<?php echo $qs($page + 1); ?>">&raquo;</a></li><?php endif; ?>
		</ul>
	</nav>
	<?php endif; ?>

	<p class="text-muted">
		Don't see your numbers here? Check them across every draw with our
		<a href="<?php echo base_url(); ?>search/checker/">Prize Bond Checker</a> or
		<a href="<?php echo base_url(); ?>search/power/">Power Search</a>.
	</p>

</div>

<?php $this->load->view('website/footer'); ?>
