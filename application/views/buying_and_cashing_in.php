<?php $this->load->view('website/header', array('title' => $title, 'description' => $description)); ?>

<div class="container">
	<div class="content-page-body">
		<h1>How to Buy and Cash In Irish Prize Bonds</h1>
		<p class="text-muted">A step-by-step walkthrough of buying, registering, and cashing in &mdash; using the official Ireland State Savings channels.</p>

		<div class="alert alert-info">
			We're an independent results checker &mdash; we don't sell bonds, hold your money, or process any transaction ourselves.
			Every step below happens directly with Ireland State Savings (An Post) or at your local Post Office. This page just
			explains the process clearly, since it isn't always laid out in one place.
		</div>

		<h2>How to buy Prize Bonds</h2>
		<p>Prize Bonds can be bought through three channels:</p>
		<ul>
			<li><strong>Online</strong> at <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a>, once you're a registered State Savings customer.</li>
			<li><strong>In person</strong> at any Post Office.</li>
			<li><strong>By phone</strong>, on 0818 20 50 60 (or 01 705 7200).</li>
			<li><strong>By monthly Direct Debit</strong> from a SEPA-compliant Irish or EU bank account, set up using the Direct Debit mandate form by post.</li>
		</ul>
		<p>
			You can hold from as little as <strong>&euro;25 (4 bonds of &euro;6.25 each)</strong> up to a maximum of
			<strong>&euro;250,000 (40,000 bonds)</strong> per person. Prize Bonds can also be bought as a gift for someone else,
			including a child, through any of the same channels.
		</p>

		<h2>Registering as a customer (first-time buyers)</h2>
		<p>
			Before your first purchase, you need to register as an Ireland State Savings customer by submitting a New Customer
			Application Form along with:
		</p>
		<ul>
			<li>Proof of identity &mdash; a current passport or EU driving licence.</li>
			<li>Proof of address issued within the last six months &mdash; a utility bill, bank statement, or Revenue letter.</li>
			<li>Proof of your PPSN &mdash; a Public Services Card or a letter from a government department.</li>
		</ul>
		<p>
			Once approved, you'll be issued a <strong>State Savings Customer Number (SSCN)</strong>, typically within 5&ndash;7 days.
			For a joint holding, both people need to be registered customers and each needs their own SSCN.
		</p>

		<h2>The 3-month rule &mdash; a common source of confusion</h2>
		<div class="alert alert-warning">
			Newly purchased Prize Bonds must be held for a <strong>minimum of 3 months (90 days)</strong> before they can be
			cashed in. This rule is about <em>encashment only</em> &mdash; it does not delay when a bond is first entered into
			a draw. Bonds you receive automatically through reinvested prize winnings are exempt from this waiting period and
			can be cashed in immediately.
		</div>
		<p>
			In other words: buy a bond today, and it's eligible to win in an upcoming draw straight away &mdash; but if you
			want your money back out, you'll need to wait at least 90 days from the purchase date before Ireland State Savings
			will process a repayment request for it.
		</p>

		<h2>How to cash in (repay) Prize Bonds</h2>
		<p>There are two ways to request repayment, once the holding period above has passed:</p>
		<ul>
			<li>
				<strong>By post</strong> &mdash; complete a Prize Bonds Repayment Form (available at any Post Office or as a
				download from statesavings.ie) and send it together with your Prize Bond certificate and a copy of a bank
				statement confirming the account name and IBAN, to:
				<br><em>Ireland State Savings, Prize Bonds, Fexco Centre, Killorglin, FREEPOST, Co. Kerry, V93 WN9T.</em>
			</li>
			<li>
				<strong>Online</strong> &mdash; if you're a State Savings Online customer with a sole (non-joint) holding, you
				can request repayment through your account, with funds transferred directly to your registered bank account.
			</li>
		</ul>
		<p>
			Once a bond is repaid, it becomes <strong>inactive</strong> and is no longer entered into future draws.
		</p>

		<h2>Getting paid if you win</h2>
		<p>
			Prizes can either be paid automatically into your registered bank account, or reinvested automatically into new
			Prize Bonds in your name. If you haven't registered bank details, winnings are reinvested by default &mdash; and
			as noted above, bonds bought this way skip the usual 3-month wait if you then want to cash them in.
		</p>

		<h2>Read next</h2>
		<p>
			See <a href="<?php echo base_url(); ?>how-it-works/">How Prize Bonds Work</a> for the draw mechanics and odds,
			<a href="<?php echo base_url(); ?>are-prize-bonds-worth-it/">Are Prize Bonds Worth It?</a> for how they stack up
			against an ordinary savings account, or our <a href="<?php echo base_url(); ?>faq/">FAQ</a> for quick answers to
			other common questions.
		</p>

		<p class="text-muted">
			Sources: Ireland State Savings official help articles (statesavings.ie), accessed <?php echo date('F Y'); ?>.
			Always confirm current forms, addresses, and processing times on the official site before acting, as these
			details can change.
		</p>
	</div>
</div>

<?php $this->load->view('website/footer'); ?>
