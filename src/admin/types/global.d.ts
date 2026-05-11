declare global {
	const cnoHealthDashboardApiSettings: {
		restBase: string;
		nonce: string;
		selectedPageOption: {
			value: string;
			label: string;
		} | null;
	};
}
export {};

export type PluginSettings = {
	page_id: number | null;
	meta_key: string;
};

export type SelectOption = {
	label: string;
	value: string;
};

export type NoticeState = {
	message: string;
	status: 'success' | 'error';
};
