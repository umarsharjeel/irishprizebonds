<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<h1>Check Your Prize Bond Numbers</h1>
	<p class="lead-paragraph">
		Enter up to five Prize Bond numbers below and we'll check each one against every draw result we have on
		file — not just the most recent draw. Each bond you own has its own unique number (a short letter prefix
		followed by six digits, e.g. <code>AHU176759</code>) and its own independent chance to win in every draw,
		so it's worth checking each number you hold, not just one.
	</p>

	<div class="card">
		<form method="post">
			<div class="number-grid">
				<?php for ($i = 0; $i < 5; $i++): ?>
					<input type="text" name="number_<?php echo $i; ?>" placeholder="e.g. AHU176759" value="<?php echo htmlspecialchars($numbers[$i]); ?>">
				<?php endfor; ?>
			</div>
			<br>
			<button type="submit" name="do_check" value="1" class="btn btn-primary">Check Numbers</button>
		</form>
	</div>

	<?php if ($results !== null): ?>
		<?php if (empty($results)): ?>
			<div class="alert alert-info">No prizes found for the numbers entered.</div>
		<?php else: ?>
			<div class="alert alert-success">Congratulations &mdash; <?php echo count($results); ?> prize(s) found!</div>
			<div class="table-wrap">
				<table class="data-table">
					<thead>
						<tr><th>Bond Number</th><th>Prize Value</th><th>Draw Date</th><th>Location</th></tr>
					</thead>
					<tbody>
						<?php foreach ($results as $r): ?>
							<tr>
								<td><?php echo htmlspecialchars($r->bond_number); ?></td>
								<td>&euro;<?php echo number_format($r->prize_value, 2); ?></td>
								<td><a href="<?php echo base_url(); ?>results/view/<?php echo $r->draw_date; ?>/"><?php echo date('d F Y', strtotime($r->draw_date)); ?></a></td>
								<td><?php echo $r->location ? htmlspecialchars($r->location) : '&mdash;'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<p class="text-muted">
		Checking more than five numbers? Use <a href="<?php echo base_url(); ?>search/power/">Power Search</a> to paste a longer list,
		or browse every draw in the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
