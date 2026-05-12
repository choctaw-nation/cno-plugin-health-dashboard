import React, { useEffect, useState } from 'react';
import Chart from './components/Chart';
import { rawData } from './utilities/types';

async function fetchData(): Promise< rawData[] > {
	const response = await fetch( `/wp-json/cno/v1/health-data/data` );
	const data = await response.json();
	return data;
}

export default function App() {
	const [ isLoading, setIsLoading ] = useState( true );
	const [ errorMessage, setErrorMessage ] = useState< string >( '' );
	const [ data, setData ] = useState< rawData[] | null >( null );

	useEffect( () => {
		setIsLoading( true );
		fetchData()
			.then( ( healthData ) => {
				setData( healthData );
			} )
			.catch( ( err ) => {
				setErrorMessage(
					'Error fetching data. Try refreshing your browser. ' +
						err.message
				);
			} )
			.finally( () => setIsLoading( false ) );
	}, [] );

	const shouldShowChart = ! errorMessage && data && data.length > 0;

	return (
		<>
			{ isLoading && (
				<div
					className="placeholder-glow"
					style={ { minHeight: 'inherit' } }
				>
					<div className="placeholder"></div>
					<p
						style={ {
							position: 'absolute',
							top: '50%',
							left: '50%',
							transform: 'translate(-50%, -50%)',
							margin: 0,
						} }
					>
						Loading data...
					</p>
				</div>
			) }
			{ errorMessage && <p>{ errorMessage }</p> }
			{ ! isLoading && shouldShowChart && <Chart data={ data } /> }
			{ ! isLoading && ! shouldShowChart && (
				<div
					className="col bg-warning p-5 fs-6 bg-opacity-50 fw-bold text-dark"
					role="alert"
				>
					No data found!
				</div>
			) }
		</>
	);
}
