@extends('layouts.app')
@section('title', __('restaurant.tables_and_floors'))

@section('css')
<style>
    .rt-page {
        font-family: "Cairo", "Tajawal", "IBM Plex Sans Arabic", sans-serif;
    }
    .rt-page .content-header {
        padding-bottom: 8px;
    }
    .rt-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }
    .rt-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6366F1;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }
    .rt-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.25;
        font-weight: 800;
        color: #0F172A;
    }
    .rt-subtitle {
        margin: 6px 0 0;
        color: #64748B;
        font-size: 14px;
        font-weight: 500;
    }
    .rt-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #E2E8F0;
        overflow: hidden;
    }
    .rt-tabs {
        display: flex;
        gap: 6px;
        padding: 14px 16px 0;
        background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%);
        border-bottom: 1px solid #E2E8F0;
        list-style: none;
        margin: 0;
    }
    .rt-tabs > li {
        margin: 0;
    }
    .rt-tabs > li > a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        color: #64748B !important;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none !important;
        border: 1px solid transparent;
        background: transparent;
        transition: all .2s ease;
    }
    .rt-tabs > li > a:hover {
        background: #EEF2FF;
        color: #4F46E5 !important;
    }
    .rt-tabs > li.active > a,
    .rt-tabs > li.active > a:hover,
    .rt-tabs > li.active > a:focus {
        background: #EEF2FF;
        color: #4F46E5 !important;
        border-color: #C7D2FE;
        box-shadow: 0 1px 0 #4F46E5 inset;
    }
    .rt-pane {
        padding: 20px;
    }
    .rt-toolbar-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .rt-section-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
    }
    .rt-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #4F46E5;
        color: #fff !important;
        border: none;
        border-radius: 999px;
        padding: 10px 18px;
        font-weight: 700;
        box-shadow: 0 8px 16px rgba(79, 70, 229, .18);
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .rt-add-btn:hover,
    .rt-add-btn:focus {
        background: #4338CA;
        color: #fff !important;
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(79, 70, 229, .24);
    }
    .rt-table-wrap {
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .rt-page .dataTables_wrapper {
        padding: 12px;
    }
    .rt-page .dataTables_wrapper .row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin: 0 0 12px;
    }
    .rt-page .dataTables_filter input {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 8px 36px 8px 12px;
        min-width: 220px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394A3B8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E") no-repeat right 12px center;
        box-shadow: none;
        transition: box-shadow .15s ease, border-color .15s ease;
    }
    .rt-page .dataTables_filter input:focus {
        outline: none;
        border-color: #6366F1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .25);
    }
    .rt-page .dataTables_length select {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 6px 10px;
        background: #fff;
    }
    .rt-page .dt-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
    }
    .rt-page .dt-buttons .tw-dw-btn,
    .rt-page .dt-buttons a,
    .rt-page .dt-buttons button {
        border: 1px solid #CBD5E1 !important;
        background: #fff !important;
        color: #334155 !important;
        border-radius: 999px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        box-shadow: none !important;
    }
    .rt-page .dt-buttons .tw-dw-btn:hover,
    .rt-page .dt-buttons a:hover,
    .rt-page .dt-buttons button:hover {
        background: #F8FAFC !important;
        border-color: #94A3B8 !important;
    }
    .rt-page table.dataTable {
        border: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .rt-page table.dataTable thead th {
        background: #F1F5F9 !important;
        color: #475569;
        font-weight: 700;
        font-size: 12px;
        text-transform: none;
        border: 0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 14px 18px !important;
        white-space: nowrap;
    }
    .rt-page table.dataTable tbody td {
        padding: 16px 18px !important;
        border: 0 !important;
        border-bottom: 1px solid #F1F5F9 !important;
        color: #0F172A;
        vertical-align: middle;
        background: #fff !important;
    }
    .rt-page table.dataTable tbody tr:hover td {
        background: #F8FAFC !important;
    }
    .rt-page table.dataTable.no-footer {
        border-bottom: 0 !important;
    }
    .rt-page .dataTables_info,
    .rt-page .dataTables_paginate {
        padding-top: 12px;
        color: #64748B;
    }
    .rt-page .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        border: 1px solid #E2E8F0 !important;
        background: #fff !important;
        color: #334155 !important;
        margin: 0 3px;
        padding: 4px 10px !important;
    }
    .rt-page .dataTables_paginate .paginate_button.current {
        background: #EEF2FF !important;
        color: #4F46E5 !important;
        border-color: #C7D2FE !important;
    }
    .rt-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .rt-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .rt-badge:hover {
        transform: translateY(-1px);
    }
    .rt-badge-edit {
        background: #EDE9FE;
        color: #6D28D9;
    }
    .rt-badge-delete {
        background: #FEE2E2;
        color: #B91C1C;
    }
    .rt-badge-qr {
        background: #CCFBF1;
        color: #0F766E;
    }
    .rt-seat-pill {
        display: inline-flex;
        min-width: 28px;
        justify-content: center;
        background: #F1F5F9;
        color: #334155;
        border-radius: 999px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 12px;
    }
    .rt-status-select {
        min-width: 150px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 6px 8px;
        font-weight: 700;
        font-size: 12px;
        background: #fff;
        color: #0F172A;
    }
    .rt-status-select:disabled {
        opacity: .7;
        cursor: not-allowed;
        background: #F8FAFC;
    }
    .rt-guest-line {
        margin-top: 6px;
        font-size: 11px;
        font-weight: 700;
        color: #92400E;
    }
    body.rt-modal-open .modal-backdrop {
        background: rgba(0, 0, 0, .4);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .rt-modal {
        max-width: 36rem;
        width: calc(100% - 24px);
        margin: 8vh auto;
    }
    .rt-modal .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, .28);
        background: #fff;
    }
    .rt-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 20px 12px;
    }
    .rt-modal-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .rt-modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }
    .rt-modal-icon-indigo { background: #EEF2FF; color: #4F46E5; }
    .rt-modal-icon-violet { background: #EDE9FE; color: #6D28D9; }
    .rt-modal-icon-teal { background: #CCFBF1; color: #0F766E; }
    .rt-modal-icon-danger { background: #FEE2E2; color: #B91C1C; }
    .rt-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.4;
    }
    .rt-modal-help,
    .rt-modal-meta {
        margin: 2px 0 0;
        color: #64748B;
        font-size: 13px;
        font-weight: 500;
    }
    .rt-modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .15s ease;
    }
    .rt-modal-close:hover {
        background: #F1F5F9;
        color: #0F172A;
    }
    .rt-modal-body {
        padding: 8px 20px 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .rt-modal-body-center {
        align-items: center;
        text-align: center;
    }
    .rt-field {
        margin: 0;
        width: 100%;
    }
    .rt-field .select2-container,
    .rt-modal .select2,
    .rt-modal .select2-container {
        width: 100% !important;
        display: block !important;
    }
    .rt-label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        line-height: 1.5;
    }
    .rt-input,
    .rt-modal .form-control {
        border: 1px solid #CBD5E1 !important;
        border-radius: 8px !important;
        min-height: 42px;
        box-shadow: none !important;
        color: #0F172A;
    }
    .rt-input::placeholder,
    .rt-modal .form-control::placeholder {
        color: #94A3B8;
    }
    .rt-input:focus,
    .rt-modal .form-control:focus {
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .25) !important;
    }
    .rt-modal .select2-container .select2-selection--single {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        min-height: 42px;
        height: 42px;
        width: 100% !important;
        padding-top: 6px;
    }
    .rt-modal .select2-container .select2-selection--single .select2-selection__rendered {
        width: 100%;
        line-height: 28px;
        padding-right: 8px;
        padding-left: 28px;
    }
    .rt-modal select.rt-input,
    .rt-modal select.form-control {
        width: 100% !important;
        display: block;
    }
    .rt-modal .select2-container--default.select2-container--focus .select2-selection--single,
    .rt-modal .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #6366F1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .25);
    }
    .rt-qr-frame {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,.04);
    }
    .rt-modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px 20px;
        border-top: 1px solid #F1F5F9;
        background: #FAFBFC;
    }
    .rt-btn-primary,
    a.rt-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #4F46E5;
        color: #fff !important;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 700;
        text-decoration: none;
        transition: background .15s ease, box-shadow .15s ease;
    }
    .rt-btn-primary:hover,
    a.rt-btn-primary:hover {
        background: #4338CA;
        color: #fff !important;
        box-shadow: 0 8px 16px rgba(79, 70, 229, .2);
    }
    .rt-btn-primary.is-loading {
        opacity: .7;
        pointer-events: none;
    }
    .rt-btn-ghost {
        background: transparent;
        color: #475569;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 700;
    }
    .rt-btn-ghost:hover {
        background: #F1F5F9;
        color: #0F172A;
    }
    .rt-btn-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #DC2626;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 700;
    }
    .rt-btn-danger:hover {
        background: #B91C1C;
        color: #fff;
    }
    .rt-confirm-copy {
        margin: 0;
        color: #475569;
        line-height: 1.7;
    }
    .rt-floor-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .rt-legend {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 12px;
        font-weight: 700;
        color: #64748B;
    }
    .rt-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-inline-end: 6px;
    }
    .rt-dot-0 { background: #22C55E; }
    .rt-dot-1 { background: #F59E0B; }
    .rt-dot-2 { background: #EF4444; }
    .rt-floor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }
    .rt-table-card {
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 14px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .04);
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
        position: relative;
        overflow: hidden;
    }
    .rt-table-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    }
    .rt-table-card.status-0 { border-top: 4px solid #22C55E; }
    .rt-table-card.status-1 { border-top: 4px solid #F59E0B; }
    .rt-table-card.status-2 { border-top: 4px solid #EF4444; }
    .rt-table-card-name {
        font-size: 16px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 4px;
    }
    .rt-table-meta {
        color: #64748B;
        font-size: 12px;
        margin-bottom: 8px;
    }
    .rt-status-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 800;
    }
    .rt-status-pill.s0 { background: #DCFCE7; color: #166534; }
    .rt-status-pill.s1 { background: #FEF3C7; color: #92400E; }
    .rt-status-pill.s2 { background: #FEE2E2; color: #991B1B; }
    .rt-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }
    .rt-mini-btn {
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 700;
        color: #334155;
    }
    .rt-mini-btn.danger { color: #B91C1C; border-color: #FECACA; background: #FEF2F2; }
    .rt-order-item-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
    }
    .rt-order-item-row input {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 6px 8px;
    }
    #rt_product_results {
        max-height: 180px;
        overflow: auto;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        margin-top: 8px;
        display: none;
    }
    #rt_product_results button {
        display: block;
        width: 100%;
        text-align: right;
        background: #fff;
        border: 0;
        border-bottom: 1px solid #F1F5F9;
        padding: 8px 10px;
    }
    #rt_product_results button:hover { background: #EEF2FF; }
</style>
@endsection

@section('content')
<section class="content-header rt-page">
    <div class="rt-hero">
        <div>
            <div class="rt-kicker">
                <i class="fa fa-cutlery"></i>
                @lang('restaurant.restaurant')
            </div>
            <h1 class="rt-title">@lang('restaurant.tables_and_floors')</h1>
            <p class="rt-subtitle">@lang('restaurant.manage_your_tables')</p>
        </div>
    </div>
</section>

<section class="content rt-page">
    <div class="rt-card">
        <ul class="nav nav-tabs rt-tabs" role="tablist">
            <li class="active">
                <a href="#tables_tab" data-toggle="tab">
                    <i class="fa fa-th-large"></i> @lang('restaurant.tables')
                </a>
            </li>
            <li>
                <a href="#floors_tab" data-toggle="tab">
                    <i class="fa fa-building"></i> @lang('restaurant.floors')
                </a>
            </li>
            <li>
                <a href="#floor_plan_tab" data-toggle="tab">
                    <i class="fa fa-th"></i> @lang('restaurant.floor_plan')
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active rt-pane" id="tables_tab">
                <div class="rt-toolbar-row">
                    <h3 class="rt-section-title">@lang('restaurant.all_your_tables')</h3>
                    <button type="button"
                        class="rt-add-btn btn-modal"
                        data-href="{{ action([\App\Http\Controllers\Restaurant\TableController::class, 'create']) }}"
                        data-container=".tables_modal">
                        <i class="fa fa-plus"></i> @lang('restaurant.add_table')
                    </button>
                </div>
                <div class="rt-table-wrap">
                    <table class="table" id="tables_table">
                        <thead>
                            <tr>
                                <th>@lang('restaurant.table')</th>
                                <th>@lang('purchase.business_location')</th>
                                <th>@lang('restaurant.floor')</th>
                                <th>@lang('restaurant.seats')</th>
                                <th>@lang('restaurant.table_status')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="tab-pane rt-pane" id="floors_tab">
                <div class="rt-toolbar-row">
                    <h3 class="rt-section-title">@lang('restaurant.all_your_floors')</h3>
                    <button type="button"
                        class="rt-add-btn btn-modal"
                        data-href="{{ action([\App\Http\Controllers\Restaurant\FloorController::class, 'create']) }}"
                        data-container=".floors_modal">
                        <i class="fa fa-plus"></i> @lang('restaurant.add_floor')
                    </button>
                </div>
                <div class="rt-table-wrap">
                    <table class="table" id="floors_table">
                        <thead>
                            <tr>
                                <th>@lang('restaurant.floor_name')</th>
                                <th>@lang('purchase.business_location')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="tab-pane rt-pane" id="floor_plan_tab">
                <div class="rt-floor-toolbar">
                    <div>
                        <h3 class="rt-section-title">@lang('restaurant.floor_plan')</h3>
                        <div class="rt-legend" style="margin-top:8px;">
                            <span><i class="rt-dot rt-dot-0"></i>@lang('restaurant.available')</span>
                            <span><i class="rt-dot rt-dot-1"></i>@lang('restaurant.reserved')</span>
                            <span><i class="rt-dot rt-dot-2"></i>@lang('restaurant.occupied')</span>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <select id="rt_location_filter" class="form-control" style="min-width:180px;">
                            <option value="">@lang('purchase.business_location')</option>
                            @foreach($business_locations as $locId => $locName)
                                <option value="{{ $locId }}">{{ $locName }}</option>
                            @endforeach
                        </select>
                        <select id="rt_floor_filter" class="form-control" style="min-width:160px;">
                            <option value="">@lang('restaurant.floors')</option>
                        </select>
                        <select id="rt_waiter_filter" class="form-control" style="min-width:180px;">
                            <option value="">@lang('restaurant.all_waiters')</option>
                        </select>
                        <button type="button" class="rt-add-btn" id="rt_floor_refresh"><i class="fa fa-refresh"></i> @lang('restaurant.refresh')</button>
                    </div>
                </div>
                <div id="rt_floor_plan"></div>
            </div>
        </div>
    </div>

    <div class="modal fade tables_modal" tabindex="-1" role="dialog"></div>
    <div class="modal fade floors_modal" tabindex="-1" role="dialog"></div>
    <div class="modal fade rt-confirm-modal" id="rt_order_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog rt-modal" role="document">
            <div class="modal-content">
                <div class="rt-modal-head">
                    <div class="rt-modal-title-wrap">
                        <span class="rt-modal-icon"><i class="fa fa-cutlery"></i></span>
                        <div>
                            <h4 class="rt-modal-title" id="rt_order_title">@lang('restaurant.open_order')</h4>
                            <p class="rt-modal-help" id="rt_order_table_name"></p>
                        </div>
                    </div>
                    <button type="button" class="rt-modal-close" data-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>
                <div class="rt-modal-body">
                    <input type="hidden" id="rt_order_table_id">
                    <label>@lang('restaurant.search_product')</label>
                    <input type="text" class="form-control rt-input" id="rt_product_search" placeholder="@lang('restaurant.search_product')">
                    <div id="rt_product_results"></div>
                    <div id="rt_order_items" style="margin-top:12px;"></div>
                    <label style="margin-top:12px;">@lang('restaurant.order_notes')</label>
                    <textarea id="rt_order_notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="rt-modal-foot">
                    <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
                    <button type="button" class="rt-btn-primary" id="rt_send_order">@lang('restaurant.send_to_kitchen')</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade rt-confirm-modal" id="rt_view_order_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog rt-modal" role="document">
            <div class="modal-content">
                <div class="rt-modal-head">
                    <div class="rt-modal-title-wrap">
                        <span class="rt-modal-icon"><i class="fa fa-list"></i></span>
                        <div>
                            <h4 class="rt-modal-title">@lang('restaurant.order_details')</h4>
                            <p class="rt-modal-help" id="rt_view_order_meta"></p>
                        </div>
                    </div>
                    <button type="button" class="rt-modal-close" data-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>
                <div class="rt-modal-body" id="rt_view_order_body"></div>
                <div class="rt-modal-foot">
                    <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade rt-confirm-modal" id="rt_reserve_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog rt-modal" role="document">
            <div class="modal-content">
                <div class="rt-modal-head">
                    <div class="rt-modal-title-wrap">
                        <span class="rt-modal-icon"><i class="fa fa-user"></i></span>
                        <div>
                            <h4 class="rt-modal-title">@lang('restaurant.reserve_table')</h4>
                            <p class="rt-modal-help" id="rt_reserve_table_name"></p>
                        </div>
                    </div>
                    <button type="button" class="rt-modal-close" data-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>
                <div class="rt-modal-body">
                    <input type="hidden" id="rt_reserve_table_id">
                    <div class="rt-field">
                        <label class="rt-label">@lang('restaurant.reservation_guest_name') *</label>
                        <input type="text" class="form-control rt-input" id="rt_reserve_guest_name">
                    </div>
                    <div class="rt-field">
                        <label class="rt-label">@lang('restaurant.reservation_guest_phone')</label>
                        <input type="text" class="form-control rt-input" id="rt_reserve_guest_phone">
                    </div>
                    <div class="rt-field">
                        <label class="rt-label">@lang('restaurant.reservation_note')</label>
                        <input type="text" class="form-control rt-input" id="rt_reserve_note">
                    </div>
                </div>
                <div class="rt-modal-foot">
                    <button type="button" class="rt-btn-ghost" id="rt_reserve_cancel">@lang('restaurant.cancel')</button>
                    <button type="button" class="rt-btn-primary" id="rt_reserve_save">@lang('messages.save')</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade rt-confirm-modal" id="rt_confirm_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog rt-modal" role="document">
            <div class="modal-content">
                <div class="rt-modal-head">
                    <div class="rt-modal-title-wrap">
                        <span class="rt-modal-icon rt-modal-icon-danger"><i class="fa fa-exclamation-triangle"></i></span>
                        <div>
                            <h4 class="rt-modal-title" id="rt_confirm_title">@lang('restaurant.confirm_delete')</h4>
                            <p class="rt-modal-help" id="rt_confirm_text"></p>
                        </div>
                    </div>
                    <button type="button" class="rt-modal-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                </div>
                <div class="rt-modal-body">
                    <p class="rt-confirm-copy" id="rt_confirm_copy"></p>
                </div>
                <div class="rt-modal-foot">
                    <button type="button" class="rt-btn-ghost" data-dismiss="modal">@lang('restaurant.cancel')</button>
                    <button type="button" class="rt-btn-danger" id="rt_confirm_ok">@lang('restaurant.confirm_delete')</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    function loadFloorsForLocation(locationId, $select, selectedId) {
        $select.empty().append($('<option>', { value: '', text: "{{ __('messages.please_select') }}" }));
        if (!locationId) {
            return;
        }
        $.ajax({
            url: "{{ url('modules/floors/for-location') }}/" + locationId,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    return;
                }
                if (!res.data.length) {
                    $select.append($('<option>', { value: '', text: "{{ __('restaurant.no_floors_for_location') }}", disabled: true }));
                    return;
                }
                $.each(res.data, function (i, floor) {
                    $select.append($('<option>', {
                        value: floor.id,
                        text: floor.name,
                        selected: String(selectedId) === String(floor.id)
                    }));
                });
            }
        });
    }

    $(document).ready(function () {
        var tables_table = $('#tables_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: false,
            ajax: "{{ action([\App\Http\Controllers\Restaurant\TableController::class, 'index']) }}",
            columnDefs: [{
                targets: [4, 5],
                orderable: false,
                searchable: false
            }],
            columns: [
                { data: 'name', name: 'res_tables.name' },
                { data: 'location', name: 'BL.name' },
                { data: 'floor_name', name: 'RF.name' },
                { data: 'seats', name: 'res_tables.seats' },
                { data: 'status', name: 'res_tables.status' },
                { data: 'action', name: 'action' }
            ]
        });

        $(document).on('change', '.rt-table-status, .rt-status-select', function () {
            var $el = $(this);
            var prev = $el.data('prev');
            var id = $el.data('id');
            var status = $el.val();
            if (String(status) === '1') {
                $el.val(prev);
                openReserveModal(id, $el, prev, $el.data('guest-name'), $el.data('guest-phone'));
                return;
            }
            postTableStatus(id, status, {}, $el, prev);
        });

        var pendingReserve = null;

        function openReserveModal(id, $el, prev, name, phone) {
            pendingReserve = { id: id, $el: $el, prev: prev };
            $('#rt_reserve_table_id').val(id);
            $('#rt_reserve_guest_name').val(name || '');
            $('#rt_reserve_guest_phone').val(phone || '');
            $('#rt_reserve_note').val('');
            $('#rt_reserve_modal').modal('show');
        }

        function postTableStatus(id, status, extra, $el, prev) {
            var payload = $.extend({
                _token: '{{ csrf_token() }}',
                status: status
            }, extra || {});
            if ($el) {
                $el.prop('disabled', true);
            }
            $.post("{{ url('modules/table-orders/change-status') }}/" + id, payload, function (res) {
                if ($el) {
                    $el.prop('disabled', false);
                }
                if (res.success) {
                    if ($el) {
                        $el.val(status);
                        $el.data('prev', status);
                        if (extra && extra.reserved_guest_name) {
                            $el.data('guest-name', extra.reserved_guest_name);
                            $el.data('guest-phone', extra.reserved_guest_phone || '');
                        }
                    }
                    toastr.success(res.msg);
                    tables_table.ajax.reload(null, false);
                    if (typeof loadFloorPlan === 'function' && $('#floor_plan_tab').hasClass('active')) {
                        loadFloorPlan();
                    }
                } else {
                    if ($el) {
                        $el.val(prev);
                    }
                    toastr.error(res.msg);
                }
            }).fail(function (xhr) {
                if ($el) {
                    $el.prop('disabled', false);
                    $el.val(prev);
                }
                toastr.error((xhr.responseJSON && xhr.responseJSON.msg) || "{{ __('messages.something_went_wrong') }}");
            });
        }

        $('#rt_reserve_save').on('click', function () {
            var name = $.trim($('#rt_reserve_guest_name').val());
            if (!name) {
                toastr.error("{{ __('restaurant.reservation_guest_required') }}");
                return;
            }
            if (!pendingReserve) {
                return;
            }
            postTableStatus(pendingReserve.id, 1, {
                reserved_guest_name: name,
                reserved_guest_phone: $('#rt_reserve_guest_phone').val(),
                reserved_note: $('#rt_reserve_note').val()
            }, pendingReserve.$el, pendingReserve.prev);
            $('#rt_reserve_modal').modal('hide');
            pendingReserve = null;
        });

        $('#rt_reserve_cancel').on('click', function () {
            if (pendingReserve && pendingReserve.$el) {
                pendingReserve.$el.val(pendingReserve.prev);
            }
            pendingReserve = null;
            $('#rt_reserve_modal').modal('hide');
        });
        $('#rt_reserve_modal').on('hidden.bs.modal', function () {
            if (pendingReserve && pendingReserve.$el) {
                pendingReserve.$el.val(pendingReserve.prev);
            }
        });

        $(document).on('change', '#status', function () {
            var $form = $(this).closest('form');
            $form.find('.rt-reservation-fields').toggle(String($(this).val()) === '1');
        });

        var floors_table = $('#floors_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: false,
            ajax: "{{ action([\App\Http\Controllers\Restaurant\FloorController::class, 'index']) }}",
            columnDefs: [{
                targets: 2,
                orderable: false,
                searchable: false
            }],
            columns: [
                { data: 'name', name: 'res_floors.name' },
                { data: 'location', name: 'BL.name' },
                { data: 'action', name: 'action' }
            ]
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') === '#floors_tab') {
                floors_table.columns.adjust().draw(false);
            } else if ($(e.target).attr('href') === '#floor_plan_tab') {
                loadFloorPlan();
            } else {
                tables_table.columns.adjust().draw(false);
            }
        });

        function initRtModalSelect2($modal) {
            $modal.find('select.select2').each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.css('width', '100%');
                $el.select2({
                    dropdownParent: $modal,
                    width: '100%',
                    dir: 'rtl'
                });
            });
        }

        $('div.tables_modal, div.floors_modal').on('shown.bs.modal', function () {
            var $modal = $(this);
            initRtModalSelect2($modal);
            var $form = $modal.find('form#table_add_form, form#table_edit_form');
            if (!$form.length) {
                return;
            }
            var $location = $form.find('select#location_id');
            var $floor = $form.find('select#floor_id');
            if ($location.length && $floor.length) {
                loadFloorsForLocation($location.val(), $floor, $floor.data('selected'));
            }
        });

        $(document).on('change', 'select#location_id', function () {
            var $floor = $(this).closest('form').find('select#floor_id');
            if ($floor.length) {
                loadFloorsForLocation($(this).val(), $floor, null);
            }
        });

        $(document).on('submit', 'form#table_add_form', function (e) {
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.success == true) {
                        $('div.tables_modal').modal('hide');
                        toastr.success(result.msg);
                        tables_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('submit', 'form#floor_add_form', function (e) {
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.success == true) {
                        $('div.floors_modal').modal('hide');
                        toastr.success(result.msg);
                        floors_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('submit', 'form#table_edit_form', function (e) {
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.success == true) {
                        $('div.tables_modal').modal('hide');
                        toastr.success(result.msg);
                        tables_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('submit', 'form#floor_edit_form', function (e) {
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.success == true) {
                        $('div.floors_modal').modal('hide');
                        toastr.success(result.msg);
                        floors_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', 'button.edit_table_button', function () {
            $('div.tables_modal').load($(this).data('href'), function () {
                $(this).modal('show');
            });
        });

        $(document).on('click', 'button.edit_floor_button', function () {
            $('div.floors_modal').load($(this).data('href'), function () {
                $(this).modal('show');
            });
        });

        $(document).on('click', 'button.qr_table_button', function () {
            $('div.tables_modal').load($(this).data('href'), function () {
                $(this).modal('show');
            });
        });

        $('div.tables_modal, div.floors_modal, #rt_confirm_modal').on('show.bs.modal', function () {
            $('body').addClass('rt-modal-open');
        }).on('hidden.bs.modal', function () {
            if (!$('.modal:visible').length) {
                $('body').removeClass('rt-modal-open');
            }
        });

        $(document).on('submit', 'form#table_add_form, form#table_edit_form, form#floor_add_form, form#floor_edit_form', function () {
            $(this).find('.rt-btn-primary').addClass('is-loading');
        });

        var pendingDelete = { href: '', table: null };
        function openDeleteConfirm(href, table, title, copy) {
            pendingDelete.href = href;
            pendingDelete.table = table;
            $('#rt_confirm_title').text(title);
            $('#rt_confirm_text').text('');
            $('#rt_confirm_copy').text(copy);
            $('#rt_confirm_modal').modal('show');
        }

        $(document).on('click', 'button.delete_table_button', function () {
            openDeleteConfirm(
                $(this).data('href'),
                tables_table,
                "{{ __('restaurant.confirm_delete') }}",
                "{{ __('restaurant.confirm_delete_table_msg') }}"
            );
        });

        $(document).on('click', 'button.delete_floor_button', function () {
            openDeleteConfirm(
                $(this).data('href'),
                floors_table,
                "{{ __('restaurant.confirm_delete') }}",
                "{{ __('restaurant.confirm_delete_floor_msg') }}"
            );
        });

        var floorPoll = null;
        var orderItems = [];
        var labels = {
            0: "{{ __('restaurant.available') }}",
            1: "{{ __('restaurant.reserved') }}",
            2: "{{ __('restaurant.occupied') }}"
        };

        function loadFloorPlan() {
            $.get("{{ action([\App\Http\Controllers\Restaurant\TableOrderController::class, 'floorState']) }}", {
                waiter_id: $('#rt_waiter_filter').val(),
                location_id: $('#rt_location_filter').val(),
                floor_id: $('#rt_floor_filter').val()
            }, function (res) {
                if (!res.success) {
                    return;
                }
                var data = res.data;
                var current = $('#rt_waiter_filter').val();
                var $filter = $('#rt_waiter_filter').empty().append($('<option>', { value: '', text: "{{ __('restaurant.all_waiters') }}" }));
                $.each(data.waiters || [], function (i, w) {
                    $filter.append($('<option>', { value: w.id, text: w.name, selected: String(current) === String(w.id) }));
                });
                var html = '';
                if (!data.floors.length) {
                    html = '<p class="text-center">{{ __("restaurant.no_tables_found") }}</p>';
                }
                $.each(data.floors, function (i, floor) {
                    html += '<h4 class="rt-section-title" style="margin:16px 0 10px;">' + $('<div>').text(floor.name).html() + '</h4>';
                    html += '<div class="rt-floor-grid">';
                    $.each(floor.tables, function (j, table) {
                        var order = table.active_order;
                        var waiter = table.assigned_waiter ? table.assigned_waiter.name : '—';
                        var summary = order ? (order.invoice_no + ' · ' + (order.items_count || 0) + ' · ' + Number(order.final_total).toFixed(2)) : '';
                        html += '<div class="rt-table-card status-' + table.status + '" data-id="' + table.id + '">';
                        html += '<p class="rt-table-card-name">' + $('<div>').text(table.name).html() + ' <span class="rt-status-pill s' + table.status + '">' + (labels[table.status] || '') + '</span></p>';
                        html += '<div class="rt-table-meta">{{ __("restaurant.assigned_waiter") }}: ' + $('<div>').text(waiter).html();
                        if (table.reservation && table.reservation.guest_name) {
                            html += '<br>{{ __("restaurant.reservation_guest_name") }}: ' + $('<div>').text(table.reservation.guest_name).html();
                            if (table.reservation.guest_phone) {
                                html += ' · ' + $('<div>').text(table.reservation.guest_phone).html();
                            }
                        }
                        if (summary) {
                            html += '<br>{{ __("restaurant.active_order_summary") }}: ' + $('<div>').text(summary).html();
                        }
                        html += '</div><div class="rt-card-actions">';
                        html += '<button type="button" class="rt-mini-btn rt-open-order">{{ __("restaurant.open_order") }}</button>';
                        if (order) {
                            html += '<button type="button" class="rt-mini-btn rt-view-order">{{ __("restaurant.view_order") }}</button>';
                            html += '<button type="button" class="rt-mini-btn rt-serve-order" data-order="' + order.id + '">{{ __("restaurant.mark_as_served") }}</button>';
                            html += '<button type="button" class="rt-mini-btn danger rt-cancel-order" data-order="' + order.id + '">{{ __("restaurant.cancel_order") }}</button>';
                        } else {
                            html += '<button type="button" class="rt-mini-btn rt-status" data-status="0">{{ __("restaurant.available") }}</button>';
                            html += '<button type="button" class="rt-mini-btn rt-status" data-status="1">{{ __("restaurant.reserved") }}</button>';
                            html += '<button type="button" class="rt-mini-btn rt-status" data-status="2">{{ __("restaurant.occupied") }}</button>';
                        }
                        html += '</div></div>';
                    });
                    html += '</div>';
                });
                $('#rt_floor_plan').html(html);
            });
        }

        $('#rt_floor_refresh').on('click', loadFloorPlan);
        $('#rt_waiter_filter, #rt_location_filter, #rt_floor_filter').on('change', loadFloorPlan);
        $('#rt_location_filter').on('change', function () {
            loadFloorsForLocation($(this).val(), $('#rt_floor_filter'), '');
        });

        $(document).on('click', '.rt-open-order', function (e) {
            e.stopPropagation();
            var id = $(this).closest('.rt-table-card').data('id');
            $.get("{{ url('modules/table-orders') }}/" + id, function (res) {
                var table = res.data;
                $('#rt_order_table_id').val(table.id);
                $('#rt_order_table_name').text(table.name);
                $('#rt_order_notes').val(table.active_order ? (table.active_order.notes || '') : '');
                orderItems = [];
                if (table.active_order && table.active_order.items) {
                    $.each(table.active_order.items, function (i, item) {
                        orderItems.push({
                            variation_id: item.variation_id,
                            name: item.name,
                            quantity: item.quantity,
                            unit_price_inc_tax: item.unit_price_inc_tax
                        });
                    });
                }
                renderOrderItems();
                $('#rt_order_modal').modal('show');
            });
        });

        function renderOrderItems() {
            var html = '';
            $.each(orderItems, function (i, item) {
                html += '<div class="rt-order-item-row" data-i="' + i + '">';
                html += '<div style="flex:1;font-weight:700;">' + $('<div>').text(item.name).html() + '</div>';
                html += '<input type="number" min="1" step="1" value="' + item.quantity + '" class="rt-qty" style="width:70px;">';
                html += '<button type="button" class="rt-mini-btn danger rt-remove-item">&times;</button></div>';
            });
            $('#rt_order_items').html(html || '<p class="text-muted">{{ __("restaurant.order_items_required") }}</p>');
        }

        $(document).on('change', '.rt-qty', function () {
            var i = $(this).closest('.rt-order-item-row').data('i');
            orderItems[i].quantity = parseFloat($(this).val()) || 1;
        });
        $(document).on('click', '.rt-remove-item', function () {
            var i = $(this).closest('.rt-order-item-row').data('i');
            orderItems.splice(i, 1);
            renderOrderItems();
        });

        var searchTimer = null;
        $('#rt_product_search').on('keyup', function () {
            var term = $(this).val();
            clearTimeout(searchTimer);
            if (!term) {
                $('#rt_product_results').hide().empty();
                return;
            }
            searchTimer = setTimeout(function () {
                $.get("{{ action([\App\Http\Controllers\Restaurant\TableOrderController::class, 'products']) }}", { term: term }, function (res) {
                    var html = '';
                    $.each(res.data || [], function (i, p) {
                        html += '<button type="button" data-product=\'' + JSON.stringify(p).replace(/'/g, "&#39;") + '\'>' + $('<div>').text(p.name + ' - ' + p.unit_price_inc_tax).html() + '</button>';
                    });
                    $('#rt_product_results').html(html).toggle(!!html);
                });
            }, 250);
        });

        $(document).on('click', '#rt_product_results button', function () {
            var p = $(this).data('product');
            if (typeof p === 'string') {
                p = JSON.parse(p);
            }
            orderItems.push({
                variation_id: p.variation_id,
                name: p.name,
                quantity: 1,
                unit_price_inc_tax: p.unit_price_inc_tax,
                unit_price: p.unit_price
            });
            $('#rt_product_search').val('');
            $('#rt_product_results').hide().empty();
            renderOrderItems();
        });

        $('#rt_send_order').on('click', function () {
            if (!orderItems.length) {
                toastr.error("{{ __('restaurant.order_items_required') }}");
                return;
            }
            $.post("{{ action([\App\Http\Controllers\Restaurant\TableOrderController::class, 'newOrder']) }}", {
                _token: '{{ csrf_token() }}',
                table_id: $('#rt_order_table_id').val(),
                notes: $('#rt_order_notes').val(),
                items: orderItems
            }, function (res) {
                if (res.success) {
                    toastr.success(res.msg);
                    $('#rt_order_modal').modal('hide');
                    loadFloorPlan();
                } else {
                    toastr.error(res.msg);
                }
            }).fail(function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.msg) || "{{ __('messages.something_went_wrong') }}");
            });
        });

        $(document).on('click', '.rt-view-order', function (e) {
            e.stopPropagation();
            var id = $(this).closest('.rt-table-card').data('id');
            $.get("{{ url('modules/table-orders') }}/" + id, function (res) {
                var order = res.data.active_order;
                if (!order) {
                    toastr.error("{{ __('restaurant.no_active_order') }}");
                    return;
                }
                var html = '<p><strong>' + order.invoice_no + '</strong> · ' + Number(order.final_total).toFixed(2) + '</p><ul>';
                $.each(order.items || [], function (i, item) {
                    html += '<li>' + $('<div>').text(item.name + ' × ' + item.quantity).html() + '</li>';
                });
                html += '</ul>';
                if (order.notes) {
                    html += '<p>' + $('<div>').text(order.notes).html() + '</p>';
                }
                $('#rt_view_order_meta').text(res.data.name);
                $('#rt_view_order_body').html(html);
                $('#rt_view_order_modal').modal('show');
            });
        });

        $(document).on('click', '.rt-serve-order', function (e) {
            e.stopPropagation();
            var orderId = $(this).data('order');
            $.post("{{ url('modules/table-orders/serve') }}/" + orderId, { _token: '{{ csrf_token() }}' }, function (res) {
                if (res.success) {
                    toastr.success(res.msg);
                    loadFloorPlan();
                } else {
                    toastr.error(res.msg);
                }
            }).fail(function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.msg) || "{{ __('messages.something_went_wrong') }}");
            });
        });

        $(document).on('click', '.rt-cancel-order', function (e) {
            e.stopPropagation();
            var orderId = $(this).data('order');
            if (!window.confirm("{{ __('restaurant.cancel_order') }}")) {
                return;
            }
            $.post("{{ action([\App\Http\Controllers\Restaurant\TableOrderController::class, 'cancel']) }}", {
                    _token: '{{ csrf_token() }}',
                    order_id: orderId
                }, function (res) {
                    if (res.success) {
                        toastr.success(res.msg);
                        loadFloorPlan();
                    } else {
                        toastr.error(res.msg);
                    }
                });
        });

        $(document).on('click', '.rt-status', function (e) {
            e.stopPropagation();
            var id = $(this).closest('.rt-table-card').data('id');
            var status = $(this).data('status');
            if (String(status) === '1') {
                openReserveModal(id, null, 0, '', '');
                return;
            }
            postTableStatus(id, status, {});
        });

        $('a[href="#floor_plan_tab"]').on('shown.bs.tab', function () {
            if (floorPoll) {
                clearInterval(floorPoll);
            }
            floorPoll = setInterval(function () {
                if ($('#floor_plan_tab').hasClass('active')) {
                    loadFloorPlan();
                }
            }, 8000);
        });

        if (window.Echo) {
            Echo.channel('table-orders.{{ session("user.business_id") }}')
                .listen('.table:updated', loadFloorPlan)
                .listen('.order:created', loadFloorPlan)
                .listen('.order:updated', loadFloorPlan);
        }

        $('#rt_confirm_ok').on('click', function () {
            if (!pendingDelete.href) {
                return;
            }
            var $btn = $(this).addClass('is-loading');
            $.ajax({
                method: 'DELETE',
                url: pendingDelete.href,
                dataType: 'json',
                data: { _token: '{{ csrf_token() }}' },
                success: function (result) {
                    $btn.removeClass('is-loading');
                    $('#rt_confirm_modal').modal('hide');
                    if (result.success == true) {
                        toastr.success(result.msg);
                        if (pendingDelete.table) {
                            pendingDelete.table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading');
                    toastr.error("{{ __('messages.something_went_wrong') }}");
                }
            });
        });
    });
</script>
@endsection
