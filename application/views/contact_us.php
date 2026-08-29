<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Contact Us</h1>
		<p class="text-muted">
			Questions, feedback, or spotted an error in our draw data? Let us know below. For buying bonds, cashing
			them in, or claiming a prize, contact the official service at
			<a href="https://www.statesavings.ie" target="_blank" rel="noopener">statesavings.ie</a> instead — we can't help with those.
		</p>

		<?php if ($success): ?>
			<div class="alert alert-success">Thanks for getting in touch — we'll get back to you if a reply is needed.</div>
		<?php endif; ?>

		<?php if (!empty($errors)): ?>
			<div class="alert alert-warning">
				<ul style="margin-bottom:0;">
					<?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width:520px;">
			<form method="post" action="<?php echo base_url(); ?>contact-us/submit">
				<div class="form-row">
					<label>Name</label>
					<input type="text" name="name" value="<?php echo htmlspecialchars($old['name']); ?>" required>
				</div>
				<div class="form-row">
					<label>Email</label>
					<input type="text" name="email" value="<?php echo htmlspecialchars($old['email']); ?>" required>
				</div>
				<div class="form-row">
					<label>Message</label>
					<textarea name="message" rows="6" required><?php echo htmlspecialchars($old['message']); ?></textarea>
				</div>
				<!-- Leave this field blank -->
				<div style="position:absolute; left:-9999px;" aria-hidden="true">
					<label>Website</label>
					<input type="text" name="website" tabindex="-1" autocomplete="off">
				</div>
				<button type="submit" class="btn btn-primary">Send Message</button>
			</form>
		</div>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
