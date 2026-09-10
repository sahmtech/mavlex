<div class="modal-dialog rt-modal" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\Restaurant\FloorController::class, 'update'], [$floor->id]), 'method' => 'PUT', 'id' => 'floor_edit_form' ]) !!}
    <div class="rt-modal-head">
      <div class="rt-modal-title-wrap">
        <span class="rt-modal-icon rt-modal-icon-violet"><i class="fa fa-edit"></i></span>
        <div>
          <h4 class="rt-modal-title">@lang('restaurant.edit_data')</h4>
          <p class="rt-modal-help">@lang('restaurant.edit_floor')</p>
        </div>
      </div>
      <button type="button" class="rt-modal-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
    </div>
    <div class="rt-modal-body">
      <div class="rt-field">
        {!! Form::label('location_id', __('purchase.business_location').':*', ['class' => 'rt-label']) !!}
        {!! Form::select('location_id', $business_locations, $floor->location_id, ['class' => 'form-control select2 rt-input', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
      </div>
      <div class="rt-field">
        {!! Form::label('name', __('restaurant.floor_name') . ':*', ['class' => 'rt-label']) !!}
        {!! Form::text('name', $floor->name, ['class' => 'form-control rt-input', 'required', 'placeholder' => __('restaurant.floor_name')]); !!}
      </div>
    </div>
    <div class="rt-modal-foot">
      <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
      <button type="submit" class="rt-btn-primary">@lang('messages.update')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
