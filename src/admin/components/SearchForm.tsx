import {
	Button,
	SearchControl,
	SelectControl,
	Snackbar,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import type { FormEvent } from 'react';
import useSearch from '../hooks/useSearch';
import { NoticeState, SelectOption } from '../types/global';

type SearchFormProps = {
	selectedPageId: string;
	selectedPageName: string;
	setSelectedPageId: ( pageId: string ) => void;
	setSelectedPageName: ( pageName: string ) => void;
	saveSelectedPageId: ( pageId: string, pageName: string ) => Promise< void >;
	isLoadingOptions: boolean;
	isSaving: boolean;
};

export default function SearchForm( {
	selectedPageId,
	selectedPageName,
	setSelectedPageId,
	setSelectedPageName,
	saveSelectedPageId,
	isLoadingOptions,
	isSaving,
}: SearchFormProps ) {
	const [ search, setSearch ] = useState( '' );
	const { pages, isLoading } = useSearch( search );
	const [ notice, setNotice ] = useState< NoticeState | null >( null );
	const handleSubmit = async ( event: FormEvent< HTMLFormElement > ) => {
		event.preventDefault();

		try {
			await saveSelectedPageId( selectedPageId, selectedPageName );
			setNotice( {
				message: 'Page setting saved.',
				status: 'success',
			} );
		} catch {
			setNotice( {
				message: 'Could not save page setting. Please try again.',
				status: 'error',
			} );
		}
	};
	const searchPageOptions: SelectOption[] = pages.map( ( page ) => ( {
		label: page.title.rendered || `(No title) #${ page.id }`,
		value: String( page.id ),
	} ) );

	const preloadedSelectedPageOption =
		cnoHealthDashboardApiSettings.selectedPageOption ?? null;

	const shouldShowPreloadedSelectedOption =
		selectedPageId &&
		preloadedSelectedPageOption?.value === selectedPageId &&
		! searchPageOptions.some(
			( option ) => option.value === selectedPageId
		);

	const pageOptions: SelectOption[] = [
		{ label: 'Select a page', value: '' },
		...( shouldShowPreloadedSelectedOption && preloadedSelectedPageOption
			? [ preloadedSelectedPageOption ]
			: [] ),
		...searchPageOptions,
	];

	const handlePageChange = ( nextPageId: string ) => {
		setSelectedPageId( nextPageId );

		const selectedOption = pageOptions.find(
			( option ) => option.value === nextPageId
		);
		setSelectedPageName( selectedOption?.label ?? '' );
	};
	return (
		<form onSubmit={ handleSubmit }>
			<p>Select the page that the dashboard will be shown on</p>
			<SearchControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label="Find a page"
				value={ search }
				help="Start typing to search for a page"
				onChange={ setSearch }
			/>
			{ ( pages.length > 0 || preloadedSelectedPageOption ) && (
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label="Health Dashboard Page"
					value={ selectedPageId }
					options={ pageOptions }
					disabled={ isLoading || isLoadingOptions || isSaving }
					onChange={ handlePageChange }
				/>
			) }
			<Button
				style={ { marginBlockStart: '1rem' } }
				isPrimary
				type="submit"
				isBusy={ isSaving }
				disabled={ isLoading || isLoadingOptions || isSaving }
			>
				Save
			</Button>
			{ notice && ! isSaving && (
				<Snackbar onRemove={ () => setNotice( null ) }>
					{ notice.message }
				</Snackbar>
			) }
		</form>
	);
}
