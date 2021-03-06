/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	withStoreCartApiHydration,
	withRestApiHydration,
} from '@woocommerce/block-hocs';
import { useStoreCart } from '@woocommerce/base-context/hooks';
import {
	CheckoutProvider,
	StoreNoticesProvider,
	ValidationContextProvider,
} from '@woocommerce/base-context';
import BlockErrorBoundary from '@woocommerce/base-components/block-error-boundary';
import { CURRENT_USER_IS_ADMIN } from '@woocommerce/settings';
import {
	renderFrontend,
	getValidBlockAttributes,
} from '@woocommerce/base-utils';
import { StoreSnackbarNoticesProvider } from '@woocommerce/base-context/providers';
import { SlotFillProvider } from '@woocommerce/blocks-checkout';

/**
 * Internal dependencies
 */
import Block from './block.js';
import blockAttributes from './attributes';
import EmptyCart from './empty-cart/index.js';

const reloadPage = () => void window.location.reload( true );

const errorBoundaryProps = {
	header: __( 'Something went wrong…', 'woocommerce' ),
	text: __(
		'The checkout has encountered an unexpected error. If the error persists, please get in touch with us for help.',
		'woocommerce'
	),
	showErrorMessage: CURRENT_USER_IS_ADMIN,
	button: (
		<button className="wc-block-button" onClick={ reloadPage }>
			{ __( 'Reload the page', 'woocommerce' ) }
		</button>
	),
};

/**
 * Wrapper component for the checkout block.
 *
 * @param {Object} props Props for the block.
 */
const CheckoutFrontend = ( props ) => {
	const { cartItems, cartIsLoading } = useStoreCart();

	return (
		<>
			{ ! cartIsLoading && cartItems.length === 0 ? (
				<EmptyCart />
			) : (
				<BlockErrorBoundary { ...errorBoundaryProps }>
					<StoreSnackbarNoticesProvider context="wc/checkout">
						<StoreNoticesProvider context="wc/checkout">
							<ValidationContextProvider>
								<SlotFillProvider>
									<CheckoutProvider>
										<Block { ...props } />
									</CheckoutProvider>
								</SlotFillProvider>
							</ValidationContextProvider>
						</StoreNoticesProvider>
					</StoreSnackbarNoticesProvider>
				</BlockErrorBoundary>
			) }
		</>
	);
};

const getProps = ( el ) => {
	return {
		attributes: getValidBlockAttributes( blockAttributes, el.dataset ),
	};
};

const getErrorBoundaryProps = () => {
	return errorBoundaryProps;
};

renderFrontend( {
	selector: '.wp-block-woocommerce-checkout',
	Block: withStoreCartApiHydration(
		withRestApiHydration( CheckoutFrontend )
	),
	getProps,
	getErrorBoundaryProps,
} );
