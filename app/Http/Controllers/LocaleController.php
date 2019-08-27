<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Models\Locale;

class LocaleController extends Controller
{
	use Helpers;

	public function getList()
	{
		$locales = Locale::all();

		return ['response' => $locales, 'status_code' => 200];
	}
}
