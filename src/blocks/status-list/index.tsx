import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { formatListBullets } from '@wordpress/icons';
/**
 * Internal dependencies
 */
import block from './block.json';
import Edit from './Edit';

registerBlockType( block.name, {
	icon: formatListBullets,
	edit: Edit,
	save() {
		const blockProps = useBlockProps.save( { id: 'app' } );
		return <div { ...blockProps } />;
	},
} );
