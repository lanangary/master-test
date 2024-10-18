/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	BelowToolbarFill,
	EditCardsFill,
} from '@ithemes/security.dashboard.api';
import {
	useConfigContext,
	PromoCard,
} from '@ithemes/security.dashboard.dashboard';

export default function App() {
	const { installType } = useConfigContext();

	return (
		<>
			<BelowToolbarFill>
				{ ( { page, dashboardId } ) =>
					dashboardId > 0 && page === 'view-dashboard' && (
						<>
						</>
					)
				}
			</BelowToolbarFill>
			{ installType === 'free' && (
				<EditCardsFill>
					<PromoCard title={ __( 'Trusted Devices', 'it-l10n-ithemes-security-pro' ) } />
					<PromoCard title={ __( 'Updates Summary', 'it-l10n-ithemes-security-pro' ) } />
					<PromoCard title={ __( 'User Security Profiles', 'it-l10n-ithemes-security-pro' ) } />
				</EditCardsFill>
			) }
		</>
	);
}
