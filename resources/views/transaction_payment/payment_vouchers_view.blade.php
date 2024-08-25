<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="padding: 0px 30px">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            @if (!empty($transaction))
                <div class="row">
                    {{-- @if (in_array($transaction->type, ['purchase', 'purchase_return'])) --}}

                        <div class="col-xs-3"
                            style="    display: flex;
                            padding: 37px;
                        align-items: center;
                        font-size: large;
                                font-weight: bold;
                        justify-content: flex-start;
                         ">
                            <p>@format_currency($single_payment_line->amount)</p>
                        </div>
                        <div class="col-xs-6" style="text-align: center;">
                            @if (!empty(Session::get('business.logo')))
                                <img style="width: 50px;"
                                    src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                    alt="Logo">
                            @endif
                            <h3 style="margin-top: 0px;     font-weight: bold;">سند صرف</h3>
                        </div>
                        <div class="col-xs-3"
                            style="display: flex;
                              padding-top: 37px;
                    align-items: center;
                    justify-content: flex-end;
                    
                                font-weight: bold;
                    ">
                            <p>بتاريخ :{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</p>
                        </div>

                        <div class="col-xs-12"
                            style="    display: flex;
                            justify-content: center;
                            justify-content: center;
    margin-top: -35px;
    margin-bottom: 24px;
                ">
                            @if (!empty($transaction->contact->tax_number))
                                {{ $transaction->contact->tax_number ??'' }}
                            @endif
                        </div>
                        <hr style="width:100%;text-align:left;margin-left:0">
                        <div class="row" style="padding-top: 10px;">

                            <div class="col-xs-3"
                                style="display: flex;align-items: center;justify-content: flex-start;">
                                <p>يصرف لـ السيد/السيدة</p>
                            </div>
                            <div class="col-xs-6" style="text-align: center;border-bottom: 1px dashed;">
                                <h6
                                    style="margin: 0px;font-size: large;
                                font-weight: bold;
                            ">
                                    {{ $transaction->contact->supplier_business_name??"" }}</h6>
                            </div>
                            <div class="col-xs-3" style="display: flex;align-items: center;justify-content: flex-end;">
                                <p>Pay to Mr./Mrs</p><br>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px;">
                            <div class="col-xs-3"
                                style="display: flex;align-items: center;justify-content: flex-start;">
                                <p>مبلغ وقدره</p>
                            </div>
                            <div class="col-xs-6" style="text-align: center;border-bottom: 1px dashed;">
                                <h6
                                    style="margin: 0px;    font-size: large;
                                font-weight: bold;
                            ">
                                    @format_currency($single_payment_line->amount) / {{ $single_payment_line->amount_string ??""}} </h6>
                            </div>
                            <div class="col-xs-3" style="display: flex;align-items: center;justify-content: flex-end;">
                                <p>Amount of</p>
                            </div>
                        </div>

                        <div class="row" style="padding-top: 10px;">
                            <div class="col-xs-3"
                                style="display: flex;align-items: center;justify-content: flex-start;">
                                <p>نقداً / شيك رقم</p>
                            </div>
                            <div class="col-xs-6" style="text-align: center;border-bottom: 1px dashed;">
                                <h6
                                    style="margin: 0px;    font-size: large;
                              font-weight: bold;
                          ">
                                    {{ $single_payment_line->payment_ref_no??"" }}
                                    {{ $payment_types[$single_payment_line->method] ?? '' }}</h6>
                            </div>
                            <div class="col-xs-3" style="display: flex;align-items: center;justify-content: flex-end;">
                                <p>.Cash / Cheque No</p>
                            </div>
                        </div>

                        <div class="row" style="padding-top: 10px;">
                            <div class="col-xs-3"
                                style="display: flex;align-items: center;justify-content: flex-start;">
                                <p>على بنك</p>
                            </div>
                            <div class="col-xs-6" style="text-align: center;border-bottom: 1px dashed;">
                                <h6
                                    style="margin: 0px;    font-size: large;    padding-top: 16px;
                            font-weight: bold;
                        ">
                                    {{ '' }}
                                </h6>
                            </div>
                            <div class="col-xs-3" style="display: flex;align-items: center;justify-content: flex-end;">
                                <p>Bank</p>
                            </div>
                        </div>

                    
                        <div class="row" style="padding-top: 10px;">

                            <div class="col-xs-3"
                                style="display: flex;align-items: center;justify-content: flex-start;">
                                <p> بتاريخ</p>
                            </div>
                            <div class="col-xs-6" style="text-align: center;border-bottom: 1px dashed;">
                                <h6
                                    style="margin: 0px;    font-size: large;
                                font-weight: bold;
                            ">
                                    {{ @format_datetime($single_payment_line->paid_on)??"" }}</h6>
                            </div>
                            <div class="col-xs-3" style="display: flex;align-items: center;justify-content: flex-end;">
                                <p>Date</p>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px;">

                            <div class="col-xs-3"
                                style="display: flex;align-items: center;justify-content: flex-start;">
                                <p>وذلك عن ثابتة</p>
                            </div>
                            <div class="col-xs-6" style="text-align: center;border-bottom: 1px dashed;">
                                <h6
                                    style="margin: 0px;    font-size: large;
                                font-weight: bold;
                            ">
                                    {{ $transaction->ref_no??"" }}
                                </h6>
                            </div>
                            <div class="col-xs-3" style="display: flex;align-items: center;justify-content: flex-end;">
                                <p> For</p><br>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px;">

                            <div class="col-xs-6"
                                style="display: flex;align-items: center;text-align: center;justify-content: center;">
                                <p>المستلم - Receiver </p>
                            </div>

                            <div class="col-xs-6"
                                style="display: flex;text-align: center;align-items: center;justify-content:center;">
                                <p>المحاسب - Accountant</p><br>
                            </div>
                            <div class="row">

                                <div class="col-xs-6" style="text-align: center;padding: 19px 58px;">
                                    <h6
                                        style="margin-top: 0px;border-bottom: 1px dashed;    font-size: large;
                                    font-weight: bold;
                                ">

                                    </h6>
                                </div>

                                <div class="col-xs-6" style="text-align: center;padding: 19px 58px;">
                                    <h6
                                        style="margin-top: 0px;border-bottom: 1px dashed;    font-size: large;
                                    font-weight: bold;
                                ">
                                    </h6>
                                </div>
                            </div>


                        </div>
                      
                    @else
                       
                    {{-- @endif --}}
                </div>
            @endif
        
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary no-print" aria-label="Print"
                onclick="$(this).closest('div.modal').printThis();">
                <i class="fa fa-print"></i> @lang('messages.print')
            </button>
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.close')
            </button>
        </div>
    </div>
</div>
