<div class="modal-dialog rt-modal" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\Restaurant\FloorController::class, 'store']), 'method' => 'post', 'id' => 'floor_add_form' ]) !!}
    <div class="rt-modal-head">
      <div class="rt-modal-title-wrap">
        <span class="rt-modal-icon rt-modal-icon-indigo"><i class="fa fa-building"></i></span>
        <div>
          <h4 class="rt-modal-title">@lang('restaurant.add_new_floor')</h4>
          <p class="rt-modal-help">@lang('restaurant.manage_your_floors')</p>
        </div>
      </div>
      <button type="button" class="rt-modal-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
    </div>
    <div class="rt-modal-body">
      @if(count($business_locations) == 1)
        @php
            $default_location = current(array_keys($business_locations->toArray()))
        @endphp
      @else
        @php $default_location = null; @endphp
      @endif
      <div class="rt-field">
        {!! Form::label('location_id', __('purchase.business_location').':*', ['class' => 'rt-label']) !!}
        {!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2 rt-input', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('name', __('restaurant.floor_name') . ':*', ['class' => 'rt-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control rt-input', 'required', 'placeholder' => __('restaurant.floor_name')]); !!}
      </div>
    </div>
    <div class="rt-modal-foot">
      <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
      <button type="submit" class="rt-btn-primary">@lang('messages.save')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
