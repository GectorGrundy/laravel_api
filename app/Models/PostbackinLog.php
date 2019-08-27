<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PostbackinLog extends AbstractEntity
{
    use DispatchesJobs;

    protected $fillable = ['api_key', 'lead_id', 'request', 'ip', 'response_code', 'response'];
    protected $appends = ['request_array', 'response_array'];
    protected $hidden = ['updated_at'];

    public function getRequestArrayAttribute()
    {
        return $this->attributes['request_array'] = json_decode($this->request);
    }

    public function getResponseArrayAttribute()
    {
        return $this->attributes['response_array'] = empty($this->response) ? [] : json_decode($this->response);
    }
}
