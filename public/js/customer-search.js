document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customer_search');
    const searchResults = document.getElementById('search_results');
    const selectedCustomerId = document.getElementById('selected_customer_id');

    if (!searchInput || !searchResults || !selectedCustomerId) return;

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/customers/search?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(customer => {
                            const div = document.createElement('div');
                            div.textContent = `${customer.name} (${customer.code})`;
                            div.addEventListener('click', () => {
                                searchInput.value = customer.name;
                                selectedCustomerId.value = customer.id;
                                searchResults.style.display = 'none';
                            });
                            searchResults.appendChild(div);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error searching customers:', error);
                    searchResults.style.display = 'none';
                });
        }, 300);
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});