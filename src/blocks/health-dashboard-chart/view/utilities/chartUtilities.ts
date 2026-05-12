import { chartData, diseaseNames, rawData } from './types';

/**
 * Transforms raw Health Data into useable chart data
 */
export function handleData( data: rawData[] ): chartData[] {
	const newData: chartData[] = [];
	data.forEach( ( item ) => {
		newData.push( {
			dateEnd: item.WeekOfYr.end,
			diseases: [
				...Object.entries( item.diseases ).map( ( diseases ) => {
					const [ disease, value ] = diseases;
					return {
						[ disease.toLowerCase() ]: {
							rate: value.PositivityRate,
						},
					};
				} ),
			],
		} );
	} );
	return newData;
}

/**
 * Get all the diseases from the chart data to use as dataKeys
 */
export function getKeys( data: chartData[] ): diseaseNames[] {
	const keys: diseaseNames[] = [];
	data.forEach( ( item ) => {
		item.diseases.forEach( ( disease ) => {
			const diseaseName = Object.keys( disease )[ 0 ] as diseaseNames;
			if ( ! keys.includes( diseaseName ) ) {
				keys.push( diseaseName );
			}
		} );
	} );
	return keys;
}

/**
 * Transforms the disease name to a more readable format.
 *
 * @param {string} str The string to capitalize
 * @return {string} the disease name
 */
export function nameTransformer( str: string ): string {
	const names = {
		covid: 'COVID-19',
		rsv: 'RSV',
	};
	if ( names[ str ] ) {
		return names[ str ];
	}
	return str.charAt( 0 ).toUpperCase() + str.slice( 1 );
}

/**
 * Gets the highest positivity rate from the data
 *
 */
export function calcMaxDomainFromPositivityRate( data: rawData[] ): number {
	let highestRate = 0;
	data.forEach( ( item ) => {
		Object.values( item.diseases ).forEach( ( disease ) => {
			const rate = Math.round( disease.PositivityRate );
			if ( rate > highestRate ) {
				highestRate = rate;
			}
		} );
	} );
	if ( highestRate <= 50 ) {
		highestRate += 15;
	} else {
		highestRate += 10;
	}
	return highestRate;
}
