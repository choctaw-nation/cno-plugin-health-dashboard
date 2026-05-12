import { Spinner } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';
export default function Edit() {
	const blockProps = useBlockProps();
	const [ statusList, setStatusList ] = useState< Record<
		string,
		'Increasing' | 'Declining' | 'Plateauing' | 'No data'
	> | null >( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ errorMessage, setErrorMessage ] = useState( null );

	useEffect( () => {
		apiFetch<
			Record<
				string,
				'Increasing' | 'Declining' | 'Plateauing' | 'No data'
			>
		>( {
			path: '/cno/v1/health-data/status-list',
		} )
			.then( ( data ) => {
				setStatusList( data );
				setIsLoading( false );
			} )
			.catch( ( error ) => {
				setErrorMessage( error.message );
				setIsLoading( false );
			} );
	}, [] );
	if ( isLoading ) {
		return <Spinner { ...blockProps } />;
	}
	if ( errorMessage ) {
		return <p { ...blockProps }>Error: { errorMessage }</p>;
	}
	return (
		<div { ...blockProps }>
			{ isLoading && <p>Loading...</p> }
			{ errorMessage && <p>Error: { errorMessage }</p> }
			{ statusList && (
				<ul>
					{ Object.entries( statusList ).map(
						( [ disease, status ] ) => (
							<li key={ disease }>
								{ disease }: { status }
							</li>
						)
					) }
				</ul>
			) }
		</div>
	);
}
