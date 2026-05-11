import { Panel, PanelBody, Button, Snackbar } from '@wordpress/components';
import { useState } from '@wordpress/element';

import useOptions from './hooks/useOptions';

import SearchForm from './components/SearchForm';
import { NoticeState } from './types/global';

export default function App() {
	const {
		selectedPageId,
		setSelectedPageId,
		saveSelectedPageId,
		selectedPageName,
		setSelectedPageName,
		savedSelectedPageName,
		setSavedSelectedPageName,
		isLoading,
		isSaving,
	} = useOptions();
	const [ notice, setNotice ] = useState< NoticeState | null >( null );

	const handleClearSelection = async () => {
		try {
			await saveSelectedPageId( '', '' );
			setSelectedPageName( '' );
			setSavedSelectedPageName( '' );
			setNotice( {
				message: 'Selection cleared.',
				status: 'success',
			} );
		} catch {
			setNotice( {
				message: 'Could not clear selection. Please try again.',
				status: 'error',
			} );
		}
	};

	return (
		<Panel>
			<PanelBody title="Page Selector">
				<p>
					This app uses <code>post meta</code> to cache health data
					in. Please select the page the dashboard will be displayed
					on to set up the proper store.
				</p>
				{ savedSelectedPageName && (
					<div>
						<p>
							<strong>Currently selected page: </strong>{ ' ' }
							{ savedSelectedPageName }
						</p>
						<Button
							variant="secondary"
							isDestructive={ true }
							onClick={ handleClearSelection }
							isBusy={ isSaving }
							disabled={ isLoading || isSaving }
						>
							Clear Selection
						</Button>
					</div>
				) }
				{ ! savedSelectedPageName && (
					<SearchForm
						selectedPageId={ selectedPageId }
						selectedPageName={ selectedPageName }
						setSelectedPageId={ setSelectedPageId }
						setSelectedPageName={ setSelectedPageName }
						saveSelectedPageId={ saveSelectedPageId }
						isLoadingOptions={ isLoading }
						isSaving={ isSaving }
					/>
				) }
				{ notice && ! isSaving && (
					<Snackbar onRemove={ () => setNotice( null ) }>
						{ notice.message }
					</Snackbar>
				) }
			</PanelBody>
		</Panel>
	);
}
