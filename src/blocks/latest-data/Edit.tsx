import {
	Spinner,
	Panel,
	PanelBody,
	SelectControl,
} from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect, Fragment } from '@wordpress/element';
import { format } from '@wordpress/date';

const options = [
	{ label: 'M d, Y', value: 'F j, Y' },
	{ label: 'Y-m-d', value: 'Y-m-d' },
	{ label: 'm/d/Y', value: 'm/d/Y' },
	{ label: 'd/m/Y', value: 'd/m/Y' },
	{ label: 'm-d-Y', value: 'm-d-Y' },
	{ label: 'd-m-Y', value: 'd-m-Y' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { format: dateFormat } = attributes;
	const [ lastFetchRaw, setLastFetchRaw ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ errorMessage, setErrorMessage ] = useState( null );

	useEffect( () => {
		apiFetch< string >( { path: '/cno/v1/health-data/last-updated' } )
			.then( ( data ) => {
				setLastFetchRaw( data );
				setIsLoading( false );
			} )
			.catch( ( error ) => {
				setErrorMessage( error.message );
				setIsLoading( false );
			} );
	}, [] );
	const lastFetch = format( dateFormat, lastFetchRaw );

	const blockProps = useBlockProps();

	return (
		<Fragment>
			<InspectorControls>
				<Panel>
					<PanelBody title="Date Format">
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label="Date Format"
							value={ dateFormat }
							options={ options }
							onChange={ ( value ) =>
								setAttributes( { format: value } )
							}
						/>
					</PanelBody>
				</Panel>
			</InspectorControls>
			<div { ...blockProps }>
				{ isLoading && <Spinner /> }
				{ errorMessage && <p>Error: { errorMessage }</p> }
				<time dateTime={ lastFetchRaw }>{ lastFetch }</time>
			</div>
		</Fragment>
	);
}
