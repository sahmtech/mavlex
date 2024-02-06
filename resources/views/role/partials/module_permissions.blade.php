 @if (count($module_permissions) > 0)
     @php
         $module_role_permissions = [];
         if (!empty($role_permissions)) {
             $module_role_permissions = $role_permissions;
         }
     @endphp
     @foreach ($module_permissions as $key => $value)
         <hr>
         <div class="row check_group">
             <div class="col-md-2">
                 <h4>{{ $key }}</h4>
             </div>
             <div class="col-md-10">
                 <div class="col-md-12" style="padding: 0">
                     <div class="checkbox">
                         <label>
                             <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                         </label>
                     </div>
                 </div>
                 @foreach ($value as $module_permission)
                     @php
                         if (empty($role_permissions) && $module_permission['default']) {
                             $module_role_permissions[] = $module_permission['value'];
                         }
                     @endphp
                     @if (isset($module_permission['end_group']) && $module_permission['end_group'])
                         <div class="col-md-12">
                         @else
                             <div class="col-md-6">
                     @endif

                     <div class="checkbox">
                         <label>
                             @if (!empty($module_permission['is_radio']))
                                 {!! Form::radio(
                                     'radio_option[' . $module_permission['radio_input_name'] . ']',
                                     $module_permission['value'],
                                     in_array($module_permission['value'], $module_role_permissions),
                                     ['class' => 'input-icheck'],
                                 ) !!} {{ $module_permission['label'] }}
                             @else
                                 {!! Form::checkbox(
                                     'permissions[]',
                                     $module_permission['value'],
                                     in_array($module_permission['value'], $module_role_permissions),
                                     ['class' => 'input-icheck'],
                                 ) !!} {{ $module_permission['label'] }}
                             @endif
                         </label>
                     </div>

                     @if (isset($module_permission['end_group']) && $module_permission['end_group'])
                         <hr>
                     @endif
             </div>
     @endforeach
     </div>
     </div>
 @endforeach
 @endif
