<?php
/**
 * Created by PhpStorm.
 * User: faith
 * Date: 3/29/18
 * Time: 2:20 PM
 */

namespace App\Strategies\OfferListing;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

interface OfferListingStrategy
{

    public function get(Request $request, Builder $offers);
}