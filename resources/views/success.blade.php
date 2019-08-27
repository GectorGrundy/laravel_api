@extends('layouts.success')

@section('styles')
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

    .order_info_item_wrap {
      text-align: left;
      display: inline-block;
      padding: 0;
    }

    .order_info_item {
      margin: 11px 0;
    }

    .order_info_item_value {
      width: 150px;
      display: inline-block;
      font-weight: bold;
      font-style: normal;
    }

    .success_page_main_title {
      font-size: 36px;
      line-height: 44px;
      color: #313e47;
      text-align: center;
      text-transform: uppercase;
      font-weight: bold;
    }

    .success_page_bottom_message {
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      margin: 20px 0;
    }

    @media (max-width: 320px) {
      .order_info_item_value {
        display: block;
      }
    }

    @media (max-width: 600px) {
      .success_page_main_title {
        font-size: 27px;
      }
    }
  </style>
@endsection

@section('content')
  <div class="container">
    <div class="content">
      <div class="row">
        <div class="col-lg-1 col-md-1 col-sm-1"></div>
        <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
          <h2 class="success_page_main_title">{{ trans('go.title')}}</h2>
          <div class="success_page_top_message">{{ trans('go.success_thanks_top_message') }}</div>
          <h3 class="success_page_bottom_message">{{ trans('go.thanks_bottom_message') }}</h3>
          <div class="order_info_wrap">
            <ul class="order_info_item_wrap">
              <li class="order_info_item">
                <span class="order_info_item_value">{{ trans('go.name') }}: </span>
                {{ $name }}
              </li>
              <li class="order_info_item">
                <span class="order_info_item_value">{{ trans('go.phone') }}: </span>
                {{ $phone }}
              </li>
            </ul>
            @if($show_backlink)
              <p>{{ trans('go.is_you_made_a_mistake') }}
                <a href="{{ $backlink }}">{{ trans('go.fill_in_form') }}</a>
              </p>
            @else
              <p>{{ trans('go.is_you_made_a_mistake') }}
                {{ trans('go.fill_in_form') }}
              </p>
            @endif
            @if((int)$landing_info['is_email_on_success'] === 1)
              <h3 style="margin: 30px 0 0 0;">{{ trans('go.for_receiving_special_offers') }}</h3>
              <div class="row">
                <div class="col-lg-4 col-md-3 col-sm-3"></div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                  <form id="email_form" class="custom_form" method="POST" onsubmit="updateOrderEmail();">
                    <input type="hidden" name="lead_hash" value="{{ $lead_hash }}">
                    <div class="form-group">
                      <label for="email">{{ trans('go.email') }}</label>
                      <input type="email" class="form-control" name="email" required>
                    </div>
                    <button type="button" class="btn btn-info" onclick="updateOrderEmail();">
                      {{ trans('go.send') }}
                    </button>
                  </form>
                </div>
                <div class="col-lg-4 col-md-3 col-sm-3"></div>
              </div>
            @endif
            @if((int)$landing_info['is_address_on_success'] === 1)
              <h3 style="margin: 30px 0 0 0;">{{ trans('go.fill_in_address') }}</h3>
              <div class="row">
                <div class="col-lg-4 col-md-3 col-sm-3"></div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                  <form id="address_form" class="custom_form" method="POST" onsubmit="updateOrderAddress();">
                    <input type="hidden" name="lead_hash" value="{{ $lead_hash }}">
                    <div class="form-group">
                      <label for="address">{{ trans('go.address') }}</label>
                      <input type="text" class="form-control" name="address" required>
                    </div>
                    <button type="button" class="btn btn-info" onclick="updateOrderAddress();">
                      {{ trans('go.send') }}
                    </button>
                  </form>
                </div>
                <div class="col-lg-4 col-md-3 col-sm-3"></div>
              </div>
            @endif
          </div>
        </div>
        <div class="col-lg-1 col-md-1 col-sm-1"></div>
      </div>
    </div>
  </div>
@endsection