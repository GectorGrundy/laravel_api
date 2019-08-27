<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class SyncLabelsRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:offers,id',
            'labels' => 'array',
            'labels.*' => 'array|size:2',
            'labels.*.label_id' => 'exists:offer_labels,id',
            'labels.*.available_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_sync_labels_error');
    }
}
