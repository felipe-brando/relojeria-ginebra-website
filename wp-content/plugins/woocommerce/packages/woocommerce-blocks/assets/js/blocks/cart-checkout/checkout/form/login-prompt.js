/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { getSetting } from '@woocommerce/settings';
import { useCheckoutContext } from '@woocommerce/base-context';

/**
 * Internal dependencies
 */
import { LOGIN_TO_CHECKOUT_URL } from '../utils';

const LoginPrompt = () => {
	const { customerId } = useCheckoutContext();

	if ( ! getSetting( 'checkoutShowLoginReminder', true ) || customerId ) {
		return null;
	}

	return (
		<>
			{ __(
				'Already have an account? ',
				'woocommerce'
			) }
			<a href={ LOGIN_TO_CHECKOUT_URL }>
				{ __( 'Log in.', 'woocommerce' ) }
			</a>
		</>
	);
};

export default LoginPrompt;
