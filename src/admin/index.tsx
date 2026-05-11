import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import App from './App';

domReady( () => {
	const root = document.getElementById( 'cno-health-dashboard-api-settings' );
	if ( root ) {
		const reactRoot = createRoot( root );
		reactRoot.render( <App /> );
	}
} );
