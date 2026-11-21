<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<script type="application/ld+json">
<?php echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'Article',
	'headline' => $post->title,
	'description' => $post->summary,
	'datePublished' => date('c', strtotime($post->published_at)),
	'dateModified' => date('c', strtotime($post->updated_at ? $post->updated_at : $post->published_at)),
	'mainEntityOfPage' => base_url('news/' . $post->slug . '/'),
	'author' => array('@type' => 'Organization', 'name' => 'Irish Prize Bonds'),
	'publisher' => array('@type' => 'Organization', 'name' => 'Irish Prize Bonds', 'url' => base_url()),
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
<script type="application/ld+json">
<?php echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'BreadcrumbList',
	'itemListElement' => array(
		array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => base_url()),
		array('@type' => 'ListItem', 'position' => 2, 'name' => 'Jackpot Write-ups', 'item' => base_url('news/')),
		array('@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => base_url('news/' . $post->slug . '/')),
	),
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>

<div class="container">
	<div class="content-page-body">
		<p class="text-muted" style="margin-bottom:6px;"><a href="<?php echo base_url(); ?>news/">&laquo; All jackpot write-ups</a></p>
		<h1><?php echo htmlspecialchars($post->title); ?></h1>
		<p class="text-muted">
			Published <?php echo date('j F Y', strtotime($post->published_at)); ?>
			<?php if ($draw): ?>
				&middot; <a href="<?php echo base_url(); ?>results/view/<?php echo $draw->draw_date; ?>/">Full results for the <?php echo date('j F Y', strtotime($draw->draw_date)); ?> draw</a>
			<?php endif; ?>
		</p>

		<?php echo $post->body; ?>

		<?php if (trim((string) $post->editor_note) !== ''): ?>
			<div class="alert alert-info">
				<strong>Editor's note:</strong> <?php echo nl2br(htmlspecialchars($post->editor_note)); ?>
			</div>
		<?php endif; ?>

		<?php $n = $neighbours; if ($n['older'] || $n['newer']): ?>
			<div class="btn-row" style="margin-top:28px;">
				<?php if ($n['older']): ?><a class="btn btn-default" href="<?php echo base_url(); ?>news/<?php echo htmlspecialchars($n['older']->slug); ?>/">&laquo; Previous jackpot write-up</a><?php endif; ?>
				<?php if ($n['newer']): ?><a class="btn btn-default" href="<?php echo base_url(); ?>news/<?php echo htmlspecialchars($n['newer']->slug); ?>/">Next jackpot write-up &raquo;</a><?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
