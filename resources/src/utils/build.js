import {formatSEOTitle, ucfirst} from './string';

const STATUS_PUBLIC = 1;
const STATUS_UNLISTED = 2;
const STATUS_PRIVATE = 3;

function buildLinkParams(build) {
	return {
		id: build.ID,
		title: formatSEOTitle(build.title),
	};
}

function getSteamAvatar(avatarHash, size = 'medium') {
	return 'https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/' + avatarHash.substr(0, 2) + '/' + avatarHash + '_' + size + '.jpg';
}

function buildListSearch(options = {}) {
	// clear empty fields
	for (let key of Object.keys(options)) {
		if (!options[key] || Array.isArray(options[key]) && options[key].length === 0) {
			delete options[key];
		}
	}

	for (let key of ['map', 'gameMode', 'difficulty']) {
		if (options[key]) {
			if (Array.isArray(options[key])) {
				let values = [];
				for (let value of options[key]) {
					if (typeof value === 'string') {
						values.push(value);
					}
					else {
						values.push(value.value);
					}
				}

				options[key] = values;
			}

			options[key] = Array.isArray(options[key]) ? ucfirst(options[key]).join(',') : ucfirst(options[key]);
		}
	}

	return options;
}

export {
	STATUS_PUBLIC,
	STATUS_UNLISTED,
	STATUS_PRIVATE,
	buildLinkParams,
	buildListSearch,
	getSteamAvatar,
};