/** @format **/

/**
 * External dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { dateI18n } from '@wordpress/date';
import { __ } from '@wordpress/i18n';
import moment from 'moment';
import Currency from '@woocommerce/currency';
import { TableCard } from '@woocommerce/components';

/**
 * Internal dependencies.
 */
import OrderLink from 'components/order-link';
import DisputeStatusChip from 'components/dispute-status-chip';
import ClickableCell from 'components/clickable-cell';
import DetailsLink, { getDetailsURL } from 'components/details-link';
import Page from 'components/page';
import { reasons } from './strings';
import { formatStringValue } from '../util';

const { formatCurrency } = Currency();

const headers = [
	{ key: 'details', label: '', required: true, cellClassName: 'info-button' },
	{ key: 'amount', label: __( 'Amount', 'woocommerce-payments' ), required: true },
	{ key: 'status', label: __( 'Status', 'woocommerce-payments' ), required: true },
	{ key: 'reason', label: __( 'Reason', 'woocommerce-payments' ), required: true },
	{ key: 'source', label: __( 'Source', 'woocommerce-payments' ), required: true },
	{ key: 'order', label: __( 'Order #', 'woocommerce-payments' ), required: true },
	{ key: 'customer', label: __( 'Customer', 'woocommerce-payments' ) },
	{ key: 'email', label: __( 'Email', 'woocommerce-payments' ), visible: false },
	{ key: 'country', label: __( 'Country', 'woocommerce-payments' ), visible: false },
	{ key: 'created', label: __( 'Disputed on', 'woocommerce-payments' ), required: true },
	{ key: 'dueBy', label: __( 'Respond by', 'woocommerce-payments' ), required: true },
];

export const DisputesList = ( props ) => {
	const { disputes, showPlaceholder } = props;
	const disputesData = disputes.data || [];

	const rows = disputesData.map( ( dispute ) => {
		const order = dispute.order ? {
			value: dispute.order.number,
			display: <OrderLink order={ dispute.order } />,
		} : null;

		const clickable = ( children ) => <ClickableCell href={ getDetailsURL( dispute.id, 'disputes' ) }>{ children }</ClickableCell>;

		const detailsLink = <DetailsLink id={ dispute.id } parentSegment="disputes" />;

		const reasonMapping = reasons[ dispute.reason ];
		const reasonDisplay = reasonMapping ? reasonMapping.display : formatStringValue( dispute.reason );

		const charge = dispute.charge || {};
		const source = ( ( charge.payment_method_details || {} ).card || {} ).brand;
		const customer = charge.billing_details || {};

		const data = {
			amount: { value: dispute.amount / 100, display: clickable( formatCurrency( dispute.amount / 100 ) ) },
			status: { value: dispute.status, display: clickable( <DisputeStatusChip status={ dispute.status } /> ) },
			reason: { value: dispute.reason, display: clickable( reasonDisplay ) },
			source: {
				value: source,
				display: clickable( <span className={ `payment-method__brand payment-method__brand--${ source }` } /> ),
			},
			created: { value: dispute.created * 1000, display: clickable( dateI18n( 'M j, Y', moment( dispute.created * 1000 ) ) ) },
			dueBy: {
				value: dispute.evidence_details.due_by * 1000,
				display: clickable( dateI18n( 'M j, Y / g:iA', moment( dispute.evidence_details.due_by * 1000 ) ) ),
			},
			order,
			customer: { value: customer.name, display: clickable( customer.name ) },
			email: { value: customer.email, display: clickable( customer.email ) },
			country: { value: ( customer.address || {} ).country, display: clickable( ( customer.address || {} ).country ) },
			details: { value: dispute.id, display: detailsLink },
		};

		return headers.map( ( { key } ) => data[ key ] || { display: null } );
	} );

	return (
		<Page>
			<TableCard
				title={ __( 'Disputes', 'woocommerce-payments' ) }
				isLoading={ showPlaceholder }
				rowsPerPage={ 10 }
				totalRows={ 10 }
				headers={ headers }
				rows={ rows }
			/>
		</Page>
	);
};

// Temporary MVP data wrapper
export default () => {
	const [ disputes, setDisputes ] = useState( [] );
	const [ loading, setLoading ] = useState( false );

	const fetchDisputes = async () => {
		setLoading( true );
		try {
			setDisputes( await apiFetch( { path: '/wc/v3/payments/disputes' } ) );
		} finally {
			setLoading( false );
		}
	};
	useEffect( () => {
		fetchDisputes();
	}, [] );

	return (
		<DisputesList
			disputes={ disputes }
			showPlaceholder={ loading }
		/>
	);
};
