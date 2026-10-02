<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Irish Prize Bond Results</h1>

		<div class="alert alert-info">
			No draw results have been published here yet. Results appear as soon as the first draw has been imported
			from Ireland State Savings' official results.
		</div>

		<h2>What you'll find here once results are live</h2>
		<ul>
			<li>The result of every weekly draw, with the full prize breakdown and winning bond numbers by location.</li>
			<li>A <a href="<?php echo base_url(); ?>search/checker/">number checker</a> and <a href="<?php echo base_url(); ?>search/power/">Power Search</a> for testing your own bonds against every published draw.</li>
			<li>Statistics on <a href="<?php echo base_url(); ?>stats/winners/">big winners</a>, <a href="<?php echo base_url(); ?>stats/counties/">winning counties</a> and <a href="<?php echo base_url(); ?>stats/odds/">your odds</a>.</li>
		</ul>

		<h2>In the meantime</h2>
		<ul>
			<li>New to Prize Bonds? Start with <a href="<?php echo base_url(); ?>how-it-works/">how they work</a> and the <a href="<?php echo base_url(); ?>faq/">FAQ</a>.</li>
			<li>Looking for the next draw? See the <a href="<?php echo base_url(); ?>schedule/">draw schedule</a>.</li>
			<li>Want to know where our results come from? Read <a href="<?php echo base_url(); ?>how-we-source-our-data/">how we source and verify them</a>.</li>
		</ul>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
