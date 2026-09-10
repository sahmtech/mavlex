<div class="modal-dialog rt-modal" role="document">
  <div class="modal-content">
    <div class="rt-modal-head">
      <div class="rt-modal-title-wrap">
        <span class="rt-modal-icon rt-modal-icon-teal"><i class="fa fa-qrcode"></i></span>
        <div>
          <h4 class="rt-modal-title">@lang('restaurant.qr_code')</h4>
          <p class="rt-modal-help">{{ $table->name }}</p>
        </div>
      </div>
      <button type="button" class="rt-modal-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
    </div>
    <div class="rt-modal-body rt-modal-body-center">
      <p class="rt-modal-meta">
        @if($table->floor)
          {{ $table->floor->name }} ·
        @endif
        {{ $table->name }}
        @if($table->seats)
          · {{ $table->seats }} @lang('restaurant.seats')
        @endif
      </p>
      <div class="rt-qr-frame">
        <img src="data:image/png;base64,{{ $qr_png }}" alt="QR" width="220" height="220">
      </div>
    </div>
    <div class="rt-modal-foot">
      <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
      <a href="{{ action([\App\Http\Controllers\Restaurant\TableController::class, 'qrDownload'], [$table->id]) }}" class="rt-btn-primary">
        <i class="fa fa-download"></i> @lang('restaurant.download_qr')
      </a>
    </div>
  </div>
</div>
