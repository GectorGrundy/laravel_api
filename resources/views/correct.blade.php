<!DOCTYPE html>
<html>
<head>
	<title>{{ trans('go.title') }}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	@include('parts.reset_css')

	<style type="text/css">
		.container {
			line-height: 1;
			font-family: Arial, serif;
			font-size: 15px;
			color: #313e47;
			width: 100%;
			background: #fff;
		}

		.content .order_number span {
			color: #e14740;
		}

		.content {
			padding-top: 30px;
		}

		.success_page_top_message {
			text-align: center;
		}

		.order_info_wrap {
			text-align: center;
		}

		.success_page_main_title {
			font-size: 36px;
			line-height: 44px;
			color: #313e47;
			text-align: center;
			text-transform: uppercase;
			font-weight: bold;
		}

		@media (max-width: 600px) {
			.success_page_main_title {
				font-size: 27px;
			}
		}
	</style>

	@include('parts.custom_bootstrap')
	@include('parts.custom_css')

</head>
<body>
<div class="container">
	<div class="content">
		<h2 class="success_page_main_title">{{ trans('go.title')}}</h2>
		<div class="success_page_top_message">{{ trans('go.correct_thanks_top_message') }}</div>
		<div class="row">
			<div class="col-lg-4 col-md-4 col-sm-3 col-xs-1"></div>
			<div class="col-lg-4 col-md-4 col-sm-6 col-xs-10">
				<div class="order_info_wrap">
					<form action="/correct.html?{{ $_SERVER['QUERY_STRING'] }}" id="order_form" class="custom_form" method="POST" onsubmit="validateForm();">
						<input type="hidden" name="lead_hash" value="{{ $lead_hash }}">
						<div class="form-group">
							<label for="target_geo_hash">{{ trans('go.country') }}</label>
							<select name="target_geo_hash" class="form-control" id="target_geo_hash">
								@foreach($target_geo_list AS $target_geo)
									<option value="{{ $target_geo['target_geo_hash'] }}"
									        @if($target_geo['target_geo_hash'] == $target_geo_hash) selected @endif
									>{{ $target_geo['country_title'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label for="client">{{ trans('go.name') }}</label>
							<input type="text" class="form-control" name="client" id="client" value="{{ $name }}" readonly>
						</div>
						<div class="form-group">
							<label for="phone">{{ trans('go.phone') }}</label>
							<input type="text" class="form-control" name="phone" value="{{ $phone }}"
							       required="true" pattern="[\+0-9]{6,}" title="{{ trans('go.incorrect_phone_number_msg') }}">
						</div>
						<button type="button" class="btn btn-info" onclick="validateForm();">{{ trans('go.edit') }}</button>
					</form>
				</div>
			</div>
			<div class="col-lg-4 col-md-4 col-sm-3 col-xs-1"></div>
		</div>
	</div>
</div>

<script>
	function validateForm() {
		var target_geo_hash = document.getElementById('target_geo_hash').value;

		if (target_geo_hash == '00000000') {
			alert('{{ trans('go.incorrect_target_geo_msg')  }}');
			return false;
		}

		document.getElementById('order_form').submit();
	}
</script>

{!! $yandex_metrika_script !!}
{!! $custom_html_code !!}
{!! $rating_mail_ru_script !!}
{!! $vk_widget_script !!}
{!! $google_analitycs_script !!}

</body>
</html>