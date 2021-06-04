<template>
	<div class="container">
		<template v-for="changelog in changelogs">
			<h3 :key="'header-' + changelog.version">
				{{changelog.version === 'Unreleased' ? 'Current' : changelog.version}}
				<template v-if="changelog.date">
					({{changelog.date}})
				</template>
			</h3>
			<ul :key="'list-' + changelog.version">
				<template v-for="type in changelog.changeTypes">
					<li v-for="entry in type.entries" :key="type.type + '-' + entry">
						<span class="badge" :class="classes[type.type]">{{type.type}}</span> {{entry}}
					</li>
				</template>
			</ul>
		</template>
	</div>
</template>

<script>
import axios from 'axios';
import {hidePageLoader, showPageLoader} from '../store';

export default {
	name: 'ChangelogView',
	data() {
		return {
			changelogs: [],
			classes: {
				Added: 'badge-success',
				Fixed: 'badge-danger',
				Changed: 'badge-warning',
				Removed: 'badge-danger',
			},
		};
	},
	created() {
		showPageLoader();
		axios
			.get('/changelogs')
			.then(({ data }) => {
				console.log(data);
				this.changelogs = data;
			})
			.finally(hidePageLoader);
	},
};
</script>