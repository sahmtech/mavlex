<style>
	.sell-create-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		margin-bottom: 4px;
		padding-bottom: 0;
	}
	.sell-create-header h1 {
		margin: 0;
		font-size: 22px;
		font-weight: 700;
	}
	.sell-create-location {
		width: 280px;
		max-width: 42%;
	}
	.sell-create-page {
		padding-top: 6px;
	}
	.sell-create-page .tw-mb-4 {
		margin-bottom: 8px !important;
	}
	.sell-create-page .tw-rounded-xl > .tw-p-2 {
		padding: 6px 8px !important;
	}
	.sell-create-page .tw-py-2 {
		padding-top: 0 !important;
		padding-bottom: 0 !important;
	}
	.sell-create-page .sm\:tw-px-5 {
		padding-left: 2px !important;
		padding-right: 2px !important;
	}
	.sell-create-page .form-group {
		margin-bottom: 8px;
	}
	.sell-create-page .help-block {
		margin-bottom: 0;
	}
	.sell-product-search .form-group {
		margin-bottom: 8px;
	}
	.sell-product-search .form-control,
	.sell-product-search .input-group-btn .btn {
		height: 42px;
	}
	.sell-product-search .form-control {
		font-size: 15px;
	}
	.sell-create-page #pos_table {
		margin-bottom: 0;
	}
	#add_sell_form .sell-lines-scroll {
		height: 380px;
		min-height: 380px;
		max-height: 380px;
		overflow-y: auto;
		background: #fff;
		border: 1px solid #e6e8ee;
	}
	#add_sell_form .sell-lines-scroll > #pos_table {
		margin-bottom: 0;
		border: 0;
	}
	#add_sell_form .sell-lines-scroll #pos_table thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		background: #e2e6eb;
	}
	.sell-invoice-form .sell-lines-scroll.is-scrollable {
		overflow-y: auto;
	}
	.sell-invoice-form .sell-lines-scroll.is-scrollable #pos_table thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		background: #e2e6eb;
	}
	.sell-create-page .row.col-sm-12 {
		float: none;
		width: 100%;
		margin-left: 0;
		margin-right: 0;
		padding: 0;
	}
	.sell-address-strip {
		display: flex;
		gap: 18px;
		margin-top: 2px;
		padding-top: 8px;
		border-top: 1px solid #eef0f4;
		font-size: 12px;
		line-height: 1.35;
		color: #4b5563;
	}
	.sell-address-strip > div {
		flex: 1;
		min-width: 0;
	}
	.sell-address-strip strong {
		color: #111827;
	}
	.sell-amount-readout {
		padding-top: 26px;
	}
	.sell-create-actions {
		margin: 2px 0 8px;
	}
	.sell-create-actions .tw-dw-btn {
		min-width: 140px;
		margin: 0 6px;
	}
	.sell-invoice-form .sell-checkout-grid {
		display: grid;
		grid-template-columns: minmax(0, 0.5fr) minmax(0, 1.5fr) minmax(0, 1fr);
		column-gap: 16px;
		row-gap: 4px;
		align-items: start;
	}
	.sell-invoice-form #payment_rows_div,
	.sell-invoice-form .sell-pay-row {
		display: contents;
	}
	.sell-invoice-form .sell-field-discount-type { grid-area: 1 / 1; }
	.sell-invoice-form .sell-field-discount-amount { grid-area: 1 / 2; }
	.sell-invoice-form .sell-pay-amount { grid-area: 1 / 3; }
	.sell-invoice-form .sell-pay-date { grid-area: 2 / 1; }
	.sell-invoice-form .sell-pay-method { grid-area: 2 / 2; }
	.sell-invoice-form .sell-field-sell-note { grid-area: 2 / 3; }
	.sell-invoice-form .sell-pay-account { grid-area: 3 / 1; }
	.sell-invoice-form .cash_denomination_div { grid-area: 4 / 1 / auto / -1; }
	.sell-invoice-form #payment_rows_div .payment_details_div { grid-area: 5 / 1 / auto / -1; }
	.sell-invoice-form .sell-pay-pdf { grid-area: 6 / 1 / auto / -1; }
	.sell-invoice-form .sell-pay-amount,
	.sell-invoice-form .sell-pay-date,
	.sell-invoice-form .sell-pay-method,
	.sell-invoice-form .sell-pay-account {
		width: auto;
		float: none;
		padding: 0;
		min-width: 0;
	}
	.sell-invoice-form .sell-pay-extra {
		grid-column: 1 / -1;
	}
	.sell-invoice-form .sell-pay-note,
	.sell-invoice-form .sell-pay-advance,
	.sell-invoice-form .sell-change-return,
	.sell-invoice-form .payment_row_index,
	.sell-invoice-form .sell-pay-row > .clearfix {
		display: none;
	}
	.sell-invoice-form .sell-checkout-grid .select2-container {
		width: 100% !important;
	}
	.sell-invoice-form .sell-checkout-grid .form-group {
		margin-bottom: 8px;
	}
	.sell-invoice-form .discount-amount-with-tax {
		display: flex;
		align-items: center;
		gap: 12px;
	}
	.sell-invoice-form .discount-amount-with-tax .input-group {
		flex: 1;
		min-width: 0;
	}
	.sell-invoice-form .discount-tax-choice {
		display: flex;
		flex-direction: row;
		align-items: center;
		gap: 12px;
		white-space: nowrap;
		font-size: 13px;
		font-weight: 600;
	}
	.sell-invoice-form .discount-tax-choice label {
		margin: 0;
		font-weight: 600;
		cursor: pointer;
	}
	.sell-invoice-form .discount-tax-choice input {
		margin-left: 4px;
	}
	.sell-invoice-form .sell-order-summary {
		max-width: none;
		margin: 0;
	}
	.sell-invoice-form .sell-summary-row {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 16px 32px;
		padding: 10px 0;
		border-bottom: 1px solid #eef0f4;
	}
	.sell-invoice-form .sell-summary-row:last-of-type {
		border-bottom: 0;
		padding-bottom: 0;
	}
	.sell-invoice-form .sell-summary-item {
		display: flex;
		justify-content: space-between;
		align-items: baseline;
		gap: 12px;
	}
	.sell-invoice-form .sell-summary-label {
		font-weight: 700;
		font-size: 18px;
		line-height: 1.3;
	}
	@media (max-width: 768px) {
		.sell-invoice-form .sell-checkout-grid {
			grid-template-columns: 1fr;
		}
		.sell-invoice-form .sell-field-discount-type,
		.sell-invoice-form .sell-field-discount-amount,
		.sell-invoice-form .sell-pay-amount,
		.sell-invoice-form .sell-pay-date,
		.sell-invoice-form .sell-pay-method,
		.sell-invoice-form .sell-field-sell-note,
		.sell-invoice-form .sell-pay-account {
			grid-area: auto;
		}
	}
	@media (max-width: 768px) {
		.sell-create-header {
			flex-direction: column;
			align-items: stretch;
		}
		.sell-create-location {
			width: 100%;
			max-width: none;
		}
		.sell-address-strip {
			flex-direction: column;
			gap: 6px;
		}
	}
</style>
