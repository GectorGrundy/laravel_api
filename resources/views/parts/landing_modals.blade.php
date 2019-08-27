<div id="affcb" class="affcb__manager affcb__manager-woman1 {{ $operator_class }}" style="display: none;">
  <a href="#">
    <div class="affcb__manager-circle"></div>
    <div class="affcb__manager-fill"></div>
    <div class="affcb__manager-border"></div>
    <div class="affcb__manager-img"></div>
  </a>
</div>

<div id="affcb-form" class="modal affcb-form hidden">
  <div class="modal-content">
    <div class="modal-header">{{ trans('go.like_offer_q') }}
      <div class="affcb-form-rectangle"></div>
    </div>
    <div class="modal-body">
      <p>{{ trans('go.back_call_text') }}</p>
      <form class="form_order" method="POST" action="">
        <div class="target_list_wrap m-b-sm">
          <select class="js-select" name="target_geo_hash"></select>
        </div>
        <input type="text" name="client" placeholder="{{ trans('go.name') }}">
        <input type="text" name="phone" placeholder="{{ trans('go.phone') }}">
        <input type="submit" value="{{ trans('go.call_me') }}">
      </form>
      <p class="affcb-form-clock">{{ trans('go.operator_call_you') }}</p>
    </div>
  </div>
</div>

<link rel="stylesheet" href="{{ config('env.cdn_host') }}/css/landing.min.css">
<script src="{{ config('env.cdn_host') }}/js/modal.js"></script>