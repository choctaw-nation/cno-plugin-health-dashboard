import React, { useEffect, useState } from 'react';
import Chart from './components/Chart';
import { rawData } from './utilities/types';

async function fetchData(): Promise< rawData > {
	try {
		const response = await fetch(
			`${ cnoSiteData.rootUrl }/wp-json/cno/v1/health-data`
		);
		const data = await response.json();
		const json = JSON.parse( data );
		return json;
	} catch ( err ) {
		return err;
	}
}

export default function App() {
	const [ isLoading, setIsLoading ] = useState( true );
	const [ errorMessage, setErrorMessage ] = useState( null );
	const [ data, setData ] = useState( null );

	useEffect( () => {
		setIsLoading( true );
		fetchData()
			.then( ( healthData ) => {
				setData( healthData );
			} )
			.catch( ( err ) => {
				console.error( err );
				setErrorMessage(
					'Error fetching data. Try refreshing your browser.'
				);
			} )
			.finally( () => setIsLoading( false ) );
	}, [] );

	const layout = window.innerWidth < 500 ? 'vertical' : 'horizontal';

	return (
		<div className="col">
			{ isLoading && <p>Loading data...</p> }
			{ errorMessage && <p>{ errorMessage }</p> }
			{ ! isLoading && data.length > 0 && (
				<Chart data={ data } layout={ layout } />
			) }
			{ ! isLoading && ! data.length && (
				<div
					className="col bg-warning p-5 fs-6 bg-opacity-50 fw-bold text-dark"
					role="alert"
				>
					No data found!
				</div>
			) }
		</div>
	);
}
