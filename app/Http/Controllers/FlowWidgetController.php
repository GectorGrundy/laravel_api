<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Models\FlowWidget;

class FlowWidgetController extends Controller
{
	use Helpers;

	public function getList(): array
    {
		$flow_widgets = FlowWidget::all();

		return ['response' => $flow_widgets, 'status_code' => 200];
	}
}
