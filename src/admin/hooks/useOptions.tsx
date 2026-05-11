import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { PluginSettings } from '../types/global';

const DEFAULT_SETTINGS: PluginSettings = {
	page_id: null,
	meta_key: '_health_data',
};

export default function useOptions() {
	const [ settings, setSettings ] =
		useState< PluginSettings >( DEFAULT_SETTINGS );
	const [ selectedPageId, setSelectedPageId ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ selectedPageName, setSelectedPageName ] = useState(
		cnoHealthDashboardApiSettings.selectedPageOption?.label ?? ''
	);
	const [ savedSelectedPageName, setSavedSelectedPageName ] = useState(
		cnoHealthDashboardApiSettings.selectedPageOption?.label ?? ''
	);
	const nonce = cnoHealthDashboardApiSettings.nonce;
	useEffect( () => {
		if ( nonce ) {
			apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
		}
	}, [ nonce ] );

	useEffect( () => {
		let didCancel = false;

		const loadOptions = async () => {
			try {
				const fetchedSettings = await apiFetch< PluginSettings >( {
					path: '/cno-health-dashboard-settings/v1/settings',
				} );

				if ( ! didCancel ) {
					setSettings( fetchedSettings );
					setSelectedPageId(
						fetchedSettings.page_id
							? String( fetchedSettings.page_id )
							: ''
					);
				}
			} finally {
				if ( ! didCancel ) {
					setIsLoading( false );
				}
			}
		};

		void loadOptions();

		return () => {
			didCancel = true;
		};
	}, [] );

	const saveSelectedPageId = async ( pageId: string, pageName = '' ) => {
		const normalizedPageId = pageId ? Number( pageId ) : null;
		setSelectedPageId( pageId );

		const nextSettings: PluginSettings = {
			...settings,
			page_id: normalizedPageId,
		};

		setSettings( nextSettings );
		setIsSaving( true );

		try {
			await apiFetch( {
				path: '/cno-health-dashboard-settings/v1/settings',
				method: 'POST',
				data: nextSettings,
			} );
			setSavedSelectedPageName( pageName );
		} finally {
			setIsSaving( false );
		}
	};

	return {
		selectedPageId,
		setSelectedPageId,
		saveSelectedPageId,
		isLoading,
		isSaving,
		selectedPageName,
		setSelectedPageName,
		savedSelectedPageName,
		setSavedSelectedPageName,
	};
}
