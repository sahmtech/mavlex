@if (!$account_exist)
    <table class="table table-bordered table-striped">
        <tr>
            <td colspan="10" class="text-center">
                <h3>@lang('accounting::lang.no_accounts')</h3>
                <p>@lang('accounting::lang.add_default_accounts_help')</p>
                <a href="{{ route('accounting.create-default-accounts') }}"
                    class="btn btn-success btn-xs">@lang('accounting::lang.add_default_accounts') <i class="fas fa-file-import"></i></a>
            </td>
        </tr>
    </table>
@else
    <div class="row">
        <div class="col-md-4 mb-12 col-md-offset-4">
            <div class="input-group">
                <input type="input" class="form-control" id="accounts_tree_search">
                <span class="input-group-addon">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>

        <div class="col-md-4">
            <button class="btn btn-primary btn-sm" id="expand_all">@lang('accounting::lang.expand_all')</button>
            <button class="btn btn-primary btn-sm" id="collapse_all">@lang('accounting::lang.collapse_all')</button>
        </div>
        
        <div class="col-md-12" id="accounts_tree_container">
            <ul>
                @foreach ($account_types as $key => $value)
                    <li @if ($loop->index == 0) data-jstree='{ "opened" : true }' @endif>
                        ({{ $account_GLC[$key] }})
                        - {{ $value }}
                        <ul>
                            @foreach ($account_sub_types->where('account_primary_type', $key)->all() as $sub_type)
                                <li @if ($loop->index == 0) data-jstree='{ "opened" : true }' @endif>
                                    ({{ $account_GLC[$key] . $loop->index + 1 }})
                                     -  @if (app()->getLocale() == 'ar')
                    
                                    @lang('accounting::lang.' . $sub_type->name)
                                @else
                                    @lang('accounting::lang.' . $sub_type->name) 
                                @endif
                                    {{-- - @format_currency($sub_type->balance) --}}
                                    {{-- @if ($sub_type->status == 'active')
                                        <span><i class="fas fa-check text-success"
                                                title="@lang('accounting::lang.active')"></i></span>
                                    @elseif($sub_type->status == 'inactive')
                                        <span><i class="fas fa-times text-danger" title="@lang('lang_v1.inactive')"
                                                style="font-size: 14px;"></i></span>
                                    @endif --}}
                                    {{-- <span class="tree-actions">
                                        @if (
                                            (auth()->user()->can('Admin#'.request()->session()->get('user.business_id'))||auth()->user()->can('superadmin') ||
                                                auth()->user()->can('accounting.view_ledger')
                                            ))
                                            <a class="btn-modal btn-xs btn-default text-success ledger-link"
                                                style="margin: 2px;" title="@lang('accounting::lang.ledger')"
                                                href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@ledger', $sub_type->id) }}">
                                                <i class="fas fa-file-alt"></i></a>
                                        @endif

                                        @if (
                                            (auth()->user()->can('Admin#'.request()->session()->get('user.business_id'))||auth()->user()->can('superadmin') ||
                                                auth()->user()->can('accounting.edit_accounts')
                                            ))
                                            <a class="btn-modal btn-xs btn-default text-primary"
                                                style="margin: 2px;" title="@lang('messages.edit')"
                                                href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@edit', $sub_type->id) }}"
                                                data-href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@edit', $sub_type->id) }}"
                                                data-container="#create_account_modal">
                                                <i class="fas fa-edit"></i>

                                            </a>
                                        @endif


                                        @if (
                                            (auth()->user()->can('Admin#'.request()->session()->get('user.business_id'))||auth()->user()->can('superadmin') ||
                                                auth()->user()->can('accounting.add_extra_accounts')
                                            ))
                                            <a class="btn-modal btn-xs btn-default text-primary"
                                                style="margin: 2px;" title="@lang('accounting::lang.add_account')"
                                                href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@open_create_dialog', $sub_type->id) }}"
                                                data-href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@open_create_dialog', $sub_type->id) }}"
                                                data-container="#create_account_modal">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        @endif
                                        @if (
                                            (auth()->user()->can('Admin#'.request()->session()->get('user.business_id')) ||
                                                auth()->user()->can('superadmin') ||
                                                auth()->user()->can('accounting.active_accounts')
                                            ))
                                            <a class="activate-deactivate-btn text-warning  btn-xs btn-default"
                                                style="margin: 2px;"
                                                title="@if ($sub_type->status == 'active') @lang('messages.deactivate') @else 
                                                  @lang('messages.activate') @endif"
                                                href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@activateDeactivate', $sub_type->id) }}">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                        @endif

                                    </span> --}}

                                    <ul>
                                        @foreach ($accounts->where('account_sub_type_id', $sub_type->id)->sortBy('name')->all() as $account)
                                            <li
                                                @if (count($account->child_accounts) == 0) data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right" }' @endif>
                                                @if (app()->getLocale() == 'ar')
                                                    @if (!empty($account->gl_code))
                                                        ({{ $account->gl_code }})
                                                        -
                                                    @endif
                                                    @lang('accounting::lang.accounts_name.' . $account->name)
                                                @else
                                                    @lang('accounting::lang.accounts_name.' . $account->name) @if (!empty($account->gl_code))
                                                        - ({{ $account->gl_code }})
                                                    @endif
                                                @endif


                                                - @format_currency($account->balance)
                                                @if ($account->status == 'active')
                                                    <span><i class="fas fa-check text-success"
                                                            title="@lang('accounting::lang.active')"></i></span>
                                                @elseif($account->status == 'inactive')
                                                    <span><i class="fas fa-times text-danger" title="@lang('lang_v1.inactive')"
                                                            style="font-size: 14px;"></i></span>
                                                @endif
                                                <span class="tree-actions">
                                                    @if (
                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id'))||auth()->user()->can('superadmin') ||
                                                            auth()->user()->can('accounting.view_ledger')
                                                        ))
                                                        <a class="btn-modal btn-xs btn-default text-success ledger-link"
                                                            style="margin: 2px;" title="@lang('accounting::lang.ledger')"
                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@ledger', $account->id) }}">
                                                            <i class="fas fa-file-alt"></i></a>
                                                    @endif

                                                    @if (
                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id'))||auth()->user()->can('superadmin') ||
                                                            auth()->user()->can('accounting.edit_accounts')
                                                        ))
                                                        <a class="btn-modal btn-xs btn-default text-primary"
                                                            style="margin: 2px;" title="@lang('messages.edit')"
                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@edit', $account->id) }}"
                                                            data-href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@edit', $account->id) }}"
                                                            data-container="#create_account_modal">
                                                            <i class="fas fa-edit"></i>

                                                        </a>
                                                    @endif


                                                    @if (
                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id'))||auth()->user()->can('superadmin') ||
                                                            auth()->user()->can('accounting.add_extra_accounts')
                                                        ))
                                                        <a class="btn-modal btn-xs btn-default text-primary"
                                                            style="margin: 2px;" title="@lang('accounting::lang.add_account')"
                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@open_create_dialog', $account->id) }}"
                                                            data-href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@open_create_dialog', $account->id) }}"
                                                            data-container="#create_account_modal">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    @endif
                                                    @if (
                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id')) ||
                                                            auth()->user()->can('superadmin') ||
                                                            auth()->user()->can('accounting.active_accounts')
                                                        ))
                                                        <a class="activate-deactivate-btn text-warning  btn-xs btn-default"
                                                            style="margin: 2px;"
                                                            title="@if ($account->status == 'active') @lang('messages.deactivate') @else 
                                                              @lang('messages.activate') @endif"
                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@activateDeactivate', $account->id) }}">
                                                            <i class="fas fa-power-off"></i>
                                                        </a>
                                                    @endif

                                                </span>
                                                @if (count($account->child_accounts) > 0)
                                                    <ul>
                                                        @foreach ($account->child_accounts as $child_account)
                                                            <li
                                                                @if (count($child_account->child_accounts) == 0) data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right"}' @endif>
                                                                {{ $child_account->name }}

                                                                @if (!empty($child_account->gl_code))
                                                                    - ({{ $child_account->gl_code }})
                                                                @endif
                                                                - @format_currency($child_account->balance)

                                                                @if ($child_account->status == 'active')
                                                                    <span><i class="fas fa-check text-success"
                                                                            title="@lang('accounting::lang.active')"></i></span>
                                                                @elseif($child_account->status == 'inactive')
                                                                    <span><i class="fas fa-times text-danger"
                                                                            title="@lang('lang_v1.inactive')"
                                                                            style="font-size: 14px;"></i></span>
                                                                @endif
                                                                <span class="tree-actions">
                                                                    @if (
                                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id')) ||
                                                                            auth()->user()->can('superadmin') ||
                                                                            auth()->user()->can('accounting.view_ledger')
                                                                        ))
                                                                        <a class="btn-modal btn-xs btn-default text-success ledger-link"
                                                                            style="margin: 2px;"
                                                                            title="@lang('accounting::lang.ledger')"
                                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@ledger', $child_account->id) }}">
                                                                            <i class="fas fa-file-alt"></i></a>
                                                                    @endif
                                                                    @if (
                                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id')) ||
                                                                            auth()->user()->can('superadmin') ||
                                                                            auth()->user()->can('accounting.edit_accounts')
                                                                        ))
                                                                        <a class="btn-modal btn-xs btn-default text-primary"
                                                                            style="margin: 2px;"
                                                                            title="@lang('messages.edit')"
                                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@edit', $child_account->id) }}"
                                                                            data-href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@edit', $child_account->id) }}"
                                                                            data-container="#create_account_modal">
                                                                            <i class="fas fa-edit"></i></a>
                                                                    @endif
                                                                    @if (
                                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id')) ||
                                                                            auth()->user()->can('superadmin') ||
                                                                            auth()->user()->can('accounting.add_extra_accounts')
                                                                        ))
                                                                        <a class="btn-modal btn-xs btn-default text-primary"
                                                                            style="margin: 2px;"
                                                                            title="@lang('accounting::lang.add_account')"
                                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@open_create_dialog', $child_account->id) }}"
                                                                            data-href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@open_create_dialog', $child_account->id) }}"
                                                                            data-container="#create_account_modal">
                                                                            <i class="fas fa-plus"></i>
                                                                        </a>
                                                                    @endif
                                                                    @if (
                                                                        (auth()->user()->can('Admin#'.request()->session()->get('user.business_id')) ||
                                                                            auth()->user()->can('superadmin') ||
                                                                            auth()->user()->can('accounting.active_accounts')
                                                                        ))
                                                                        <a class="activate-deactivate-btn text-warning  btn-xs btn-default"
                                                                            style="margin: 2px;"
                                                                            title="@if ($child_account->status == 'active') @lang('messages.deactivate') @else 
                                                                       @lang('messages.activate') @endif"
                                                                            href="{{ action('\Modules\Accounting\Http\Controllers\CoaController@activateDeactivate', $child_account->id) }}">
                                                                            <i class="fas fa-power-off"></i>
                                                                        </a>
                                                                    @endif
                                                                </span>
                                                                @if (count($child_account->child_accounts) > 0)
                                                                    @include(
                                                                        'accounting::chart_of_accounts.chiled_tree',
                                                                        ['account' => $child_account]
                                                                    )
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
