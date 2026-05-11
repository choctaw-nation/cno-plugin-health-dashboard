import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import block from './block.json';

registerBlockType( block.name, {
	edit: () => {
		const blockProps = useBlockProps( {
			style: {
				width: '100%',
				backgroundColor: '#ccc',
				color: 'black',
				padding: '3rem',
			},
		} );
		return <div { ...blockProps }>Health Data Chart</div>;
	},
	save() {
		const blockProps = useBlockProps.save( { id: 'app' } );
		return <div { ...blockProps } />;
	},
} );
