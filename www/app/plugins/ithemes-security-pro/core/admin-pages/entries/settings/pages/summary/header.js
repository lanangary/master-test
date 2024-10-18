/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import { Logo } from '@ithemes/security-ui';
import Improvements from './improvements';

export default function Header( { installType } ) {
	const dashboardLink = useGlobalNavigationUrl( 'dashboard' ),
		settingsLink = useGlobalNavigationUrl( 'settings' );

	return (
		<Flex direction="column" gap={ 8 } expanded={ false } align="start">
			<Logo size={ 44 } />
			<Flex as="header" direction="column" gap={ 2 } expanded={ false }>
				<Heading
					level={ 1 }
					text={ installType === 'free'
						? __( 'Great Work! Thanks to Solid Security Basic, your site is secure and ready for your users.', 'it-l10n-ithemes-security-pro' )
						: __( 'Great Work! Your site is ready and is more secure than ever!', 'it-l10n-ithemes-security-pro' ) }
					size={ TextSize.GIGANTIC }
					weight={ TextWeight.NORMAL }
				/>
				<Text
					text={ installType === 'free'
						? __( 'Use your security dashboard for insights into your users’ activity and potential threats to your site. From there you’ll be guided to actions you can take.', 'it-l10n-ithemes-security-pro' )
						: __( 'If you want to dig into your site’s security, check out your security dashboard, and make changes via settings.', 'it-l10n-ithemes-security-pro' ) }
					size={ TextSize.EXTRA_LARGE }
					variant={ TextVariant.DARK }
				/>
			</Flex>
			<Flex gap={ 4 } justify="start">
				<Button
					variant={ installType === 'free' ? 'secondary' : 'primary' }
					href={ dashboardLink }
					text={ __( 'Dashboard', 'it-l10n-ithemes-security-pro' ) }
				/>
				<Button
					variant={ installType === 'free' ? 'secondary' : 'primary' }
					href={ settingsLink }
					text={ __( 'Settings', 'it-l10n-ithemes-security-pro' ) }
				/>
			</Flex>
			<Improvements installType={ installType } />
		</Flex>
	);
}
