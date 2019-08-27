const {mix} = require('laravel-mix');
const production = process.env.NODE_ENV === 'production';

mix.disableNotifications();

if (!production) {
    mix.sourceMaps();
}

mix
    .copy('resources/assets/js/modal.js', 'public/js/modal.js')
    .copy('resources/assets/css/landing.css', 'public/css')
    .minify('public/css/landing.css')

    .babel('resources/assets/js/activity_tracker.js', 'public/js/activity_tracker.js')
    .babel('resources/assets/js/landing.js', 'public/js/landing.js')
    .babel('resources/assets/js/dtime.js', 'public/js/dtime.js')
    .babel('resources/assets/js/freeze.js', 'public/js/freeze.js')
    .babel('resources/assets/js/success.js', 'public/js/success.js')
    .babel('resources/assets/js/transit.js', 'public/js/transit.js');


