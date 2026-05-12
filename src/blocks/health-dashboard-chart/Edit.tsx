import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps( {
		style: {
			backgroundColor: '#ccc',
			color: 'black',
			padding: '3rem',
		},
	} );
	return (
		<div { ...blockProps }>
			<p>Health Data Chart</p>
			<p>Intentionally not rendered in the editor for performance.</p>
		</div>
	);
}
