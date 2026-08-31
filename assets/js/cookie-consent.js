/**
 * Minimal cookie consent gate.
 *
 * Stores the visitor's choice in localStorage. Essential cookies (the
 * site's own session cookie — only actually set on the pages that use it,
 * e.g. Contact Us; most public pages set none at all) run regardless, since
 * the site can't function without them where they're genuinely needed.
 * Anything non-essential (analytics, etc.) must NOT fire until consent is
 * given — so don't just drop a <script src="...ga.js"> tag in the page.
 * Instead, mark it inert until consent is granted:
 *
 *   <script type="text/plain" data-cookieconsent="analytics">
 *     // your Google Analytics / GA4 snippet here
 *   </script>
 *
 * type="text/plain" means the browser won't execute it. Once the visitor
 * accepts, this script rewrites every such tag to type="text/javascript"
 * and re-inserts it, which does execute it. If consent was already
 * accepted on a previous visit, this runs immediately on page load.
 *
 * Withdrawal: a "Cookie Settings" link (id="cookie-settings-link", see
 * website/footer.php) re-opens the same banner at any time — GDPR Art. 7(3)
 * requires withdrawal to be as easy as giving consent. Re-saving a choice
 * reloads the page, so a switch to Reject takes effect cleanly (the gated
 * scripts stay untouched/inert on the fresh load) rather than trying to
 * half-undo an already-running analytics script mid-session. Switching to
 * Reject also clears any GA cookies already set, since flipping the stored
 * choice alone doesn't remove cookies GA already wrote to the browser.
 */
(function () {
	var STORAGE_KEY = 'ipb_cookie_consent'; // 'accepted' | 'rejected'

	function getConsent() {
		try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
	}
	function setConsent(value) {
		try { localStorage.setItem(STORAGE_KEY, value); } catch (e) {}
	}

	function activateGatedScripts() {
		var gated = document.querySelectorAll('script[type="text/plain"][data-cookieconsent="analytics"]');
		gated.forEach(function (oldScript) {
			var newScript = document.createElement('script');
			for (var i = 0; i < oldScript.attributes.length; i++) {
				var attr = oldScript.attributes[i];
				if (attr.name !== 'type') newScript.setAttribute(attr.name, attr.value);
			}
			newScript.text = oldScript.text;
			oldScript.parentNode.replaceChild(newScript, oldScript);
		});
	}

	// Removes any cookie GA(4) has set (_ga, _ga_<container>, _gid, etc.) — tried
	// against every domain variant GA might have used (exact host, and the
	// leading-dot root-domain form it defaults to), since document.cookie can only
	// clear a cookie by matching the same domain/path it was set with.
	function clearAnalyticsCookies() {
		var host = window.location.hostname;
		var rootHost = host.replace(/^www\./, '');
		var domains = [host, '.' + host, '.' + rootHost];
		document.cookie.split(';').forEach(function (c) {
			var name = c.split('=')[0].trim();
			if (!/^_ga/.test(name)) return;
			domains.forEach(function (d) {
				document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=' + d;
			});
			document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
		});
	}

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	ready(function () {
		var banner = document.getElementById('cookie-consent-banner');
		var acceptBtn = document.getElementById('cookie-consent-accept');
		var rejectBtn = document.getElementById('cookie-consent-reject');
		var settingsLink = document.getElementById('cookie-settings-link');
		var consent = getConsent();

		if (consent === 'accepted') {
			activateGatedScripts();
		} else if (consent === null && banner) {
			banner.style.display = 'flex';
		}

		if (acceptBtn) {
			acceptBtn.addEventListener('click', function () {
				var isChange = consent !== null;
				setConsent('accepted');
				if (isChange) {
					location.reload(); // fresh load so the gated scripts activate cleanly
					return;
				}
				activateGatedScripts();
				if (banner) banner.style.display = 'none';
			});
		}
		if (rejectBtn) {
			rejectBtn.addEventListener('click', function () {
				var wasAccepted = consent === 'accepted';
				setConsent('rejected');
				clearAnalyticsCookies();
				if (wasAccepted) {
					location.reload(); // drop the now-inert gtag/dataLayer state cleanly
					return;
				}
				if (banner) banner.style.display = 'none';
			});
		}
		if (settingsLink && banner) {
			settingsLink.addEventListener('click', function () {
				banner.style.display = 'flex';
			});
		}
	});
})();
