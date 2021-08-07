import { v4 as uuidv4 } from 'uuid';

/**
 * Generate universally unique identifier.
 *
 * @returns {string}
 */
const uuid = () => {
	return uuidv4();
};

/**
 * Map entry
 *
 * @param {Array}  synonyms Array of synonyms.
 * @param {string} id       The id, default generated by the application.
 * @returns {object}
 */
const mapEntry = (synonyms = [], id = '') => {
	return {
		id: id.length ? id : uuidv4(),
		synonyms,
		valid: true,
	};
};

/**
 * Reduce state to Solr spec.
 *
 * @param {object} state Current state.
 * @param {object[]} state.sets Array of synonym sets.
 * @param {object[]} state.alternatives Array of alternative sets.
 * @returns {string}
 */
const reduceStateToSolr = ({ sets, alternatives }) => {
	const synonymsList = [];

	// Handle sets.
	synonymsList.push('# Defined sets ( equivalent synonyms).');
	synonymsList.push(...sets.map(({ synonyms }) => synonyms.map(({ value }) => value).join(', ')));

	// Handle alternatives.
	synonymsList.push('\r');
	synonymsList.push('# Defined alternatives (explicit mappings).');
	synonymsList.push(
		...alternatives.map((alternative) =>
			alternative.synonyms.find((item) => item.primary && item.value.length)
				? alternative.synonyms
						.find((item) => item.primary)
						.value.concat(' => ')
						.concat(
							alternative.synonyms
								.filter((i) => !i.primary)
								.map(({ value }) => value)
								.join(', '),
						)
				: false,
		),
	);

	return synonymsList.filter(Boolean).join('\n');
};

/**
 * Reduce Solr text file to State.
 *
 * @param {string} solr A string in the Solr parseable synonym format.
 * @param {object} currentState  The current sate.
 * @returns {object}
 */
const reduceSolrToState = (solr, currentState) => {
	/**
	 * Format token.
	 *
	 * @param {string} value The value.
	 * @param {boolean} primary Whether it's a primary.
	 * @returns {object}
	 */
	const formatToken = (value, primary = false) => {
		return {
			label: value,
			value,
			primary,
		};
	};

	return {
		...currentState,
		...solr.split(/\r?\n/).reduce(
			(newState, line) => {
				if (line.indexOf('#') === 0 || !line.trim().length) {
					return newState;
				}

				if (line.indexOf('=>') !== -1) {
					const parts = line.split('=>');
					return {
						...newState,
						alternatives: [
							...newState.alternatives,
							mapEntry([
								formatToken(parts[0].trim(), true),
								...parts[1].split(',').map((token) => formatToken(token.trim())),
							]),
						],
					};
				}

				return {
					...newState,
					sets: [
						...newState.sets,
						mapEntry([...line.split(',').map((token) => formatToken(token.trim()))]),
					],
				};
			},
			{ alternatives: [], sets: [] },
		),
	};
};

export { reduceStateToSolr, reduceSolrToState, uuid, mapEntry };