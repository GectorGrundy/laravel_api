<?php
declare(strict_types=1);

Route::get('/rdr/{clickid}', 'RedirectToExternalLanding')->name('external.redirect');
Route::match(['GET', 'POST'], '/lead/create', 'CreatePixelLead');
Route::post('/addUpsale', 'CodOrderController@addUpsale');

// Fallback request for static files
Route::get('/apollofiles/{site_type}/{site_hash}/{filepath?}', 'SymlinkCreator')
    ->where('filepath', '.*')
    ->where('site_type', 'prelanding|landing')
    ->name('symlink');

Route::group(['middleware' => [
    'go_fallback_locale',
    'visitor.info',
    'current_domain_info',
    'nginx_for_static',
    'domain.site.flow.offer.info',
    'visitor.target_geo',
    'request_parameters',
    'visitor.cookie',
    'landing.locale',
    'landing.cors',
]], function () {

    // Tds
    Route::get('/click/{flow_hash}', 'FallbackRequestManager')->name('tds');

    Route::get('/success/{lead_hash}/{is_iframe}', 'CodOrderController@showSuccessPage')
        ->name('cod_lead.success');
    Route::post('/correct.html', 'CodOrderController@correctOrder');
    Route::get('/correct/{lead_hash}', 'CodOrderController@showCorrectPage')
        ->name('cod_lead.correct');
    Route::post('/updateOrderEmail', 'CodOrderController@updateOrderEmail');
    Route::post('/updateOrderAddress', 'CodOrderController@updateOrderAddress');

    Route::post('/order.html', 'OrderManager')->name('order.action');

    Route::post('/errors/cloaking', 'ErrorsController@cloaking');
    Route::post('/temp_lead/create', 'TempLeadController@create');
    Route::get('/privacypolicy.html', 'ShowPrivacyPolicy');
    Route::get('/terms.html', 'ShowTerms');
    Route::get('/returns.html', 'ShowReturns');

    // Show transits and landings
    Route::get('{path?}', 'FallbackRequestManager')
        ->where('path', '.*')
        ->name('fallback');
});
