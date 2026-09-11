<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Content extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function how_it_works()
	{
		$data['title'] = 'How Prize Bonds Work in Ireland | Irish Prize Bonds';
		$data['description'] = 'A plain-English guide to how Irish Prize Bonds work: prize tiers, draw schedule, tax treatment, odds of winning, and how to buy or cash them in.';
		$this->load->view('how_it_works', $data);
	}

	public function faq()
	{
		$data['faqs'] = array(
			array(
				'q' => 'How often are Prize Bond draws held?',
				'a' => 'Every week. On the last draw of each calendar month, an additional €500,000 jackpot prize is included alongside the usual weekly prizes.',
			),
			array(
				'q' => 'What are my odds of winning?',
				'a' => 'Odds depend on how many bonds you hold and how many bonds are in circulation overall. Irish press coverage has put the odds of the top weekly prize at roughly 1 in 141 million for the €25 minimum holding, improving with a larger holding. See our <a href="' . base_url() . 'stats/odds">odds calculator</a> for an estimate, and our <a href="' . base_url() . 'how-it-works">How It Works</a> page for more context.',
			),
			array(
				'q' => 'Are Prize Bond winnings taxed?',
				'a' => 'No. Winnings are exempt from DIRT, Income Tax, PRSI and Capital Gains Tax — what you win is yours in full.',
			),
			array(
				'q' => "What's the minimum and maximum I can hold?",
				'a' => 'Minimum €25 (4 bonds). Maximum €250,000 (40,000 bonds) per person.',
			),
			array(
				'q' => 'Is there a minimum holding period before I can cash in new bonds?',
				'a' => 'Yes. Newly purchased Prize Bonds must be held for a minimum of 3 months (90 days) before they can be cashed in / repaid. This restriction is about cashing in only &mdash; it does not delay when a bond first becomes eligible for a draw. Bonds you receive automatically through reinvested prize winnings are exempt from this waiting period. See our <a href="' . base_url() . 'how-to-buy-and-cash-in">How to Buy and Cash In</a> guide for the full process.',
			),
			array(
				'q' => 'How do I check if my numbers have won?',
				'a' => 'Use our <a href="' . base_url() . 'search/checker">Check Numbers</a> tool for up to 5 numbers at a time, or <a href="' . base_url() . 'search/power">Power Search</a> to paste a longer list. You can also browse every draw individually in the <a href="' . base_url() . 'results/archive">Draw Archive</a>.',
			),
			array(
				'q' => 'Can I check old Prize Bonds online?',
				'a' => 'Yes. Old Prize Bonds stay valid indefinitely and remain in every draw until they are cashed in, and unclaimed prizes never expire. Enter any bond number &mdash; however old &mdash; into our <a href="' . base_url() . 'search/checker">Check Numbers</a> tool and it is matched against every published draw we hold. If you have lost the certificate for an old holding, the official <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a> site can trace it from your name and address.',
			),
			array(
				'q' => 'What happens to Prize Bonds when the holder dies?',
				'a' => 'They become part of the estate, the same as a bank account or shares &mdash; they don\'t automatically pass to next of kin or get cashed in on their own. Whoever is handling the estate needs to notify Ireland State Savings, after which the bonds can be transferred into the inheritor\'s name or cashed in. See our <a href="' . base_url() . 'unclaimed-and-inherited-prize-bonds">Unclaimed and Inherited Prize Bonds</a> guide for the full process, including when a Grant of Probate is required.',
			),
			array(
				'q' => 'Can I buy Prize Bonds for a child?',
				'a' => 'Yes &mdash; Prize Bonds are a common gift for children, since the capital stays fully safe and every bond keeps entering the weekly draw for as long as it\'s held. A parent or guardian handles the purchase and any transactions until the child turns 18, at which point full control transfers automatically. See <a href="' . base_url() . 'prize-bonds-for-children">Buying Prize Bonds for a Child</a> for the documents needed and how it works in practice.',
			),
			array(
				'q' => 'How soon after a draw are the results published here?',
				'a' => 'Prize Bond draws take place each week and the official results are released shortly afterwards. We import each new draw as soon as the official figures are available, usually within a day, so the <a href="' . base_url() . 'results">latest results</a> page and number checker stay current. There is no minute-by-minute live feed &mdash; draw results are a single published set, not a rolling broadcast.',
			),
			array(
				'q' => 'How far back do your results go?',
				'a' => "We're continuously building a historical archive of draw results. Check the <a href=\"" . base_url() . 'results/archive">Draw Archive</a> for the current range of draws covered.',
			),
			array(
				'q' => 'Are Irish Prize Bonds the same as UK Premium Bonds?',
				'a' => 'They are the same idea run by different countries. Irish Prize Bonds are issued by the State through the NTMA and Prize Bond Company; UK Premium Bonds are issued by NS&amp;I. This site only covers the Irish scheme &mdash; draw dates, prize tiers, tax treatment and the checker here all relate to Irish Prize Bonds, not UK Premium Bonds.',
			),
			array(
				'q' => 'How long have Prize Bonds existed?',
				'a' => 'Since 1957 — one of the longest-running Irish state savings products. See our <a href="' . base_url() . 'history">History</a> page for the full timeline.',
			),
			array(
				'q' => 'How is a Prize Bond number formatted?',
				'a' => 'Each individual bond has a unique number: a short letter prefix (2-3 letters) followed by a six-digit number, e.g. AHU176759. If you hold multiple bonds, each has its own separate number and separate chance to win.',
			),
			array(
				'q' => 'How do I get paid if I win?',
				'a' => "Prizes are paid directly to a registered bank account, or automatically reinvested into new Prize Bonds if you haven't registered bank details.",
			),
			array(
				'q' => 'Is this the official Prize Bonds website?',
				'a' => 'No. This is an independent, unofficial results checker, not affiliated with An Post, the Prize Bond Company, or the NTMA. To buy bonds, cash them in, or claim a prize, use the official site at <a href="https://www.statesavings.ie/prize-bonds" target="_blank" rel="noopener">statesavings.ie</a>.',
			),
		);

		$data['title'] = 'Prize Bonds FAQ | Irish Prize Bonds';
		$data['description'] = 'Frequently asked questions about Irish Prize Bonds: odds of winning, tax, minimum holding period, history, and how prizes are paid.';
		$this->load->view('faq', $data);
	}

	public function about()
	{
		$data['title'] = 'About | Irish Prize Bonds';
		$data['description'] = 'About this independent Irish Prize Bond results checker.';
		$this->load->view('about', $data);
	}

	public function privacy_policy()
	{
		$data['title'] = 'Privacy Policy | Irish Prize Bonds';
		$data['description'] = 'How Irish Prize Bonds collects, uses, and protects your data, including our contact form and analytics cookies.';
		$this->load->view('privacy_policy', $data);
	}

	public function schedule()
	{
		$this->load->database();

		// The one real, authoritative future date we have: Cron::_sync_next_draw_preview()
		// keeps a single draws row in sync with statesavings.ie's own "Next Draw" banner
		// (not a guess — see that method's docblock). Show only this — no calendar
		// projection beyond it, consistent with never claiming a date we don't
		// actually know.
		//
		// draw_date >= today (not >): on the draw's own calendar day, before results
		// are posted, it's still genuinely "the next draw" — a strict > would wrongly
		// claim there's no confirmed next draw for the entire day. published = 0
		// is what actually retires it once it's done, rather than the date rolling
		// over at midnight — a completed draw should stop showing here the moment
		// it's imported, not sit around all day looking like it's still upcoming.
		$data['next_draw'] = $this->db->select('draw_date, is_jackpot')->from('draws')
			->where('draw_date >=', date('Y-m-d'))->where('published', 0)
			->order_by('draw_date', 'asc')->limit(1)->get()->row();

		$data['title'] = 'When Is the Next Prize Bond Draw? Dates and Schedule | Irish Prize Bonds';
		$data['description'] = 'When is the next Irish Prize Bond draw? The confirmed next draw date, the weekly draw schedule and monthly €500,000 jackpot dates explained.';
		$this->load->view('schedule', $data);
	}

	public function history()
	{
		$data['title'] = 'History of Irish Prize Bonds | Irish Prize Bonds';
		$data['description'] = 'The history of Irish Prize Bonds from their 1957 launch to today: legislation, operators, and how the scheme has evolved.';
		$this->load->view('history', $data);
	}

	public function buying_and_cashing_in()
	{
		$data['title'] = 'How to Buy and Cash In Irish Prize Bonds | Irish Prize Bonds';
		$data['description'] = 'Step-by-step: how to buy Irish Prize Bonds online, by post office, or by phone, how first-time registration works, the 3-month rule before cashing in, and how to request repayment.';
		$this->load->view('buying_and_cashing_in', $data);
	}

	public function unclaimed_and_inherited()
	{
		$data['title'] = 'Unclaimed and Inherited Prize Bonds | Irish Prize Bonds';
		$data['description'] = 'What happens to Prize Bonds when the holder dies, how to claim or trace inherited bonds, when a Grant of Probate is needed, and why old bonds never expire.';
		$this->load->view('unclaimed_and_inherited', $data);
	}

	public function prize_bonds_for_children()
	{
		$data['title'] = 'Buying Prize Bonds for a Child | Irish Prize Bonds';
		$data['description'] = 'How to buy Irish Prize Bonds as a gift for a child: documents needed, who controls the bond while they are a minor, and what changes at 18.';
		$this->load->view('prize_bonds_for_children', $data);
	}

	public function glossary()
	{
		$data['terms'] = array(
			array(
				'term' => 'Prize Bond',
				'definition' => 'A savings product issued by the Irish state, sold in units of &euro;6.25, where each bond earns no interest but is entered into a weekly draw for a tax-free cash prize instead. See <a href="' . base_url() . 'how-it-works">How Prize Bonds Work</a>.',
			),
			array(
				'term' => 'Bond Number',
				'definition' => 'The unique reference for a single bond &mdash; a 2&ndash;3 letter prefix followed by six digits (e.g. <code>AHU176759</code>). Each bond you hold has its own separate number and its own independent chance to win every draw.',
			),
			array(
				'term' => 'Draw',
				'definition' => 'The weekly random selection of winning bond numbers, held every week. See the full <a href="' . base_url() . 'schedule">draw schedule</a>.',
			),
			array(
				'term' => 'Jackpot Draw',
				'definition' => 'The draw on the last Friday of each calendar month, which includes one extra &euro;500,000 top prize on top of the usual weekly tiers.',
			),
			array(
				'term' => 'Prize Tier',
				'definition' => 'One of the fixed prize amounts awarded in a draw (for example &euro;100,000, &euro;1,000, or &euro;100) along with how many winners received it. See a draw\'s full tier breakdown on any <a href="' . base_url() . 'results">results</a> page.',
			),
			array(
				'term' => 'Prize Fund',
				'definition' => 'The total amount paid out across every prize tier in a single draw. Recalculated monthly based on the total value of bonds in circulation and net sales that month &mdash; it isn\'t a fixed figure.',
			),
			array(
				'term' => 'DIRT (Deposit Interest Retention Tax)',
				'definition' => 'The 33% tax deducted at source from interest on an ordinary Irish deposit account. Prize Bond winnings are fully exempt from DIRT, along with Income Tax, PRSI and Capital Gains Tax. See <a href="' . base_url() . 'are-prize-bonds-worth-it">Are Prize Bonds Worth It?</a> for how that compares to a deposit account.',
			),
			array(
				'term' => 'NTMA (National Treasury Management Agency)',
				'definition' => 'The state body that has held overall responsibility for the Prize Bond scheme since 1990. See our <a href="' . base_url() . 'history">History</a> page.',
			),
			array(
				'term' => 'Prize Bond Company',
				'definition' => 'The company that has run the Prize Bond scheme on behalf of the Minister for Finance since 1989, with day-to-day administration (sales, prize payments, customer service) handled by An Post through Ireland State Savings, and overall oversight held by the NTMA since 1990. See our <a href="' . base_url() . 'history">History</a> page for the full timeline.',
			),
			array(
				'term' => 'Ireland State Savings',
				'definition' => 'The public-facing brand, operated through An Post, under which Prize Bonds and other Irish state savings products are sold, registered and administered. Their official site is <a href="https://www.statesavings.ie" target="_blank" rel="noopener">statesavings.ie</a>.',
			),
			array(
				'term' => 'SSCN (State Savings Customer Number)',
				'definition' => 'The customer number issued once you\'re registered with Ireland State Savings, needed before your first purchase. See <a href="' . base_url() . 'how-to-buy-and-cash-in">How to Buy and Cash In</a>.',
			),
			array(
				'term' => 'Encashment / Repayment',
				'definition' => 'Cashing a bond in for its face value. Once repaid, a bond becomes inactive and stops being entered into future draws.',
			),
			array(
				'term' => '3-Month Rule',
				'definition' => 'Newly purchased bonds must be held for a minimum of 3 months (90 days) before they can be cashed in. This only restricts encashment &mdash; a new bond is entered into draws immediately. Bonds received through reinvested winnings are exempt from this wait.',
			),
			array(
				'term' => 'Reinvestment',
				'definition' => 'Automatically using a prize win to buy new Prize Bonds in the winner\'s name, rather than paying it out to a bank account. This is the default if you haven\'t registered bank details.',
			),
			array(
				'term' => 'Location',
				'definition' => 'The county (or, for overseas holders, country) officially associated with a winning bond, as published by Ireland State Savings. See our <a href="' . base_url() . 'stats/counties">County Stats</a> page for how wins break down by location.',
			),
			array(
				'term' => 'Nominated Parent/Guardian',
				'definition' => 'The adult who must confirm any transaction &mdash; cashing in, claiming a prize, and so on &mdash; on a bond held by a minor, until the child turns 18. See <a href="' . base_url() . 'prize-bonds-for-children">Buying Prize Bonds for a Child</a>.',
			),
			array(
				'term' => 'Grant of Probate',
				'definition' => 'Court confirmation of a will\'s executor, required by Ireland State Savings only where a deceased person\'s sole-name holdings totalled &euro;25,000 or more. See <a href="' . base_url() . 'unclaimed-and-inherited-prize-bonds">Unclaimed and Inherited Prize Bonds</a>.',
			),
		);

		$data['title'] = 'Glossary of Prize Bond Terms | Irish Prize Bonds';
		$data['description'] = 'Plain-English definitions for Prize Bond terms: bond number, draw, jackpot, prize tier, DIRT, SSCN, encashment, and more.';
		$this->load->view('glossary', $data);
	}

	public function data_sources()
	{
		$data['title'] = 'How We Source and Verify Our Draw Results | Irish Prize Bonds';
		$data['description'] = 'Where our Irish Prize Bond draw results come from, how often they are updated, and what happens when a result cannot be confirmed yet.';
		$this->load->view('data_sources', $data);
	}

	public function worth_it()
	{
		$this->load->database();

		$data['stats'] = $this->db->select(
				"COUNT(*) as draw_count, MIN(draw_date) as first_date, MAX(draw_date) as last_date, SUM(total_prize_fund) as total_fund, SUM(total_prizes_count) as total_prizes"
			)
			->from('draws')->where('published', 1)->get()->row();

		$data['winner_count'] = $this->db->select('COUNT(DISTINCT bond_number) as c')->from('draw_winners')->get()->row()->c;

		$data['title'] = 'Are Irish Prize Bonds Worth It? | Irish Prize Bonds';
		$data['description'] = 'A balanced, data-informed look at whether Irish Prize Bonds are worth it compared to an ordinary tax-free deposit account, using our own tracked draw results.';
		$this->load->view('worth_it', $data);
	}

}
