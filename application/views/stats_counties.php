<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<h1>&#128506; County Prize Bond Statistics</h1>
	<p class="lead-paragraph">
		Which Irish county wins the most Prize Bond prizes? The official results page can only ever show you one
		draw's county breakdown at a time, reset every week. We keep a running tally across every draw we track,
		so patterns that only show up over months or years — not just this week's draw — actually become visible.
		<?php if (!empty($rows)): ?>
			Right now, <strong><?php echo htmlspecialchars($rows[0]->name); ?></strong> leads with
			<?php echo number_format($rows[0]->win_count); ?> recorded wins.
		<?php endif; ?>
	</p>

	<div class="card">
		<?php if (empty($rows)): ?>
			<p>No data yet.</p>
		<?php endif; ?>
		<?php $rank = 1; ?>
		<?php foreach ($rows as $r): ?>
			<div class="leaderboard-row">
				<div class="leaderboard-rank">#<?php echo $rank++; ?></div>
				<div class="leaderboard-name"><?php echo htmlspecialchars($r->name); ?></div>
				<div class="leaderboard-bar-wrap">
					<div class="leaderboard-bar" style="width: <?php echo $max_count > 0 ? round(($r->win_count / $max_count) * 100) : 0; ?>%;"></div>
				</div>
				<div class="leaderboard-count"><?php echo number_format($r->win_count); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<h2>Full Breakdown</h2>
	<div class="table-wrap">
		<table class="data-table">
			<thead>
				<tr><th>County / Location</th><th>Wins</th><th>Total Value Won</th></tr>
			</thead>
			<tbody>
				<?php foreach ($rows as $r): ?>
					<tr>
						<td><?php echo htmlspecialchars($r->name); ?></td>
						<td><?php echo number_format($r->win_count); ?></td>
						<td>&euro;<?php echo number_format($r->total_value); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php if (!empty($trend)): $t = $trend; ?>
		<h2>How Concentrated Are the Wins?</h2>
		<p>
			Across the <?php echo number_format($t['located_total']); ?> prizes with a recorded location, wins were spread over
			<?php echo number_format($t['distinct']); ?> locations. <strong><?php echo htmlspecialchars($t['leader']); ?></strong> took
			<?php echo number_format($t['leader_share'], 1); ?>% of them, and the top five locations together took
			<?php echo number_format($t['top5_share'], 1); ?>%.
			<?php if ($t['unlocated_total'] > 0): ?>
				A further <?php echo number_format($t['unlocated_total']); ?> prizes have no location recorded and are left out of every
				percentage on this page.
			<?php endif; ?>
		</p>
		<p class="text-muted">
			Share of prizes isn't the same as chance of winning. The official results don't say how many bonds are held in each county,
			so a location with more bondholders will win more prizes without being any luckier per bond.
		</p>

		<h2>Month by Month</h2>
		<?php if ($t['leader_min'] && $t['leader_max'] && $t['leader_min']['label'] !== $t['leader_max']['label']): ?>
			<p>
				<?php echo htmlspecialchars($t['leader']); ?>'s share of each month's prizes ranged from
				<?php echo number_format($t['leader_min']['share'], 1); ?>% (<?php echo $t['leader_min']['label']; ?>) to
				<?php echo number_format($t['leader_max']['share'], 1); ?>% (<?php echo $t['leader_max']['label']; ?>),
				counting only months with at least two draws.
			</p>
		<?php endif; ?>
		<div class="table-wrap">
			<table class="data-table">
				<thead>
					<tr>
						<th>Month</th><th>Draws</th><th>Prizes with a location</th><th>Locations winning</th>
						<th>Top location</th><th><?php echo htmlspecialchars($t['leader']); ?> share</th><th>Top-five share</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($t['months'] as $m): ?>
						<tr>
							<td><?php echo $m['label']; ?></td>
							<td><?php echo $m['draws']; ?></td>
							<td><?php echo number_format($m['located']); ?></td>
							<td><?php echo number_format($m['distinct']); ?></td>
							<td><?php echo htmlspecialchars($m['top_name']); ?> (<?php echo number_format($m['top_share'], 1); ?>%)</td>
							<td><?php echo number_format($m['leader_share'], 1); ?>%</td>
							<td><?php echo number_format($m['top5_share'], 1); ?>%</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ($t['big_total'] > 0 && !empty($t['compare'])): $c0 = $t['compare'][0]; ?>
			<h2>Do the Big Prizes Follow the Same Pattern?</h2>
			<p>
				<?php echo htmlspecialchars($c0['name']); ?> took <?php echo number_format($c0['all_share'], 1); ?>% of all prizes and
				<?php echo number_format($c0['big_share'], 1); ?>% of the prizes worth &euro;1,000 or more. Prizes of &euro;1,000 and over are a much
				smaller sample (<?php echo number_format($t['big_total']); ?> so far, against <?php echo number_format($t['located_total']); ?> prizes overall),
				so their shares move around more from month to month. The ten locations with the most prizes:
			</p>
			<div class="table-wrap">
				<table class="data-table">
					<thead>
						<tr><th>Location</th><th>Share of all prizes</th><th>Share of &euro;1,000+ prizes</th><th>Difference</th></tr>
					</thead>
					<tbody>
						<?php foreach ($t['compare'] as $c): ?>
							<tr>
								<td><?php echo htmlspecialchars($c['name']); ?></td>
								<td><?php echo number_format($c['all_share'], 1); ?>%</td>
								<td><?php echo number_format($c['big_share'], 1); ?>%</td>
								<td><?php echo $c['diff'] >= 0 ? '+' : '&minus;'; ?><?php echo number_format(abs($c['diff']), 1); ?> points</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<p class="text-muted">
		See how the weekly prize fund has changed on the <a href="<?php echo base_url(); ?>stats/prize-fund/">Prize Fund Trends</a> page,
		who's won the biggest prizes on the <a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a> page,
		or read <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the full picture.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
