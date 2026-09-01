<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>&#128202; Prize Bond Odds Calculator</h1>
		<p class="text-muted">Estimate your odds of winning based on how many bonds you hold.</p>

		<div class="card">
			<form method="get">
				<div class="form-row">
					<label for="bonds-held">Number of bonds you hold (or plan to hold)</label>
					<input type="number" name="bonds" id="bonds-held" min="4" max="40000" step="1" value="<?php echo $bonds ?: ''; ?>" placeholder="e.g. 100">
				</div>
				<p class="text-muted" style="margin-bottom:10px;">Minimum holding is 4 bonds (&euro;25). Maximum is 40,000 bonds (&euro;250,000).</p>
				<button type="submit" class="btn btn-primary">Calculate</button>
			</form>
		</div>

		<?php if ($result): ?>
			<div class="card">
				<h2>With <?php echo number_format($result['bonds']); ?> bonds (&euro;<?php echo number_format($result['euro_value']); ?>)</h2>
				<div class="stat-grid" style="grid-template-columns: repeat(1,1fr);">
					<div class="stat-tile" style="text-align:left; display:flex; justify-content:space-between; align-items:center;">
						<span class="label">Odds of the top prize in one draw</span>
						<span class="value" style="font-size:1.1rem;">1 in <?php echo number_format(1 / max($result['top_prize_per_draw'], 0.0000000001)); ?></span>
					</div>
					<div class="stat-tile" style="text-align:left; display:flex; justify-content:space-between; align-items:center;">
						<span class="label">Odds of winning <em>any</em> prize in one draw</span>
						<span class="value" style="font-size:1.1rem;">1 in <?php echo number_format(1 / max($result['any_prize_per_draw'], 0.0000000001)); ?></span>
					</div>
					<div class="stat-tile" style="text-align:left; display:flex; justify-content:space-between; align-items:center;">
						<span class="label">Odds of winning <em>something</em> over a year (52 draws)</span>
						<span class="value" style="font-size:1.1rem;"><?php echo round($result['any_prize_per_year'] * 100, 1); ?>%</span>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<h2>How this is calculated</h2>
		<p>
			The exact number of Prize Bonds currently in circulation isn't published in real time, so this calculator uses a
			reference figure derived from odds reported in Irish press coverage (roughly 1 in 141 million for the minimum
			&euro;25 holding to win a top prize in a single draw), plus the average number of prizes awarded in a real
			regular weekly draw. These are <strong>estimates for general interest, not official figures</strong> &mdash;
			actual odds shift slightly month to month as the total pool of bonds and the prize fund change.
		</p>
		<p>
			This is a probability estimate, not financial advice. See <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the full picture.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
