	</main>
	<footer class="site-footer">
		<div class="container">
			<div class="footer-grid">
				<div>
					<h4>Results</h4>
					<ul>
						<li><a href="<?php echo base_url(); ?>results/archive/">Draw Archive</a></li>
						<li><a href="<?php echo base_url(); ?>schedule/">Draw Dates</a></li>
						<li><a href="<?php echo base_url(); ?>search/checker/">Check My Numbers</a></li>
						<li><a href="<?php echo base_url(); ?>search/power/">Power Search</a></li>
					</ul>
				</div>
				<div>
					<h4>Insights</h4>
					<ul>
						<li><a href="<?php echo base_url(); ?>stats/winners/">Big Winners</a></li>
						<li><a href="<?php echo base_url(); ?>stats/counties/">County Stats</a></li>
						<li><a href="<?php echo base_url(); ?>stats/odds/">Odds Calculator</a></li>
					</ul>
				</div>
				<div>
					<h4>Learn</h4>
					<ul>
						<li><a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a></li>
						<li><a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to Buy &amp; Cash In</a></li>
						<li><a href="<?php echo base_url(); ?>are-prize-bonds-worth-it/">Are They Worth It?</a></li>
						<li><a href="<?php echo base_url(); ?>history/">History</a></li>
						<li><a href="<?php echo base_url(); ?>faq/">FAQ</a></li>
						<li><a href="<?php echo base_url(); ?>about/">About This Site</a></li>
						<li><a href="<?php echo base_url(); ?>contact-us/">Contact Us</a></li>
						<li><a href="<?php echo base_url(); ?>privacy-policy/">Privacy Policy</a></li>
						<li><a href="javascript:void(0);" id="cookie-settings-link">Cookie Settings</a></li>
						<li><a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">Official Site (statesavings.ie)</a></li>
					</ul>
				</div>
			</div>
			<div class="fine-print">
				&copy; <?php echo date('Y'); ?> Irish Prize Bonds. This is an independent, unofficial results checker and is not affiliated with, endorsed by, or connected to An Post, the Prize Bond Company, or the NTMA. For purchasing bonds or claiming prizes, use the official site at <a href="https://www.statesavings.ie" target="_blank" rel="noopener">statesavings.ie</a>.
			</div>
		</div>
	</footer>

	<div id="cookie-consent-banner" class="cookie-consent-banner" style="display:none;">
		<div class="container cookie-consent-inner">
			<p>
				We use essential cookies to run this site, and optional analytics cookies to help us understand usage —
				only if you agree. See our <a href="<?php echo base_url(); ?>privacy-policy/">Privacy Policy</a>.
			</p>
			<div class="cookie-consent-actions">
				<button id="cookie-consent-reject" type="button" class="btn btn-default btn-sm">Reject Non-Essential</button>
				<button id="cookie-consent-accept" type="button" class="btn btn-primary btn-sm">Accept All</button>
			</div>
		</div>
	</div>
	<script src="<?php echo base_url(); ?>assets/js/cookie-consent.js?v=<?php echo filemtime(APPPATH . '../assets/js/cookie-consent.js'); ?>"></script>
</body>
</html>
