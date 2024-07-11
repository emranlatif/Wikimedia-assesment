'use strict';
// NOTE: Please do not use any third-party libraries to implement the
	// following as we want to keep the JS payload as small as possible. You may
	// use ES6. There is no need to support IE11.
	//
	// TODO A: Improve the readability of this file through refactoring and
	// documentation. Make any changes you think are necessary.
	//
	// TODO B: When typing in the "title" field, we want to auto-complete based on
	// article titles that already exist. You may use the
	// api.php?prefixsearch={search} endpoint for auto-completion. To avoid
	// hitting the server endpoint excessively, please also add JavaScript code
	// that ensures at least 200ms has passed between requests. Check the
	// `design-spec/auto-complete-hover.png` file for the design spec.
	// Also, you don't need to make the autocomplete list disappear when the input
	// has lost focus in this TODO. That will be handled as part of TODO D.
	//
	// TODO C: When the user selects an item from the auto-complete list, we want
	// the textarea to populate with that article's contents. You may use the
	// api.php?title={title} endpoint to get the article's contents. Check the
	// `design-spec/auto-complete-select.png` file for the design spec.
	//
	// TODO D: The autocomplete list should only be shown when the input receives
	// focus. The list should be hidden after the user selects an item from the
	// list or after the input loses focus.
	//
	// TODO E: Figure out how to make multiple requests to the server as the user
	// scrolls through the autocomplete list.
	//
	// TODO F: Add error-handling requirements, such as displaying error messages
	// to the user when API requests fail and provide a graceful degradation of
	// functionality.
(function () {
	function getFormButtonToWork() {
		const submitButton = document.querySelector('.submit-button');
		const form = document.querySelector('form');

		// Make form submit button work when submit is clicked.
		submitButton.addEventListener('click', (e) => {
			e.preventDefault();
			form.submit();
		});
	}

	// Debounce function to limit the rate of API calls
	function debounce(func, wait) {
		let timeout;
		return function (...args) {
			clearTimeout(timeout);
			timeout = setTimeout(() => func.apply(this, args), wait);
		};
	}

	// Fetch auto-complete suggestions
	async function fetchSuggestions(query, offset = 0) {
		try {
			const response = await fetch(`api.php?prefixsearch=${query}&offset=${offset}`);
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
			const data = await response.json();
			return data.content;
		} catch (error) {
			console.error('Error fetching suggestions:', error);
			displayErrorMessage('Error fetching suggestions. Please try again later.');
			return [];
		}
	}

	// Fetch article content
	async function fetchArticleContent(title) {
		try {
			const response = await fetch(`api.php?title=${title}`);
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
			const data = await response.json();
			return data.content;
		} catch (error) {
			console.error('Error fetching article content:', error);
			displayErrorMessage('Error fetching article content. Please try again later.');
			return '';
		}
	}

	// Display error messages
	function displayErrorMessage(message) {
		const errorContainer = document.createElement('div');
		errorContainer.textContent = message;
		errorContainer.style.color = 'red';
		errorContainer.classList.add('error-message');
		document.body.appendChild(errorContainer);
		setTimeout(() => {
			errorContainer.remove();
		}, 5000);
	}

	// Handle input event for auto-complete
	async function handleInputEvent(event) {
		const query = event.target.value;
		if (query.length > 0) {
			const suggestions = await fetchSuggestions(query);
			displaySuggestions(suggestions, event.target, query);
		} else {
			clearSuggestions();
		}
	}

	// Display suggestions
	function displaySuggestions(suggestions, inputElement, query) {
		clearSuggestions();
		const suggestionsList = document.createElement('ul');
		const inputRect = inputElement.getBoundingClientRect();
		suggestionsList.style.position = 'absolute';
		suggestionsList.style.top = `${inputElement.offsetHeight}px`;
		suggestionsList.style.width = `${inputRect.width}px`;
		suggestionsList.style.zIndex = 10;
		suggestionsList.style.padding = 0;
		suggestionsList.style.listStyle = 'none';
		suggestionsList.style.backgroundColor = '#4f77c9';
		suggestionsList.classList.add('suggestions-list');

		suggestions.forEach(suggestion => {
			const listItem = document.createElement('li');
			listItem.textContent = suggestion;
			listItem.style.cursor = 'pointer';
			listItem.style.paddingBottom = '5px';
			listItem.style.paddingTop = '5px';
			listItem.style.borderBottom = '1px solid';

			// Add hover effect using JavaScript
			listItem.addEventListener('mouseover', () => {
				listItem.style.backgroundColor = '#d6e0f5'; // Change to the desired hover color
			});
			listItem.addEventListener('mouseout', () => {
				listItem.style.backgroundColor = ''; // Reset to original background color
			});

			// Add mouse click event listener
			listItem.addEventListener('mousedown', (event) => {
				if (event.button === 0) { // Left mouse button
					handleSuggestionClick(suggestion, inputElement);
				}
			});

			suggestionsList.appendChild(listItem);
		});

		// Add scroll event listener to the suggestions list
		suggestionsList.addEventListener('scroll', async () => {
			if (suggestionsList.scrollTop + suggestionsList.clientHeight >= suggestionsList.scrollHeight) {
				const additionalSuggestions = await fetchSuggestions(query, suggestions.length);
				additionalSuggestions.forEach(suggestion => {
					const listItem = document.createElement('li');
					listItem.textContent = suggestion;
					listItem.style.cursor = 'pointer';
					listItem.style.paddingBottom = '5px';
					listItem.style.paddingTop = '5px';
					listItem.style.borderBottom = '1px solid';

					// Add hover effect using JavaScript
					listItem.addEventListener('mouseover', () => {
						listItem.style.backgroundColor = '#d6e0f5'; // Change to the desired hover color
					});
					listItem.addEventListener('mouseout', () => {
						listItem.style.backgroundColor = ''; // Reset to original background color
					});

					// Add mouse click event listener
					listItem.addEventListener('mousedown', (event) => {
						if (event.button === 0) { // Left mouse button
							handleSuggestionClick(suggestion, inputElement);
						}
					});

					suggestionsList.appendChild(listItem);
				});
			}
		});

		// Append suggestionsList after the input element
		inputElement.parentNode.insertBefore(suggestionsList, inputElement.nextSibling);
	}

	// Clear suggestions
	function clearSuggestions() {
		const existingSuggestions = document.querySelector('.suggestions-list');
		if (existingSuggestions) {
			existingSuggestions.remove();
		}
	}

	// Handle suggestion click
	async function handleSuggestionClick(suggestion, inputElement) {
		inputElement.value = suggestion;
		clearSuggestions();
		const articleContent = await fetchArticleContent(suggestion);
		document.querySelector('textarea[name="body"]').value = articleContent;
	}

	// Attach event listeners
	function attachEventListeners() {
		const titleInput = document.querySelector('input[name="title"]');
		titleInput.addEventListener('input', debounce(handleInputEvent, 200));
		titleInput.addEventListener('focus', () => {
			if (titleInput.value.length > 0) {
				handleInputEvent({ target: titleInput });
			}
		});
		titleInput.addEventListener('blur', () => {
			setTimeout(clearSuggestions, 100); // Delay to allow click event on suggestion to trigger
		});
	}

	// Wait for DOM content to load
	document.addEventListener('DOMContentLoaded', () => {
		attachEventListeners();
	});
})();
