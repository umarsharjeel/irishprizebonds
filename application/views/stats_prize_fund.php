<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<?php
$eur = function ($n) { return '&euro;' . number_format($n); };
$pct = function ($p, $dp = 1) { return ($p >= 0 ? '+' : '&minus;') . number_format(abs($p), $dp) . '%'; };
$has_months = !empty($months);
?>

<div class="container">
	<div class="content-page-body">
		<h1>Prize Fund Trends</h1>
		<p class="lead-paragraph">
			Every Prize Bond draw pays out a set list of prizes, but the size of the pot behind them isn't fixed: it is recalculated
			every month. This page tracks how the weekly prize fund has moved month by month
			<?php if ($totals['first']): ?>across the <?php echo number_format($totals['draws']); ?> draws we have published since
			<?php echo date('j F Y', strtotime($totals['first'])); ?><?php endif; ?>,
			<?php if ($change): ?>what the increase on 1 September 2026 changed, <?php endif; ?>and how many prizes a typical draw pays.
			Every figure is calculated from the draw results on this site.
		</p>

		<?php if (!$has_months): ?>
			<p class="text-muted">No published draws yet &mdash; trends will appear once results are in.</p>
		<?php endif; ?>

		<?php if ($change): $b = $change['before']; $a = $change['after']; ?>
			<h2>What the 1 September 2026 Increase Changed</h2>
			<p>
				The NTMA <a href="https://www.ntma.ie/news/ntma-to-increase-ireland-state-savings-rates" target="_blank" rel="noopener">announced</a>
				an increase to the rate used to calculate the monthly Prize Bond prize fund, with new prize tiers from 1 September 2026.
				Here is what that looks like in the results, comparing a regular draw in <?php echo $b['label']; ?> (the last month before the
				increase) with a regular draw in <?php echo $a['label']; ?> (the latest month since):
			</p>
			<div class="stat-grid" style="grid-template-columns: repeat(3,1fr);">
				<div class="stat-tile">
					<div class="value"><?php echo $pct($change['fund_pct']); ?></div>
					<div class="label">Weekly prize fund</div>
				</div>
				<div class="stat-tile">
					<div class="value"><?php echo $pct($change['prizes_pct']); ?></div>
					<div class="label">Prizes per draw</div>
				</div>
				<div class="stat-tile">
					<div class="value"><?php echo $b['big'] > 0 ? number_format($a['big'] / $b['big'], 1) . '&times;' : '&mdash;'; ?></div>
					<div class="label">Prizes of &euro;1,000+</div>
				</div>
			</div>
			<div class="table-wrap">
				<table class="data-table">
					<thead>
						<tr><th>Regular draw</th><th><?php echo $b['label']; ?></th><th><?php echo $a['label']; ?></th><th>Change</th></tr>
					</thead>
					<tbody>
						<tr><td>Weekly prize fund</td><td><?php echo $eur($b['fund']); ?></td><td><?php echo $eur($a['fund']); ?></td><td><?php echo $pct($change['fund_pct']); ?></td></tr>
						<tr><td>Prizes per draw</td><td><?php echo number_format($b['prizes']); ?></td><td><?php echo number_format($a['prizes']); ?></td><td><?php echo $pct($change['prizes_pct']); ?></td></tr>
						<tr><td>Top prize</td><td><?php echo $eur($b['top']); ?></td><td><?php echo $eur($a['top']); ?></td><td><?php echo $pct($change['top_pct'], 0); ?></td></tr>
						<tr><td>Prizes of &euro;1,000 or more</td><td><?php echo number_format($b['big']); ?></td><td><?php echo number_format($a['big']); ?></td><td><?php echo $change['big_pct'] !== null ? $pct($change['big_pct'], 0) : '&mdash;'; ?></td></tr>
						<tr><td>Smallest prize</td><td><?php echo $eur($b['base']); ?></td><td><?php echo $eur($a['base']); ?></td><td><?php echo $pct($change['base_pct'], 0); ?></td></tr>
						<tr><td>Average prize</td><td><?php echo $eur($b['avg']); ?></td><td><?php echo $eur($a['avg']); ?></td><td><?php echo $pct($change['avg_pct']); ?></td></tr>
						<tr><td>Top prize as a share of the fund</td><td><?php echo number_format($b['top_share'], 1); ?>%</td><td><?php echo number_format($a['top_share'], 1); ?>%</td><td><?php echo number_format(abs($a['top_share'] - $b['top_share']), 1); ?> points <?php echo $a['top_share'] >= $b['top_share'] ? 'higher' : 'lower'; ?></td></tr>
					</tbody>
				</table>
			</div>
			<p>
				<?php if ($change['base_extra_share'] !== null): ?>
					Of the extra <?php echo $eur($change['extra']); ?> a week, <?php echo $eur($change['base_extra']); ?>
					(<?php echo number_format($change['base_extra_share']); ?>%) went into the smallest prize tier: <?php echo number_format($b['base_count']); ?> prizes of
					<?php echo $eur($b['base']); ?> became <?php echo number_format($a['base_count']); ?> prizes of <?php echo $eur($a['base']); ?>.
				<?php endif; ?>
				The number of &euro;1,000+ prizes went from <?php echo number_format($b['big']); ?> to <?php echo number_format($a['big']); ?>, and the top prize
				<?php echo $a['top'] > $b['top'] ? 'rose from ' . $eur($b['top']) . ' to ' . $eur($a['top']) : 'stayed at ' . $eur($a['top']); ?>.
				These are regular draws only; a jackpot draw adds one &euro;500,000 prize on top.
			</p>
		<?php endif; ?>

		<?php if ($has_months): ?>
			<h2>Month by Month</h2>

			<?php if ($chart): ?>
				<figure style="margin:0 0 16px;">
					<svg viewBox="0 0 <?php echo $chart['w']; ?> <?php echo $chart['h']; ?>" role="img" aria-labelledby="pf-chart-title pf-chart-desc"
					     style="width:100%;height:auto;max-width:720px;display:block;">
						<title id="pf-chart-title">Regular weekly prize fund by month</title>
						<desc id="pf-chart-desc">Bar chart of the weekly prize fund of regular draws in each month. The same figures are in the table below.</desc>
						<?php foreach ($chart['grid'] as $g): ?>
							<line x1="<?php echo $chart['ml']; ?>" x2="<?php echo $chart['w'] - $chart['mr']; ?>" y1="<?php echo round($g['y'], 1); ?>" y2="<?php echo round($g['y'], 1); ?>" stroke="var(--color-border)" stroke-width="1"/>
							<text x="<?php echo $chart['ml'] - 8; ?>" y="<?php echo round($g['y'], 1) + 4; ?>" text-anchor="end" font-size="11" fill="var(--color-text-muted)"><?php echo $g['label']; ?></text>
						<?php endforeach; ?>
						<?php foreach ($chart['bars'] as $bar): ?>
							<rect x="<?php echo round($bar['x'], 1); ?>" y="<?php echo round($bar['y'], 1); ?>" width="<?php echo round($bar['w'], 1); ?>" height="<?php echo round($bar['h'], 1); ?>" rx="2" fill="<?php echo $bar['new_era'] ? 'var(--color-accent)' : 'var(--color-primary)'; ?>"><title><?php echo $bar['full']; ?></title></rect>
							<text x="<?php echo round($bar['cx'], 1); ?>" y="<?php echo round($bar['y'], 1) - 6; ?>" text-anchor="middle" font-size="11" font-weight="600" fill="var(--color-text)"><?php echo $bar['value']; ?></text>
							<text x="<?php echo round($bar['cx'], 1); ?>" y="<?php echo $chart['base_y'] + 18; ?>" text-anchor="middle" font-size="12" fill="var(--color-text-muted)"><?php echo $bar['label']; ?></text>
						<?php endforeach; ?>
						<?php if ($chart['marker']): ?>
							<line x1="<?php echo round($chart['marker']['x'], 1); ?>" x2="<?php echo round($chart['marker']['x'], 1); ?>" y1="<?php echo $chart['marker']['top']; ?>" y2="<?php echo $chart['marker']['bottom']; ?>" stroke="var(--color-text-muted)" stroke-width="1" stroke-dasharray="4 3"/>
							<text x="<?php echo round($chart['marker']['x'], 1) + 6; ?>" y="<?php echo $chart['marker']['top'] + 6; ?>" font-size="11" fill="var(--color-text-muted)">Prize increase, 1 Sep</text>
						<?php endif; ?>
					</svg>
					<figcaption class="text-muted">
						Weekly prize fund of regular draws, by month. Green bars are before the 1 September 2026 increase, gold bars after it.
					</figcaption>
				</figure>
			<?php endif; ?>

			<?php if ($drift): $dr = $drift; ?>
				<p>
					In the <?php echo $dr['count']; ?> months before the increase (<?php echo $dr['first']['label']; ?> to <?php echo $dr['last']['label']; ?>)
					the regular weekly fund <?php echo $dr['direction'] === 'moved' ? 'moved from' : $dr['direction'] . ' from'; ?>
					<?php echo $eur($dr['first']['reg_avg']); ?> to <?php echo $eur($dr['last']['reg_avg']); ?> (<?php echo $pct($dr['pct']); ?>).
					The fund is recalculated each month from the value of bonds outstanding and net sales &mdash; see
					<a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> &mdash; so small month-to-month steps are expected,
					and regular draws within a month normally share exactly the same fund.
				</p>
			<?php endif; ?>

			<div class="table-wrap">
				<table class="data-table">
					<thead>
						<tr>
							<th>Month</th><th>Draws</th><th>Regular weekly fund</th><th>Change</th>
							<th>Prizes per regular draw</th><th>Jackpot draw fund</th><th>Total prizes</th><th>Total fund</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($months as $m): ?>
							<tr>
								<td>
									<?php echo $m['label']; ?>
									<?php if ($m['new_era']): ?><span class="badge badge-info">After increase</span><?php endif; ?>
									<?php if ($m['in_progress']): ?><span class="text-muted">(so far)</span><?php endif; ?>
								</td>
								<td><?php echo $m['draws']; ?></td>
								<td>
									<?php if ($m['reg_avg'] === null): ?>&mdash;
									<?php elseif ($m['reg_min'] == $m['reg_max']): ?><?php echo $eur($m['reg_avg']); ?>
									<?php else: ?><?php echo $eur($m['reg_min']); ?> &ndash; <?php echo $eur($m['reg_max']); ?>
									<?php endif; ?>
								</td>
								<td><?php echo $m['change_pct'] !== null ? $pct($m['change_pct']) : '&mdash;'; ?></td>
								<td><?php echo $m['reg_prizes_avg'] !== null ? number_format($m['reg_prizes_avg']) : '&mdash;'; ?></td>
								<td><?php echo $m['jp_fund'] !== null ? $eur($m['jp_fund']) : '&mdash;'; ?></td>
								<td><?php echo number_format($m['prizes']); ?></td>
								<td><?php echo $eur($m['fund']); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<h2>How to Read These Figures</h2>
			<ul>
				<li><strong>Prize fund</strong> is the total value of every prize paid in a draw. The weekly (regular) figure leaves out jackpot draws, which add one extra &euro;500,000 prize.</li>
				<li><strong>Change</strong> compares each month's regular weekly fund with the previous month that had a regular draw. The jump at the 1 September 2026 increase is a change to the scheme, not a normal month-to-month move.</li>
				<li><strong>Partial months:</strong> we only count draws we have published, from <?php echo $totals['first'] ? date('j F Y', strtotime($totals['first'])) : 'the first draw'; ?> onwards. The current month shows draws so far, and the first month may be incomplete. A month with only a jackpot draw has no regular figure.</li>
				<li>Altogether the <?php echo number_format($totals['draws']); ?> draws we have published paid <?php echo number_format($totals['prizes']); ?> prizes worth <?php echo $eur($totals['fund']); ?>. See <a href="<?php echo base_url(); ?>how-we-source-our-data/">how we source our data</a>.</li>
			</ul>
		<?php endif; ?>

		<p class="text-muted">
			Related: <a href="<?php echo base_url(); ?>stats/counties/">where the prizes go, by county</a>,
			<a href="<?php echo base_url(); ?>stats/odds/">the odds calculator</a> and the
			<a href="<?php echo base_url(); ?>results/archive/">draw archive</a>.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
