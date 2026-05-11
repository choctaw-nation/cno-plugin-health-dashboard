import { createRoot } from 'react-dom/client';
import App from './health-dashboard/App';
const root = document.getElementById( 'app' );
if ( root ) {
	createRoot( root ).render( <App /> );
}
