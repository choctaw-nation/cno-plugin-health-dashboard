import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';

registerBlockType( block.name, {
	edit: Edit,
	save() {
		const blockProps = useBlockProps.save( { id: 'app' } );
		return <div { ...blockProps } />;
	},
} );
