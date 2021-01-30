import axios from 'axios';
import {hideAjaxLoader, showAjaxLoader} from '../store';

const LIKE = 1;
const DISLIKE = -1;

function like(objectType, object, likeType = LIKE) {
	showAjaxLoader();

	return axios
		.post('/like/', {
			objectID: object.ID,
			objectType,
			likeType: likeType === LIKE ? 'like' : 'dislike',
		})
		.then(({ data }) => {
			let newLikeValue = 0;
			if (typeof data[LIKE] !== 'undefined') {
				object.likes += data[LIKE];
				if (data[LIKE] > 0) {
					newLikeValue = LIKE;
				}
			}
			if (typeof data[DISLIKE] !== 'undefined') {
				object.dislikes += data[DISLIKE];
				if (data[DISLIKE] > 0) {
					newLikeValue = DISLIKE;
				}
			}

			object.likeValue = newLikeValue;
		})
		.catch(() => {
			// TODO error handling
		})
		.finally(() => {
			hideAjaxLoader();
		});
}

export {
	like,
	LIKE,
	DISLIKE,
};