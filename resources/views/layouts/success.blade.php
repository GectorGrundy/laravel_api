<!DOCTYPE html>
<html>
<head>
  {!! $base_tag !!}
  <title>{{ trans('go.title') }}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  @include('parts.reset_css')
  @include('parts.custom_bootstrap')
  @include('parts.custom_css')

  @yield('styles')
</head>
<body>

@yield('content')

<script>
    TRANSLATIONS = {!! json_encode(Lang::get('go')) !!}
        lead_payout = {{ $lead_payout }}
        lead_currency_code = '{{ $lead_currency_code }}'
</script>
{!! $yandex_metrika_script !!}
{!! $facebook_pixel_script !!}
{!! $custom_html_code !!}
{!! $rating_mail_ru_script !!}
{!! $vk_widget_script !!}
{!! $google_analitycs_script !!}

<script>
  {!! File::get(public_path('js/success.js')) !!}
</script>

@yield('scripts')

</body>
</html>