/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SelectableCard } from '../../../../components';
import Question from '../question';
import { StyledIsClientOptions, self, client } from '../styles';

export default function IsClient( { question, onAnswer, isAnswering } ) {
	return (
		<Question
			prompt={ question.prompt }
			description={ question.description }
		>
			<StyledIsClientOptions>
				<SelectableCard
					disabled={ isAnswering }
					onClick={ () => onAnswer( false ) }
					title={ __( 'My Own Website', 'it-l10n-ithemes-security-pro' ) }
					direction="vertical"
					icon={ self }
				/>
				<SelectableCard
					disabled={ isAnswering }
					onClick={ () => onAnswer( true ) }
					title={ __( 'Client Website', 'it-l10n-ithemes-security-pro' ) }
					direction="vertical"
					icon={ client }
				/>
			</StyledIsClientOptions>
		</Question>
	);
}
