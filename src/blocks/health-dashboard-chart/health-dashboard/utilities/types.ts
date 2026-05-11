export type diseaseNames = 'covid' | 'flu' | 'rsv';

type diseaseData = {
	[ K in diseaseNames ]: {
		PositivityRate: number;
		Comparison: 'INCREASING' | 'DECREASING' | 'PLATEAUING' | null;
	};
};

export type chartData = {
	dateEnd: string;
	diseases: Array< { [ K in diseaseNames ]: { rate: number } } >;
};

export type rawData = {
	WeekOfYr: {
		start: string; // YYYY-MM-DD
		end: string; // YYYY-MM-DD
	};
	diseases: diseaseData;
};
