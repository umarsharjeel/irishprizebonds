<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Jackpot Write-ups</h1>
		<p class="text-muted">
			One write-up for each monthly &euro;500,000 jackpot draw: who won, how the draw compares with the previous jackpot, and the jackpot winners in the draws we hold.
			Every figure comes from the official results.
		</p>

		<?php if (empty($posts)): ?>
			<p>The first write-up will appear here after the next jackpot draw. In the meantime, the <a href="<?php echo base_url(); ?>results/">latest results</a> and the
			<a href="<?php echo base_url(); ?>results/archive/">draw archive</a> are always up to date.</p>
		<?php else: ?>
			<?php foreach ($posts as $p): ?>
				<div class="card">
					<h2><a href="<?php echo base_url(); ?>news/<?php echo htmlspecialchars($p->slug); ?>/"><?php echo htmlspecialchars($p->title); ?></a></h2>
					<p class="text-muted" style="margin:0 0 8px;"><?php echo date('j F Y', strtotime($p->published_at)); ?></p>
					<p style="margin:0;"><?php echo htmlspecialchars($p->summary); ?></p>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
