<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<?php
$r = $report;
$eur = function ($n) { return '&euro;' . number_format($n); };
$pct = function ($p) { return number_format(abs($p), 1) . '%'; };
$words = array(1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six');
$nw = function ($n) use ($words) { return isset($words[$n]) ? $words[$n] : (string) $n; };
$plural = function ($n, $one, $many) { return $n === 1 ? $one : $many; };
?>

<div class="container">
	<div class="grid-2" style="align-items:start;">
		<h1 style="margin-bottom:4px;"><?php echo $month_label; ?> Prize Bond Results</h1>
		<div style="text-align:right;">
			<?php if ($prev_month): ?><a href="<?php echo base_url(); ?>results/month/<?php echo $prev_month; ?>/">&laquo; <?php echo date('F Y', strtotime($prev_month . '-01')); ?></a><?php endif; ?>
			<?php if ($prev_month && $next_month): ?> &nbsp;|&nbsp; <?php endif; ?>
			<?php if ($next_month): ?><a href="<?php echo base_url(); ?>results/month/<?php echo $next_month; ?>/"><?php echo date('F Y', strtotime($next_month . '-01')); ?> &raquo;</a><?php endif; ?>
		</div>
	</div>

	<p class="text-muted">Every Irish Prize Bond draw held in <?php echo $month_label; ?>, with prize funds and winner counts. Click any draw for the full winners list.</p>

	<div class="stat-grid stat-grid-3">
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

	<h2><?php echo $month_label; ?> at a Glance</h2>
	<p>
		<?php echo $month_label; ?> had <strong><?php echo $nw($r['draw_count']); ?> <?php echo $plural($r['draw_count'], 'draw', 'draws'); ?></strong><?php echo $r['in_progress'] ? ' so far' : ''; ?><?php if ($r['regular_count'] > 0 && $r['jackpot_count'] > 0): ?>
		(<?php echo $nw($r['regular_count']); ?> regular and <?php echo $nw($r['jackpot_count']); ?> jackpot)<?php endif; ?>,
		paying out <strong><?php echo $eur($total_fund); ?></strong> across <strong><?php echo number_format($total_prizes); ?> prizes</strong>.
		<?php if ($r['regular']): $g = $r['regular']; ?>
			<?php if ($g['fund_min'] == $g['fund_max']): ?>
				Each regular draw had a prize fund of <?php echo $eur($g['fund_avg']); ?>
			<?php else: ?>
				Regular draws had prize funds of between <?php echo $eur($g['fund_min']); ?> and <?php echo $eur($g['fund_max']); ?>
			<?php endif; ?>
			and awarded about <?php echo number_format($g['prizes_avg']); ?> prizes.
		<?php endif; ?>
		<?php if ($r['jackpot']): ?>
			The jackpot draw on <?php echo date('j F', strtotime($r['jackpot']->draw_date)); ?> had a total fund of <?php echo $eur($r['jackpot']->total_prize_fund); ?>,
			which includes the extra &euro;500,000 jackpot prize.
			<?php if (!empty($r['jackpot_post'])): ?>
				<a href="<?php echo base_url(); ?>news/<?php echo htmlspecialchars($r['jackpot_post']->slug); ?>/">Read the jackpot write-up.</a>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($r['in_progress']): ?>
			More draws will be added to this page as they take place.
		<?php endif; ?>
	</p>
	<?php if ($r['archive_starts_here']): ?>
		<p class="text-muted">
			This is the earliest month in our archive<?php echo $r['draw_count'] === 1 ? ', and its only draw is the first we hold' : ''; ?>; draws before
			<?php echo date('j F Y', strtotime($draws[0]->draw_date)); ?> aren't held here yet.
		</p>
	<?php endif; ?>

	<?php
	$cmp = array();
	if ($r['prev']) {
		$p = $r['prev'];
		if (!$p['same_era']) {
			$cmp[] = 'The Prize Bond fund and prize tiers were increased from 1 September 2026, so ' . $month_label . "'s totals can't be compared directly with "
				. $p['label'] . "'s. See <a href=\"" . base_url() . 'stats/prize-fund/">Prize Fund Trends</a> for the before-and-after.';
		} elseif ($r['regular'] && $p['reg_fund']) {
			$diff = $r['regular']['fund_avg'] - $p['reg_fund'];
			$pdiff = $r['regular']['prizes_avg'] - $p['reg_prizes'];
			if (abs($diff) < 0.5) {
				$cmp[] = 'The regular weekly prize fund was ' . $eur($r['regular']['fund_avg']) . ', unchanged from ' . $p['label'] . '.';
			} else {
				$cmp[] = 'The regular weekly prize fund was ' . $eur($r['regular']['fund_avg']) . ', ' . $eur(abs($diff)) . ' ' . ($diff > 0 ? 'higher' : 'lower')
					. ' than ' . $p['label'] . "'s " . $eur($p['reg_fund']) . ' (' . ($diff > 0 ? '+' : '&minus;') . $pct(100 * $diff / $p['reg_fund'])
					. '). The fund is recalculated each month from net Prize Bond sales.';
			}
			if (abs($pdiff) >= 0.5) {
				$cmp[] = 'Each regular draw awarded about ' . number_format(abs($pdiff)) . ' ' . ($pdiff > 0 ? 'more' : 'fewer') . ' prizes than in ' . $p['label'] . '.';
			}
		}
		if ($p['draws'] !== $r['draw_count'] && !$r['in_progress']) {
			$cmp[] = $month_label . ' had ' . $nw($r['draw_count']) . ' ' . $plural($r['draw_count'], 'draw', 'draws') . ' against ' . $nw($p['draws'])
				. ' in ' . $p['label'] . ", which is why the month's total paid out also differs.";
		}
	}
	?>
	<?php if (!empty($cmp)): ?>
		<h2>Compared with <?php echo $r['prev']['label']; ?></h2>
		<p><?php echo implode(' ', $cmp); ?></p>
	<?php endif; ?>

	<h2>Draws in <?php echo $month_label; ?></h2>
	<div class="table-wrap">
		<table class="data-table data-table-compact">
			<thead>
				<tr><th>Draw Date</th><th>Prize Fund</th><th>Prizes</th><th class="hide-xs"></th></tr>
			</thead>
			<tbody>
				<?php foreach ($draws as $d): ?>
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

	<?php if (!empty($r['big_prizes'])): ?>
		<h2>The Biggest Prizes of <?php echo $month_label; ?></h2>
		<?php
		$jp = array();
		$reg = array();
		foreach ($r['big_prizes'] as $w) {
			if ($w->prize_value >= 500000) { $jp[] = $w; } else { $reg[] = $w; }
		}
		$extra = array();
		foreach ($jp as $w) {
			if ($w->location) {
				$extra[] = 'The ' . $eur($w->prize_value) . ' jackpot on ' . date('j F', strtotime($w->draw_date)) . ' went to a bond registered in ' . htmlspecialchars($w->location) . '.';
			}
		}
		if (!empty($reg)) {
			$locs = array();
			$vals = array();
			foreach ($reg as $w) {
				$vals[(string) (int) $w->prize_value] = (int) $w->prize_value;
				if ($w->location) { $locs[] = htmlspecialchars($w->location); }
			}
			if (!empty($locs)) {
				$last = array_pop($locs);
				$list = empty($locs) ? $last : implode(', ', $locs) . ' and ' . $last;
				$extra[] = 'The weekly top ' . $plural(count($reg), 'prize', 'prizes') . ' (' . implode(' and ', array_map($eur, array_values($vals))) . ') went to ' . $list . '.';
			}
		}
		?>
		<p>
			<?php echo ucfirst($nw(count($r['big_prizes']))); ?> <?php echo $plural(count($r['big_prizes']), 'prize', 'prizes'); ?> of &euro;50,000 or more
			<?php echo $plural(count($r['big_prizes']), 'was', 'were'); ?> awarded<?php if ($r['big_count'] > 0): ?>,
			out of the <?php echo number_format($r['big_count']); ?> prizes of &euro;1,000 or more across the month's draws<?php endif; ?>.
			<?php echo implode(' ', $extra); ?>
		</p>
		<div class="table-wrap">
			<table class="data-table data-table-compact">
				<thead><tr><th>Draw</th><th>Prize</th><th>Winning Bond</th><th>Location</th></tr></thead>
				<tbody>
					<?php foreach ($r['big_prizes'] as $w): ?>
						<tr>
							<td><a href="<?php echo base_url(); ?>results/view/<?php echo $w->draw_date; ?>/"><?php echo date('j M', strtotime($w->draw_date)); ?></a></td>
							<td><?php echo $eur($w->prize_value); ?></td>
							<td><?php echo htmlspecialchars($w->bond_number); ?></td>
							<td><?php echo $w->location ? htmlspecialchars($w->location) : '&mdash;'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<?php if ($r['geo']): $geo = $r['geo']; $top = $geo['top']; ?>
		<?php
		$max_n = (int) $top[0]->n;
		$tied = array();
		foreach ($top as $l) {
			if ((int) $l->n === $max_n) {
				$tied[] = htmlspecialchars($l->name);
			}
		}
		$n_tied = count($tied);
		if ($n_tied >= 2) {
			$last_tied = array_pop($tied);
			$lead = implode(', ', $tied) . ' and ' . $last_tied . ' shared the top spot with ' . number_format($max_n) . ' prizes each';
			$rest = array_slice($top, $n_tied);
		} else {
			$lead = htmlspecialchars($top[0]->name) . ' had the most prizes with ' . number_format($max_n) . ' (' . $pct(100 * $max_n / $geo['located']) . ' of prizes with a recorded location)';
			$rest = array_slice($top, 1);
		}
		$follow = array();
		foreach (array_slice($rest, 0, 2) as $l) {
			$follow[] = htmlspecialchars($l->name) . ' (' . number_format($l->n) . ')';
		}
		$big_parts = array();
		foreach ($geo['big_top'] as $bt) {
			$big_parts[] = htmlspecialchars($bt->name) . ' (' . number_format($bt->big) . ')';
		}

		// How this compares with the previous month's geography.
		$geo_cmp = array();
		if ($r['prev_geo'] && $r['prev']) {
			$pg = $r['prev_geo'];
			$pl = $r['prev']['label'];
			if ($n_tied < 2) {
				$prev_top = $pg['top'][0];
				if (isset($pg['top'][1]) && (int) $pg['top'][1]->n === (int) $prev_top->n) {
					// a tied lead last month: no clean "led again / took the lead" statement
				} elseif ($prev_top->name === $top[0]->name) {
					$geo_cmp[] = htmlspecialchars($top[0]->name) . ' also led in ' . $pl . ', with ' . $pct(100 * $prev_top->n / $pg['located']) . ' of prizes.';
				} else {
					$geo_cmp[] = htmlspecialchars($top[0]->name) . ' took the lead from ' . htmlspecialchars($prev_top->name) . ', which topped ' . $pl . "'s table.";
				}
			}
			// Location counts are only comparable between months with the same number of draws.
			if ($r['prev']['draws'] === $r['draw_count'] && !$r['in_progress']) {
				if ($geo['distinct'] === $pg['distinct']) {
					$geo_cmp[] = 'As in ' . $pl . ', winners were spread across ' . number_format($geo['distinct']) . ' locations.';
				} else {
					$geo_cmp[] = number_format($geo['distinct']) . ' ' . $plural($geo['distinct'], 'location', 'locations') . ' had winners, against ' . number_format($pg['distinct']) . ' in ' . $pl . '.';
				}
			}
		}
		?>
		<h2>Where the Winners Were From</h2>
		<p>
			Winning bonds in <?php echo $month_label; ?> were registered in <strong><?php echo number_format($geo['distinct']); ?> different
			<?php echo $plural($geo['distinct'], 'location', 'locations'); ?></strong>.
			<?php echo $lead; ?><?php echo !empty($follow) ? ', followed by ' . implode(' and ', $follow) : ''; ?>.
			<?php echo implode(' ', $geo_cmp); ?>
			<?php if (!empty($big_parts)): ?>
				The prizes of &euro;1,000 or more went most often to <?php echo implode(', ', $big_parts); ?>.
			<?php endif; ?>
			<?php if ($geo['unlocated'] > 0): ?>
				<?php echo number_format($geo['unlocated']); ?> <?php echo $plural($geo['unlocated'], 'prize has', 'prizes have'); ?> no location recorded and
				<?php echo $plural($geo['unlocated'], 'is', 'are'); ?> left out of these figures.
			<?php endif; ?>
		</p>
		<div class="table-wrap">
			<table class="data-table data-table-compact">
				<thead><tr><th>Location</th><th>Prizes</th><th>Share</th></tr></thead>
				<tbody>
					<?php foreach ($top as $l): ?>
						<tr>
							<td><?php echo htmlspecialchars($l->name); ?></td>
							<td><?php echo number_format($l->n); ?></td>
							<td><?php echo number_format(100 * $l->n / $geo['located'], 1); ?>%</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<h2>About These Figures</h2>
	<p>
		Calculated by us from the draw results we hold (see <a href="<?php echo base_url(); ?>how-we-source-our-data/">how we source our data</a>).
		<?php if ($r['geo']): ?>
			Locations are as recorded by Ireland State Savings, and a bigger share of prizes isn't a better chance of winning, since the official
			results don't give bonds held per location.
		<?php endif; ?>
		Running totals: <a href="<?php echo base_url(); ?>stats/counties/">County Stats</a> and
		<a href="<?php echo base_url(); ?>stats/prize-fund/">Prize Fund Trends</a>.
	</p>

	<p class="text-muted">
		Looking for a different month? Browse the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>,
		or check your own numbers with our <a href="<?php echo base_url(); ?>search/checker/">Prize Bond Checker</a>.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
