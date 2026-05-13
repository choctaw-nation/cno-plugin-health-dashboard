import { createRoot } from 'react-dom/client';
import App from './view/App';
import './view.css';

const root = document.getElementById( 'app' );
if ( root ) {
	createRoot( root ).render( <App /> );
}
