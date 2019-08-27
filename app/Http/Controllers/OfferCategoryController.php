<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Locale;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\OfferCategory as R;
use App\Models\OfferCategory;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;

class OfferCategoryController extends Controller
{
    use Helpers;

    public function create(R\CreateRequest $request)
    {
        $category = OfferCategory::create($request->all());

        $category->syncTitleTranslations([[
            'locale_id' => Locale::EN,
            'title' => $request->input('title_en')
        ]]);

        return $this->response->accepted(null, [
            'message' => trans('offer_categories.on_create_success'),
            'response' => $category,
            'status_code' => 202
        ]);
    }

    public function edit(R\EditRequest $request)
    {
        $category = OfferCategory::find($request->input('offer_category_id'));

        $category->update($request->all());

        $category->syncTitleTranslations([[
            'locale_id' => Locale::EN,
            'title' => $request->input('title_en')
        ]]);

        return $this->response->accepted(null, [
            'message' => trans('offer_categories.on_edit_success'),
            'response' => $category,
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        OfferCategory::destroy($request->input('offer_category_id'));

        return $this->response->accepted(null, [
            'message' => trans('offer_categories.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getById(R\GetByIdRequest $request)
    {
        $category = OfferCategory::with($request->input('with', []))
            ->find($request->input('offer_category_id'));

        return ['response' => $category, 'status_code' => 200];
    }

    public function getList(R\GetListRequest $request)
    {
        $categories = OfferCategory::with($request->input('with', []))
            ->isAdult($request->input('is_adult'))
            ->latest('id')
            ->get();

        return ['response' => $categories, 'status_code' => 200];
    }

    public function getListForOfferFilter()
    {
        $user = \Auth::user();

        $result = OfferCategory::all()
            ->map(function (OfferCategory $offer_category) use ($user) {

                $offers = Offer::whereHas('offer_categories', function (Builder $builder) use ($offer_category) {
                    return $builder->where('offer_category_id', $offer_category['id']);
                })
                    ->excludeArchived()
                    ->get();

                if ($user->isPublisher()) {
                    $offers = Offer::rejectInactiveOffersForPublisher($offers);
                }

                $offer_category['offers_count'] = $offers->count();
                return $offer_category;
            });

        return [
            'response' => $result,
            'status_code' => 200
        ];
    }
}
