<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Prize Bond Draw Dates &amp; Schedule</h1>

		<p>
			Irish Prize Bond draws are held <strong>every week</strong>. On the <strong>last draw of each calendar month</strong>,
			an additional &euro;500,000 jackpot prize is added on top of the usual weekly prize tiers &mdash; every other draw
			that month is a regular draw with a &euro;50,000 top prize.
		</p>

		<h2>Next Scheduled Draws</h2>
		<div class="table-wrap" style="max-width:500px;">
			<table class="data-table">
				<thead><tr><th>Draw Date</th><th>Type</th></tr></thead>
				<tbody>
					<?php if (empty($upcoming)): ?>
						<tr><td colspan="2">No upcoming draws scheduled yet.</td></tr>
					<?php endif; ?>
					<?php foreach ($upcoming as $d): ?>
						<tr>
							<td><a href="<?php echo base_url(); ?>results/view/<?php echo $d->draw_date; ?>/"><?php echo date('l, d F Y', strtotime($d->draw_date)); ?></a></td>
							<td><?php echo $d->is_jackpot ? '<span class="badge badge-jackpot">Jackpot (€500,000)</span>' : 'Regular (€50,000 top prize)'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<h2>How the schedule works</h2>
		<ul>
			<li><strong>Weekly draws:</strong> every week, prizes range from &euro;75 up to &euro;50,000.</li>
			<li><strong>Monthly jackpot:</strong> the last draw of each month adds a &euro;500,000 top prize.</li>
			<li><strong>Prize fund:</strong> recalculated at the end of every month based on net Prize Bond sales, which is why the number of smaller prizes varies slightly month to month.</li>
		</ul>

		<p>
			See the full <a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a> for past results, or read
			<a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the complete picture.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
