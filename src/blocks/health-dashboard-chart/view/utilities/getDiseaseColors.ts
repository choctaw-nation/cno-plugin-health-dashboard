import { diseaseNames } from './types';

/**
 * CNO Brand Color Groups
 */
const colorGroups = {
	1: {
		primary: [ '#f9423a', '#ff7f32' ],
		supporting: [ '#8a1538', '#e31c79', '#ea27c2' ],
	},
	2: {
		primary: [ '#e57200', '#ffb600' ],
		supporting: [ '#ff9e1b', '#c05131', '#f93822' ],
	},
	3: {
		primary: [ '#007b5f', '#d0c883' ],
		supporting: [ '#e0e722', '#abad23', '#071d49' ],
	},
	4: {
		primary: [ '#62b5e5', '#001e62' ],
		supporting: [ '#eaa794', '#fa4616', '#daaa00' ],
	},
	5: {
		primary: [ '#512d6d', '#ac4fc6' ],
		supporting: [ '#221c35', '#cba3d8', '#2c5234' ],
	},
	6: {
		primary: [ '#bb29bb', '#f1e6b2' ],
		supporting: [ '#bcd19b', '#279989', '#6c1d45' ],
	},
	7: {
		primary: [ '#ffb81c', '#85b09a' ],
		supporting: [ '#c66e4e', '#dfd1a7', '#fae053' ],
	},
	8: {
		primary: [ '#5bc500', '#67d2df' ],
		supporting: [ '#e2e868', '#13322b', '#0d5257' ],
	},
	9: {
		primary: [ '#00558c', '#b5e3d8' ],
		supporting: [ '#d3bc8d', '#ffa38b', '#e04f39' ],
	},
	10: {
		primary: [ '#654ea3', '#c98bdb' ],
		supporting: [ '#f6eb61', '#999b30', '#653379' ],
	},
	11: {
		primary: [ '#00677f', '#0085ad' ],
		supporting: [ '#40c1ac', '#d7d2cb', '#003d4c' ],
	},
	12: {
		primary: [ '#009cde', '#b9d9eb' ],
		supporting: [ '#6cc24a', '#bce194', '#003a70' ],
	},
};

/**
 * Get a random color from declared CSS variables
 * @return Random CSS variable
 */
export default function getColors(
	diseaseName: diseaseNames,
	type: 'dot' | 'line' = 'line'
): string {
	const colors = {
		covid: {
			dot: colorGroups[ 2 ].primary[ 1 ],
			line: `rgb(192,81,48)`,
		},
		flu: {
			dot: colorGroups[ 12 ].supporting[ 1 ],
			line: `rgb(108,194,74)`,
		},
		rsv: {
			dot: colorGroups[ 12 ].primary[ 1 ],
			line: `rgb(0,58,112)`,
		},
	};

	return colors[ diseaseName ][ type ];
}
