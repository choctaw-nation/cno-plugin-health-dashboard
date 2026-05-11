import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { formatListBullets } from '@wordpress/icons';
/**
 * Internal dependencies
 */
import block from './block.json';

registerBlockType( block.name, {
	icon: formatListBullets,
	edit: () => {
		const blockProps = useBlockProps();
		return (
			<ul { ...blockProps }>
				<li>Health Data Status List</li>
				<li>Health Data Status List</li>
				<li>Health Data Status List</li>
			</ul>
		);
	},
	save() {
		const blockProps = useBlockProps.save( { id: 'app' } );
		return <div { ...blockProps } />;
	},
} );
