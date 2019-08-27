var landing_url = "{LANDING_URL}";
var LANDING_TARGET_BLANK = "{LANDING_TARGET_BLANK}";
var extra_flow_url = "{EXTRA_FLOW_URL}";
var IS_MOBILE = "{IS_MOBILE}";
var FLOW_IS_NOBACK = "{FLOW_IS_NOBACK}";
var FLOW_IS_COMEBACKER = "{FLOW_IS_COMEBACKER}";

$(document).ready(function(){

    try {
        FBEvents.init();
    } catch (e){
        console.info(e);
    }

	function changeLinksUrl() {
		var links = document.querySelectorAll('a');

		if (extra_flow_url != '') {
			var goToExtraFlow = function () {
				location.href = extra_flow_url;
			};
		}
        if (hasParamInUrl(landing_url, 'from')) {
            landing_url = replaceQueryParam(landing_url, 'from', 'transit')
        } else {
            landing_url += '&from=transit'
        }

		for (var i = 0, all_links = links.length; i < all_links; i++) {
			links[i].href = landing_url;
            if (LANDING_TARGET_BLANK) {
                links[i].target = '_blank';
            }

			if (extra_flow_url !== '') {
				links[i].addEventListener('click', goToExtraFlow, false);
			}
		}
	}

	if (history.pushState && FLOW_IS_NOBACK && IS_MOBILE) {
		(function () {
			var t;
			try {
				for (t = 0; 3 > t; ++t) {
					history.pushState({}, "", location.href);
				}

				onpopstate = function (t) {
                    if (hasParamInUrl(landing_url, 'from')) {
                        landing_url = replaceQueryParam(landing_url, 'from', 'noback')
                    } else {
                        landing_url += '&from=noback'
                    }

                    t.state && location.replace(landing_url)
				}
			} catch (o) {
                console.log(o);
			}
		})();
	}

	changeLinksUrl();
});

function hasParamInUrl(url, param) {
    return (url.indexOf(param) !== -1);
}

function replaceQueryParam(url, param, value) {
    var reg = new RegExp('([?|&])' + param + '=(.*?)[^&]*', 'gi');
    return url.replace(reg, "$1" + param + '=' + value);
}