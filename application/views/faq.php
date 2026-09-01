<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<script type="application/ld+json">
<?php
$schema_items = array();
foreach ($faqs as $item) {
	$schema_items[] = array(
		'@type' => 'Question',
		'name' => $item['q'],
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text' => strip_tags($item['a']),
		),
	);
}
echo json_encode(array(
	'@context' => 'https://schema.org',
	'@type' => 'FAQPage',
	'mainEntity' => $schema_items,
), JSON_UNESCAPED_SLASHES);
?>
</script>

<div class="container">
	<div class="content-page-body">
		<h1>Frequently Asked Questions</h1>

		<?php foreach ($faqs as $item): ?>
			<div class="faq-item">
				<h2><?php echo htmlspecialchars($item['q']); ?></h2>
				<p><?php echo $item['a']; ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
