document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('fast-order-form');
    const totalValueElement = document.getElementById('total-value');

    // Funktion zur Berechnung der Gesamtsumme
    const calculateTotal = () => {
        let total = 0;

        // Iteriere durch alle Produktreihen
        const rows = form.querySelectorAll('.product-row');
        rows.forEach(row => {
            const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
            total += quantity * price;
        });

        // Aktualisiere die Gesamtsumme
        totalValueElement.textContent = total.toFixed(2);
    };

    // Funktion zum Abrufen des Preises vom Server
    const fetchPrice = (productNumber, callback) => {
        fetch(`/fast-order/get-price?productNumber=${productNumber}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch price');
                }
                return response.json();
            })
            .then(data => {
                if (data.price) {
                    callback(data.price[0].gross);
                }
            })
            .catch(error => console.error('Error fetching price:', error));
    };

    if(form){

        // Ereignislistener für Änderungen der Produktnummer
        form.addEventListener('input', (event) => {
            if (event.target.matches('input[name*="[productNumber]"]')) {
                const row = event.target.closest('.product-row');
                const productNumber = event.target.value;

                fetchPrice(productNumber, (price) => {
                    const priceInput = row.querySelector('input[name*="[price]"]');
                    priceInput.value = price.toFixed(2);
                    calculateTotal();
                });
            }

            if (event.target.matches('input[name*="[quantity]"]')) {
                calculateTotal();
            }
        });

        // Initiale Berechnung
        calculateTotal();
    }
    
});

