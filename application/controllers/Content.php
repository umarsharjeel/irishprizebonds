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
				'q' => 'How far back do your results go?',
				'a' => "We're continuously building a historical archive of draw results. Check the <a href=\"" . base_url() . 'results/archive">Draw Archive</a> for the current range of draws covered.',
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

		$data['title'] = 'Prize Bond Draw Dates & Schedule | Irish Prize Bonds';
		$data['description'] = 'When is the next Irish Prize Bond draw? Weekly draw schedule and monthly jackpot dates explained.';
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
