import React from 'react';
import {
	LineChart,
	Line,
	XAxis,
	YAxis,
	Tooltip,
	Legend,
	CartesianGrid,
} from 'recharts';

import { rawData } from '../utilities/types';
import {
	calcMaxDomainFromPositivityRate,
	nameTransformer,
	getKeys,
	handleData,
} from '../utilities/chartUtilities';
import getColors from '../utilities/getDiseaseColors';

export default function Chart( { data }: { data: rawData[] } ) {
	const maxDomain = calcMaxDomainFromPositivityRate( data );
	const chartData = handleData( data );
	const keys = getKeys( chartData );
	return (
		<LineChart
			data={ chartData }
			style={ { minHeight: 'inherit', maxHeight: 500 } }
			width={ '100%' }
			responsive
		>
			<CartesianGrid />
			<Legend verticalAlign="top" />
			<Tooltip />
			<XAxis
				dataKey="dateEnd"
				label={ {
					value: 'Week of',
					position: 'bottom',
				} }
				tickCount={ 30 }
			/>
			<YAxis
				allowDecimals={ true }
				unit="%"
				domain={ [ 0, maxDomain ] }
				label={ {
					value: 'Positivity Rate',
					angle: -90,
					position: 'insideLeft',
				} }
			/>

			{ keys.map( ( key, i ) => {
				return (
					<Line
						key={ key }
						type="monotone"
						name={ `${ nameTransformer( key ) } Positivity Rate` }
						unit="%"
						dataKey={ `diseases.${ i }.${ key }.rate` }
						stroke={ getColors( key ) }
						strokeWidth={ 6 }
						dot={ { strokeWidth: 12 } }
					/>
				);
			} ) }
		</LineChart>
	);
}
