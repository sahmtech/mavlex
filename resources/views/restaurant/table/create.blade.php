<div class="modal-dialog rt-modal" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\Restaurant\TableController::class, 'store']), 'method' => 'post', 'id' => 'table_add_form' ]) !!}
    <div class="rt-modal-head">
      <div class="rt-modal-title-wrap">
        <span class="rt-modal-icon rt-modal-icon-indigo"><i class="fa fa-plus"></i></span>
        <div>
          <h4 class="rt-modal-title">@lang('restaurant.add_new_table')</h4>
          <p class="rt-modal-help">@lang('restaurant.manage_your_tables')</p>
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
        {!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2 rt-input', 'id' => 'location_id', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('floor_id', __('restaurant.floor').':', ['class' => 'rt-label']) !!}
        {!! Form::select('floor_id', [], null, ['class' => 'form-control rt-input', 'id' => 'floor_id', 'placeholder' => __('restaurant.select_floor')]); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('name', __('restaurant.table_name') . ':*', ['class' => 'rt-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control rt-input', 'required', 'placeholder' => __('restaurant.table_name') ]); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('seats', __('restaurant.seats') . ':', ['class' => 'rt-label']) !!}
        {!! Form::number('seats', null, ['class' => 'form-control rt-input', 'min' => 1, 'placeholder' => __('restaurant.seats')]); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('status', __('restaurant.table_status') . ':', ['class' => 'rt-label']) !!}
        {!! Form::select('status', [
            0 => __('restaurant.available').' (0)',
            1 => __('restaurant.reserved').' (1)',
            2 => __('restaurant.occupied').' (2)',
        ], 0, ['class' => 'form-control rt-input', 'id' => 'status']); !!}
      </div>
      <div class="rt-field rt-reservation-fields" style="display:none;">
        {!! Form::label('reserved_guest_name', __('restaurant.reservation_guest_name').':*', ['class' => 'rt-label']) !!}
        {!! Form::text('reserved_guest_name', null, ['class' => 'form-control rt-input', 'id' => 'reserved_guest_name']); !!}
      </div>
      <div class="rt-field rt-reservation-fields" style="display:none;">
        {!! Form::label('reserved_guest_phone', __('restaurant.reservation_guest_phone').':', ['class' => 'rt-label']) !!}
        {!! Form::text('reserved_guest_phone', null, ['class' => 'form-control rt-input', 'id' => 'reserved_guest_phone']); !!}
      </div>
      <div class="rt-field rt-reservation-fields" style="display:none;">
        {!! Form::label('reserved_note', __('restaurant.reservation_note').':', ['class' => 'rt-label']) !!}
        {!! Form::text('reserved_note', null, ['class' => 'form-control rt-input', 'id' => 'reserved_note']); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('description', __('restaurant.short_description') . ':', ['class' => 'rt-label']) !!}
        {!! Form::text('description', null, ['class' => 'form-control rt-input','placeholder' => __('restaurant.short_description')]); !!}
      </div>
    </div>
    <div class="rt-modal-foot">
      <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
      <button type="submit" class="rt-btn-primary">@lang('messages.save')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
