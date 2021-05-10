/** @format **/

/**
 * External dependencies
 */
import { Button, Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import './shared.scss';
import AccountFees from './account-fees';
import AccountStatusItem from './account-status-item';
import DepositsStatus from 'components/deposits-status';
import PaymentsStatus from 'components/payments-status';
import StatusChip from './status-chip';

const AccountStatusCard = ( props ) => {
	const { title, content } = props;
	return (
		<Card isSmall>
			<CardHeader
				className={
					'woocommerce-card__header woocommerce-account-status-header'
				}
			>
				{ title }
			</CardHeader>
			<CardBody>{ content }</CardBody>
		</Card>
	);
};

const AccountStatusError = () => {
	return (
		<AccountStatusCard
			title={
				<div className="title">
					{ __( 'Account details', 'woocommerce-payments' ) }
				</div>
			}
			content={
				<div>
					{ __(
						'Error determining the connection status.',
						'woocommerce-payments'
					) }
				</div>
			}
		/>
	);
};

const AccountStatusDetails = ( props ) => {
	const { accountStatus, accountFees } = props;

	return (
		<AccountStatusCard
			title={
				<>
					<div className="title">
						{ __( 'Account details', 'woocommerce-payments' ) }
					</div>
					<div>
						<StatusChip accountStatus={ accountStatus.status } />
					</div>
					<div className="woocommerce-account-status-header-controls">
						<Button disabled isLink>
							{ __( 'Edit details', 'woocommerce-payments' ) }
						</Button>
					</div>
				</>
			}
			content={
				<>
					{ accountStatus.email && (
						<AccountStatusItem
							label={ __(
								'Connected email:',
								'woocommerce-payments'
							) }
							value={ accountStatus.email }
						/>
					) }
					<AccountStatusItem
						label={ __( 'Payments:', 'woocommerce-payments' ) }
						value={
							<PaymentsStatus
								paymentsEnabled={
									accountStatus.paymentsEnabled
								}
							/>
						}
					/>
					<AccountStatusItem
						label={ __( 'Deposits:', 'woocommerce-payments' ) }
						value={
							<DepositsStatus
								depositsStatus={ accountStatus.depositsStatus }
							/>
						}
					/>
					<AccountStatusItem
						label={ __( 'BaseFee:', 'woocommerce-payments' ) }
						value={ <AccountFees accountFees={ accountFees } /> }
					/>
				</>
			}
		/>
	);
};

const AccountStatus = ( props ) => {
	const { accountStatus } = props;

	return accountStatus.error ? (
		<AccountStatusError />
	) : (
		<AccountStatusDetails { ...props } />
	);
};

export default AccountStatus;
