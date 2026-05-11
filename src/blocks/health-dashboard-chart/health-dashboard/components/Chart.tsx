import React from 'react';
import {
	LineChart,
	Line,
	XAxis,
	YAxis,
	ResponsiveContainer,
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

export default function Chart( {
	data,
	layout = 'horizontal',
}: {
	data: rawData[];
	layout: 'vertical' | 'horizontal';
} ) {
	const maxDomain = calcMaxDomainFromPositivityRate( data );
	const chartData = handleData( data );
	const keys = getKeys( chartData );
	return (
		<ResponsiveContainer minHeight={ 500 }>
			<LineChart data={ chartData } layout={ layout }>
				<CartesianGrid />
				<Legend verticalAlign="top" />
				<Tooltip />
				{ layout === 'horizontal' && (
					<>
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
					</>
				) }
				{ layout === 'vertical' && (
					<>
						<XAxis
							type="number"
							allowDecimals={ true }
							unit="%"
							domain={ [ 0, maxDomain ] }
							label={ {
								value: 'Positivity Rate',
								position: 'insideBottom',
							} }
						/>
						<YAxis
							dataKey="dateEnd"
							label={ {
								value: 'Week of',
								position: 'insideLeft',
								angle: -90,
							} }
							tickCount={ 30 }
						/>
					</>
				) }
				{ keys.map( ( key, i ) => {
					return (
						<Line
							key={ key }
							type="monotone"
							name={ `${ nameTransformer(
								key
							) } Positivity Rate` }
							unit="%"
							dataKey={ `diseases.${ i }.${ key }.rate` }
							stroke={ getColors( key ) }
							strokeWidth={ 6 }
							dot={ { strokeWidth: 12 } }
						/>
					);
				} ) }
			</LineChart>
		</ResponsiveContainer>
	);
}
