<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<script type="application/ld+json">
<?php
$schema_items = array();
foreach ($terms as $item) {
	$schema_items[] = array(
		'@type' => 'DefinedTerm',
		'name' => $item['term'],
		'description' => strip_tags($item['definition']),
	);
}
echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'DefinedTermSet',
	'name' => 'Glossary of Prize Bond Terms',
	'hasDefinedTerm' => $schema_items,
), JSON_UNESCAPED_SLASHES);
?>
</script>

<div class="container">
	<div class="content-page-body">
		<h1>Glossary of Prize Bond Terms</h1>
		<p class="text-muted">Plain-English definitions for the terms used across this site and on your Prize Bond paperwork.</p>

		<?php foreach ($terms as $item): ?>
			<div class="faq-item">
				<h2><?php echo htmlspecialchars($item['term']); ?></h2>
				<p><?php echo $item['definition']; ?></p>
			</div>
		<?php endforeach; ?>

		<h2>Read next</h2>
		<p>
			See <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the full mechanics, or our <a href="<?php echo base_url(); ?>faq/">FAQ</a> for common questions in a Q&amp;A format.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
