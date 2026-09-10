<div class="modal-dialog rt-modal" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\Restaurant\TableController::class, 'update'], [$table->id]), 'method' => 'PUT', 'id' => 'table_edit_form' ]) !!}
    <div class="rt-modal-head">
      <div class="rt-modal-title-wrap">
        <span class="rt-modal-icon rt-modal-icon-violet"><i class="fa fa-edit"></i></span>
        <div>
          <h4 class="rt-modal-title">@lang('restaurant.edit_data')</h4>
          <p class="rt-modal-help">@lang('restaurant.edit_table')</p>
        </div>
      </div>
      <button type="button" class="rt-modal-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
    </div>
    <div class="rt-modal-body">
      <div class="rt-field">
        {!! Form::label('location_id', __('purchase.business_location').':*', ['class' => 'rt-label']) !!}
        {!! Form::select('location_id', $business_locations, $table->location_id, ['class' => 'form-control select2 rt-input', 'id' => 'location_id', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('floor_id', __('restaurant.floor').':', ['class' => 'rt-label']) !!}
        {!! Form::select('floor_id', $floors, $table->floor_id, ['class' => 'form-control rt-input', 'id' => 'floor_id', 'placeholder' => __('restaurant.select_floor'), 'data-selected' => $table->floor_id]); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('name', __('restaurant.table_name') . ':*', ['class' => 'rt-label']) !!}
        {!! Form::text('name', $table->name, ['class' => 'form-control rt-input', 'required', 'placeholder' => __('restaurant.table_name')]); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('seats', __('restaurant.seats') . ':', ['class' => 'rt-label']) !!}
        {!! Form::number('seats', $table->seats, ['class' => 'form-control rt-input', 'min' => 1, 'placeholder' => __('restaurant.seats')]); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('status', __('restaurant.table_status') . ':', ['class' => 'rt-label']) !!}
        {!! Form::select('status', [
            0 => __('restaurant.available').' (0)',
            1 => __('restaurant.reserved').' (1)',
            2 => __('restaurant.occupied').' (2)',
        ], !empty($has_open_order) ? 2 : ($table->status ?? 0), [
            'class' => 'form-control rt-input',
            'id' => 'status',
            'disabled' => !empty($has_open_order),
        ]); !!}
        @if(!empty($has_open_order))
          <p class="rt-modal-help" style="margin-top:6px;">@lang('restaurant.cannot_change_status_with_open_order')</p>
        @endif
      </div>
      <div class="rt-field rt-reservation-fields" style="{{ (empty($has_open_order) && (int)($table->status ?? 0) === 1) ? '' : 'display:none;' }}">
        {!! Form::label('reserved_guest_name', __('restaurant.reservation_guest_name').':*', ['class' => 'rt-label']) !!}
        {!! Form::text('reserved_guest_name', $table->reserved_guest_name, ['class' => 'form-control rt-input', 'id' => 'reserved_guest_name']); !!}
      </div>
      <div class="rt-field rt-reservation-fields" style="{{ (empty($has_open_order) && (int)($table->status ?? 0) === 1) ? '' : 'display:none;' }}">
        {!! Form::label('reserved_guest_phone', __('restaurant.reservation_guest_phone').':', ['class' => 'rt-label']) !!}
        {!! Form::text('reserved_guest_phone', $table->reserved_guest_phone, ['class' => 'form-control rt-input', 'id' => 'reserved_guest_phone']); !!}
      </div>
      <div class="rt-field rt-reservation-fields" style="{{ (empty($has_open_order) && (int)($table->status ?? 0) === 1) ? '' : 'display:none;' }}">
        {!! Form::label('reserved_note', __('restaurant.reservation_note').':', ['class' => 'rt-label']) !!}
        {!! Form::text('reserved_note', $table->reserved_note, ['class' => 'form-control rt-input', 'id' => 'reserved_note']); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('description', __('restaurant.short_description') . ':', ['class' => 'rt-label']) !!}
        {!! Form::text('description', $table->description, ['class' => 'form-control rt-input','placeholder' => __('restaurant.short_description')]); !!}
      </div>
    </div>
    <div class="rt-modal-foot">
      <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
      <button type="submit" class="rt-btn-primary">@lang('messages.update')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
