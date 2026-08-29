<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<h1>
		<?php echo $is_future ? 'Upcoming Draw' : 'Draw Result'; ?> &mdash; <?php echo date('d F Y', strtotime($draw->draw_date)); ?>
		<?php if ($draw->is_jackpot): ?><span class="badge badge-jackpot">Jackpot</span><?php endif; ?>
	</h1>

	<?php if ($is_future): ?>
		<div class="alert alert-info">
			This Prize Bond draw is scheduled for <strong><?php echo date('l, d F Y', strtotime($draw->draw_date)); ?></strong>.
			Results will be published here once the draw has taken place.
		</div>
	<?php else: ?>
		<div class="alert alert-warning">
			This draw has taken place — results are pending and this page will be updated soon.
		</div>
	<?php endif; ?>

	<a href="<?php echo base_url(); ?>results/archive" class="btn btn-default">&laquo; Back to Draw Archive</a>
</div>

<?php $this->load->view('website/footer'); ?>
