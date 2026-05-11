import defaultConfig from '@wordpress/scripts/config/webpack.config.js';

const blocks = [ 'health-dashboard-chart', 'latest-data' ];

export default {
	...defaultConfig,
	...{
		entry: () => ( {
			...defaultConfig.entry(),
			'admin/admin-app': `./src/admin/index.tsx`,
			...blocks.reduce( ( entries, block ) => {
				entries[
					`blocks/${ block }`
				] = `./src/blocks/${ block }/index.tsx`;
				return entries;
			}, {} ),
		} ),
	},
};
