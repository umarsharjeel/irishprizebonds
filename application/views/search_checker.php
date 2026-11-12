<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<script type="application/ld+json">
<?php echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'WebApplication',
	'name' => 'Irish Prize Bonds Number Checker',
	'url' => base_url('search/checker/'),
	'applicationCategory' => 'FinanceApplication',
	'operatingSystem' => 'Any',
	'offers' => array('@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'),
	'description' => 'Free tool to check Irish Prize Bond numbers against the draw results held on this site.',
), JSON_UNESCAPED_SLASHES); ?>
</script>

<div class="container">
	<h1>Check Your Prize Bond Numbers Online</h1>
	<p class="lead-paragraph">
		Enter up to five Prize Bond numbers below and we'll check each one against every draw result we have on
		file — not just the most recent draw. Each bond you own has its own unique number (a short letter prefix
		followed by six digits, e.g. <code>AHU176759</code>) and its own independent chance to win in every draw,
		so it's worth checking each number you hold, not just one.
	</p>
	<p class="lead-paragraph">
		Hold a block of consecutively-numbered bonds on one certificate? Fill in the optional <strong>Last Bond
		Number</strong> next to any row and we'll check the whole range for you — no need to type out every number
		individually.
	</p>

	<?php if ($coverage && (int) $coverage->draws > 0): ?>
		<div class="alert alert-info">
			<strong>What this checks:</strong> the <?php echo number_format($coverage->draws); ?> draws we hold, from
			<?php echo date('j F Y', strtotime($coverage->first_date)); ?> to <?php echo date('j F Y', strtotime($coverage->last_date)); ?>.
			A prize won in an earlier draw won't appear here &mdash; for earlier draws, check with <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">Ireland State Savings (statesavings.ie)</a>.
		</div>
	<?php endif; ?>

	<div class="card">
		<form method="post">
			<div class="checker-range-grid">
				<div class="checker-range-head">
					<span>Bond Number</span>
					<span>Last Bond Number <span class="text-muted">(optional &mdash; checks the whole range)</span></span>
				</div>
				<?php for ($i = 0; $i < 5; $i++): ?>
					<div class="checker-range-row">
						<input type="text" name="first_<?php echo $i; ?>" placeholder="e.g. AHU176759" aria-label="Bond number <?php echo $i + 1; ?>" value="<?php echo htmlspecialchars($slots[$i]['first']); ?>">
						<input type="text" name="last_<?php echo $i; ?>" placeholder="optional, e.g. AHU176780" aria-label="Last bond number for range <?php echo $i + 1; ?>" value="<?php echo htmlspecialchars($slots[$i]['last']); ?>">
					</div>
				<?php endfor; ?>
			</div>
			<br>
			<button type="submit" name="do_check" value="1" class="btn btn-primary">Check Numbers</button>
		</form>
	</div>

	<?php if (!empty($errors)): ?>
		<div class="alert alert-warning">
			<?php foreach ($errors as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($results !== null): ?>
		<?php if (empty($results)): ?>
			<div class="alert alert-info">No prizes found for the numbers entered in the draws we hold<?php if ($coverage && (int) $coverage->draws > 0): ?> (<?php echo date('j F Y', strtotime($coverage->first_date)); ?> to <?php echo date('j F Y', strtotime($coverage->last_date)); ?>)<?php endif; ?>. A prize won in an earlier draw wouldn't appear here &mdash; for earlier draws, check with <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">Ireland State Savings (statesavings.ie)</a>.</div>
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
		Checking more than five numbers or ranges? Use <a href="<?php echo base_url(); ?>search/power/">Power Search</a> to paste a longer list,
		or browse the draws we hold in the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>.
	</p>

	<h2>How to check your Prize Bonds</h2>
	<ol>
		<li>Find your bond numbers on your Prize Bond certificate or in your State Savings online account &mdash; each is a 2&ndash;3 letter prefix followed by six digits (e.g. <code>AHU176759</code>).</li>
		<li>Enter each number in a row above. If a certificate covers a block of consecutive bonds, put the first number in the left box and the last in the right box to check the whole range at once.</li>
		<li>Select <strong>Check Numbers</strong>. Every number is matched against the draw results we hold (the date range is shown above), not just the most recent draw &mdash; see the <a href="<?php echo base_url(); ?>results/archive/">archive</a>.</li>
		<li>Any prize is shown with its value, the draw date and the winning location. Unclaimed Prize Bond prizes do not expire, so for draws before our archive begins, check with Ireland State Savings.</li>
	</ol>
	<p class="text-muted">
		This is an independent checker. To claim a prize or update your bank details, use the official
		<a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a> site.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
