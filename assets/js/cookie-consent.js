/**
 * Minimal cookie consent gate.
 *
 * Stores the visitor's choice in localStorage. Essential cookies (the
 * site's own session cookie) run regardless, since the site can't function
 * without them. Anything non-essential (analytics, etc.) must NOT fire
 * until consent is given — so don't just drop a <script src="...ga.js">
 * tag in the page. Instead, mark it inert until consent is granted:
 *
 *   <script type="text/plain" data-cookieconsent="analytics">
 *     // your Google Analytics / GA4 snippet here
 *   </script>
 *
 * type="text/plain" means the browser won't execute it. Once the visitor
 * accepts, this script rewrites every such tag to type="text/javascript"
 * and re-inserts it, which does execute it. If consent was already
 * accepted on a previous visit, this runs immediately on page load.
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

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	ready(function () {
		var banner = document.getElementById('cookie-consent-banner');
		var acceptBtn = document.getElementById('cookie-consent-accept');
		var rejectBtn = document.getElementById('cookie-consent-reject');
		var consent = getConsent();

		if (consent === 'accepted') {
			activateGatedScripts();
		} else if (consent === null && banner) {
			banner.style.display = 'flex';
		}

		if (acceptBtn) {
			acceptBtn.addEventListener('click', function () {
				setConsent('accepted');
				activateGatedScripts();
				if (banner) banner.style.display = 'none';
			});
		}
		if (rejectBtn) {
			rejectBtn.addEventListener('click', function () {
				setConsent('rejected');
				if (banner) banner.style.display = 'none';
			});
		}
	});
})();
