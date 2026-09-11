<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>Unclaimed and Inherited Prize Bonds</h1>
		<p class="text-muted">What happens to Prize Bonds after the holder dies, how to trace or claim bonds you've found or inherited, and why old bonds never simply expire.</p>

		<div class="alert alert-info">
			This is general information, not legal or financial advice. Every estate is different — for anything beyond a straightforward case, speak to a solicitor or contact Ireland State Savings directly.
		</div>

		<h2>Prize Bonds never expire</h2>
		<p>
			A Prize Bond stays valid, and stays entered in every weekly draw, for as long as it's held &mdash; there's no expiry date and no cut-off after which an old bond stops counting. The same is true of prizes: an unclaimed win doesn't lapse either. This matters a lot for old certificates, because it means a bond bought decades ago, or found while sorting through a relative's paperwork, can still be actively winning money right now. You can check any bond number &mdash; however old &mdash; against every draw we hold using our <a href="<?php echo base_url(); ?>search/checker/">Check Numbers</a> tool.
		</p>

		<h2>What happens to Prize Bonds when the holder dies</h2>
		<p>
			Prize Bonds form part of the deceased person's estate, the same as a bank account or shares. They don't automatically pass to next of kin, and they don't get cashed in or cancelled on their own &mdash; they simply keep sitting in the weekly draw, under the original holder's name, until someone notifies Ireland State Savings of the death and completes their claims process.
		</p>

		<h2>If the estate held Prize Bonds only</h2>
		<p>
			Where Prize Bonds are the only Ireland State Savings product involved, the process is simpler than a full deceased-account claim. Send a letter notifying them of the death, together with:
		</p>
		<ul>
			<li>An original or certified copy of the Death Certificate (or Coroner's Certificate).</li>
			<li>If a will exists, the original or a certified copy of it, to establish who the executor(s) are.</li>
			<li>If there's no will, evidence that you're acting on behalf of the nearest next of kin.</li>
		</ul>
		<p>
			Send this to: <em>Ireland State Savings, Prize Bonds, Fexco Centre, Killorglin, FREEPOST, Co. Kerry, V93 WN9T.</em> If the estate held other State Savings products too (deposit accounts, Savings Certificates, and so on), a separate Deceased Claim Form is required instead &mdash; see the official <a href="https://www.statesavings.ie/help-support/help-articles/bereavement-guide-and-support" target="_blank" rel="noopener">Bereavement Guide and Support</a> page for that process.
		</p>

		<h2>When a Grant of Probate is needed</h2>
		<p>
			Ireland State Savings requires a Grant of Probate (or Letter of Administration) only where the deceased's <strong>total sole-name State Savings holdings were €25,000 or more</strong> on the date of death. Below that threshold, the simpler prize-bonds-only or deceased-claim process above is generally enough on its own, without waiting on full probate through the courts &mdash; which can otherwise take months.
		</p>

		<h2>Once the claim is processed</h2>
		<p>
			The person inheriting the bonds typically has two options: <strong>keep them</strong>, transferred into their own name, where they stay live in the draw indefinitely, or <strong>cash them in</strong> for their face value, same as any other repayment. Neither choice affects any prizes already won before the transfer &mdash; those are handled as part of the same claim.
		</p>

		<h2>Found an old bond and not sure what to do with it?</h2>
		<p>
			If you've come across a Prize Bond certificate &mdash; your own from years ago, or one belonging to somebody else &mdash; the first step is simply to check its number against our full draw history using <a href="<?php echo base_url(); ?>search/checker/">Check Numbers</a> or <a href="<?php echo base_url(); ?>search/power/">Power Search</a> for a longer list. If you're not the registered holder and believe it may be unclaimed from a deceased estate, Ireland State Savings can also help trace a holding from a name and address even without the certificate to hand.
		</p>

		<h2>Read next</h2>
		<p>
			See <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the draw mechanics, <a href="<?php echo base_url(); ?>how-to-buy-and-cash-in/">How to Buy and Cash In</a> for the standard (non-inherited) repayment process, or our <a href="<?php echo base_url(); ?>faq/">FAQ</a> for other common questions.
		</p>

		<p class="text-muted">
			Sources: Ireland State Savings official help articles &mdash; Bereavement Guide and Support, and "When does State Savings require a Grant of Probate?" (statesavings.ie), accessed <?php echo date('F Y'); ?>. Always confirm current forms, addresses and requirements on the official site, as these details can change.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
