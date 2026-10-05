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

<?php
$an = $analysis;
$ordinal = function ($n) {
	$n = (int) $n;
	$v = $n % 100;
	if ($v >= 11 && $v <= 13) {
		return $n . 'th';
	}
	$suffixes = array(1 => 'st', 2 => 'nd', 3 => 'rd');
	return $n . (isset($suffixes[$n % 10]) ? $suffixes[$n % 10] : 'th');
};
$eur = function ($n) { return '&euro;' . number_format(abs($n)); };
?>
	<h2>Draw Analysis</h2>
	<p class="text-muted">
		Worked out by us from this draw's published results and the other draws we track, not copied from the official site.
		Comparisons only use <?php echo $an['type_label']; ?> draws <?php echo $an['era_label']; ?>, because the prize fund and tiers
		changed on 1 September 2026 and totals from either side of that date aren't like-for-like.
		See <a href="<?php echo base_url(); ?>how-we-source-our-data/">how we source our data</a>, or the
		<a href="<?php echo base_url(); ?>stats/prize-fund/">monthly prize fund trends</a> for the bigger picture.
	</p>

	<?php if ($an['comparator']): ?>
		<?php
		$c = $an['comparator'];
		$fund_diff = $draw->total_prize_fund - $c->total_prize_fund;
		$prize_diff = $draw->total_prizes_count - $c->total_prizes_count;
		$same_month = date('Y-m', strtotime($c->draw_date)) === date('Y-m', strtotime($draw->draw_date));
		?>
		<h3>Compared with the previous <?php echo $an['type_label']; ?> draw</h3>
		<p>
			The previous <?php echo $an['type_label']; ?> draw, on
			<a href="<?php echo base_url(); ?>results/view/<?php echo $c->draw_date; ?>/"><?php echo date('j F Y', strtotime($c->draw_date)); ?></a>,
			awarded <?php echo number_format($c->total_prizes_count); ?> prizes worth <?php echo $eur($c->total_prize_fund); ?>.
			<?php if ($fund_diff == 0): ?>
				This draw's prize fund was <strong>identical</strong>
			<?php else: ?>
				This draw's prize fund was <strong><?php echo $eur($fund_diff); ?> <?php echo $fund_diff > 0 ? 'higher' : 'lower'; ?></strong>
				<?php if ($c->total_prize_fund > 0): ?>(<?php echo number_format(abs($fund_diff) / $c->total_prize_fund * 100, 1); ?>%)<?php endif; ?>
			<?php endif; ?>
			and it awarded
			<?php if ($prize_diff == 0): ?>
				<strong>the same number of prizes</strong>.
			<?php else: ?>
				<strong><?php echo number_format(abs($prize_diff)); ?> <?php echo $prize_diff > 0 ? 'more' : 'fewer'; ?> prizes</strong>.
			<?php endif; ?>
			<?php if (!$same_month && ($fund_diff != 0 || $prize_diff != 0)): ?>
				The prize fund is recalculated each month from net Prize Bond sales, so a change between draws in different months is expected.
			<?php endif; ?>
		</p>
	<?php elseif ($an['first_since_change']): ?>
		<h3>No earlier draw to compare with</h3>
		<p>
			This was the first draw published after the prize fund and tiers were increased on 1 September 2026, so there is no
			earlier draw under the same structure to compare it with.
		</p>
	<?php endif; ?>

	<?php if ($an['rank']): $r = $an['rank']; ?>
		<h3>Where this draw ranks</h3>
		<p>
			Of the <?php echo $r['of']; ?> <?php echo $an['type_label']; ?> draws we have published <?php echo $an['era_label']; ?>,
			this one ranks <strong><?php echo $r['fund_tied'] > 0 ? 'joint ' : ''; ?><?php echo $ordinal($r['by_fund']); ?></strong> for total prize fund and
			<strong><?php echo $r['prizes_tied'] > 0 ? 'joint ' : ''; ?><?php echo $ordinal($r['by_prizes']); ?></strong> for number of prizes awarded (1st is the highest).
			<?php if ($r['fund_tied'] > 0): ?>
				Prize funds are recalculated monthly, so draws in the same month often share the same total.
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ($an['money']): $m = $an['money']; ?>
		<h3>Where the prize money went</h3>
		<p>
			<?php if (!$m['single_tier']): ?>
				<?php if ($m['top_count'] === 1): ?>
					The single top prize of <?php echo $eur($m['top_value']); ?>
				<?php else: ?>
					The <?php echo number_format($m['top_count']); ?> top prizes of <?php echo $eur($m['top_value']); ?>
				<?php endif; ?>
				took <?php echo number_format($m['top_share'], 1); ?>% of the money in the prize breakdown above. At the other end, the
				<?php echo number_format($m['base_count']); ?> prizes of <?php echo $eur($m['base_value']); ?> made up
				<?php echo number_format($m['base_count_share'], 1); ?>% of all prizes and <?php echo number_format($m['base_value_share'], 1); ?>% of the money.
			<?php endif; ?>
			The average prize was <?php echo $eur($m['avg_prize']); ?>.
			<?php if ($m['big_count'] > 0): ?>
				<?php echo number_format($m['big_count']); ?> prizes (<?php echo number_format($m['big_count'] / $m['total_count'] * 100, 2); ?>%)
				were &euro;1,000 or more.
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ($an['geo']): $g = $an['geo']; ?>
		<h3>Where the winners are from</h3>
		<p>
			Prizes in this draw went to <strong><?php echo number_format($g['distinct']); ?> different <?php echo $g['distinct'] === 1 ? 'location' : 'locations'; ?></strong>
			(counties, or the country for overseas holders).
			<?php echo htmlspecialchars($g['top'][0]->name); ?> had the most, with <?php echo number_format($g['top'][0]->n); ?>
			prizes (<?php echo number_format($g['top'][0]->n / $g['located_total'] * 100, 1); ?>% of prizes with a recorded location).
			<?php if ($g['unlocated'] > 0): ?>
				<?php echo number_format($g['unlocated']); ?> prizes have no location recorded.
			<?php endif; ?>
		</p>
		<div class="table-wrap" style="max-width:500px;">
			<table class="data-table">
				<thead><tr><th>Location</th><th>Prizes</th><th>Share</th></tr></thead>
				<tbody>
					<?php foreach ($g['top'] as $loc): ?>
						<tr>
							<td><?php echo htmlspecialchars($loc->name); ?></td>
							<td><?php echo number_format($loc->n); ?></td>
							<td><?php echo number_format($loc->n / $g['located_total'] * 100, 1); ?>%</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php if ($g['big_total'] > 0 && !empty($g['big_top'])): ?>
			<p>
				The <?php echo number_format($g['big_total']); ?> prizes of &euro;1,000 or more went to:
				<?php
				$parts = array();
				foreach ($g['big_top'] as $loc) {
					$parts[] = htmlspecialchars($loc->name) . ' (' . number_format($loc->n) . ')';
				}
				echo implode(', ', $parts);
				if ($g['big_distinct'] > count($g['big_top'])) {
					$others = $g['big_distinct'] - count($g['big_top']);
					echo ' and ' . $others . ' other ' . ($others === 1 ? 'location' : 'locations');
				}
				?>.
			</p>
		<?php endif; ?>
		<p class="text-muted">
			For running totals across every draw, see the <a href="<?php echo base_url(); ?>stats/counties/">county statistics</a>
			and the list of <a href="<?php echo base_url(); ?>stats/winners/">big winners</a>.
		</p>
	<?php endif; ?>

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
