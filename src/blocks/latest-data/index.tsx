import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { timeToRead } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import block from './block.json';

registerBlockType( block.name, {
	icon: timeToRead,
	edit: () => {
		const blockProps = useBlockProps();
		return <div { ...blockProps }>May 2, 2025</div>;
	},
	save() {
		const blockProps = useBlockProps.save( { id: 'app' } );
		return <div { ...blockProps } />;
	},
} );
