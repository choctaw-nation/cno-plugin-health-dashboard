import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

export type WpPage = {
	id: number;
	title: {
		rendered: string;
	};
};

export default function useSearch( search: string ) {
	const [ pages, setPages ] = useState< WpPage[] >( [] );
	const [ isLoading, setIsLoading ] = useState( false );

	useEffect( () => {
		let didCancel = false;
		const controller = new AbortController();
		const timeoutId = window.setTimeout( () => {
			setIsLoading( true );

			const query = new URLSearchParams( {
				_fields: 'id,title',
				per_page: '20',
			} );

			if ( search.trim() ) {
				query.set( 'search', search.trim() );
			}

			const fetchPages = async () => {
				try {
					const fetchedPages = await apiFetch< WpPage[] >( {
						path: `/wp/v2/pages?${ query.toString() }`,
						signal: controller.signal,
					} );

					if ( ! didCancel ) {
						setPages( fetchedPages );
					}
				} catch {
					if ( ! didCancel ) {
						setPages( [] );
					}
				} finally {
					if ( ! didCancel ) {
						setIsLoading( false );
					}
				}
			};

			void fetchPages();
		}, 150 );

		return () => {
			didCancel = true;
			window.clearTimeout( timeoutId );
			controller.abort();
		};
	}, [ search ] );

	return {
		pages,
		isLoading,
	};
}
