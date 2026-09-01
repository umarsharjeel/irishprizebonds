<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<h1>Power Search</h1>
	<p class="lead-paragraph">
		If you hold more than a handful of bonds, checking them one at a time gets tedious fast — Power Search lets
		you paste a whole list at once (one per line, or separated by commas/spaces, up to 5,000 numbers) and we'll
		check every one against every draw result we have on file in a single pass. Useful if you've built up a
		holding over several purchases and want to check the lot after each draw.
	</p>
	<p class="lead-paragraph">
		You can mix in ranges too — a token like <code>AHU176720-AHU176759</code> (or the shorthand
		<code>AHU176720-176759</code>) expands to every number in that block, so a whole certificate's worth of
		consecutively-numbered bonds is one line, not forty.
	</p>

	<div class="card">
		<form method="post">
			<div class="form-row">
				<textarea name="list" rows="8" aria-label="Bond numbers to check, one per line or separated by commas or spaces" placeholder="AHU176759&#10;BDY424458-BDY424478&#10;AYR077005"><?php echo htmlspecialchars($list); ?></textarea>
			</div>
			<button type="submit" name="do_search" value="1" class="btn btn-primary">Search</button>
		</form>
	</div>

	<?php if (!empty($errors)): ?>
		<div class="alert alert-warning">
			<?php foreach ($errors as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($results !== null): ?>
		<p class="text-muted"><?php echo number_format($searched_count); ?> number(s) searched.</p>
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
		Just checking a few numbers? Try the simpler <a href="<?php echo base_url(); ?>search/checker/">Prize Bond Checker</a>,
		or browse every draw in the <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a>.
	</p>
</div>

<?php $this->load->view('website/footer'); ?>
