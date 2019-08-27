(function () {
    var window_height = $(window).height(),
        needle_scrolled_px = window_height * 1.5,
        px_were_scrolled = 0,
        top_offset,
        prev_top_offset = 0,
        offset_difference;

    var track_scroll = debounce(function () {
        top_offset = window.pageYOffset;
        offset_difference = Math.abs(top_offset - prev_top_offset);
        px_were_scrolled += offset_difference;

        if (px_were_scrolled >= needle_scrolled_px) {
            try {
                FBEvents.ViewContent();
            } catch (e){
                console.info(e);
            }
            window.removeEventListener('scroll', track_scroll);
        }
        prev_top_offset = top_offset;
    }, 50);

    window.addEventListener('scroll', track_scroll);

})();

function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};