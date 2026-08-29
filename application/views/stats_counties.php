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

	<p class="text-muted">
		See who's won the biggest prizes on the <a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a> page,
		or read <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the full picture.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
