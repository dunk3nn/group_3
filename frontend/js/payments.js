const API_URL = `http://${window.location.hostname}:3000/api`;

const paymentForm = document.getElementById('paymentForm');
const paymentsTable = document.getElementById('paymentsTable');

// LOAD PAYMENTS
async function fetchPayments() {
    try {
        const response = await fetch(`${API_URL}/payments`);

        if (!response.ok) {
            throw new Error('Failed to load payments.');
        }

        const payments = await response.json();

        paymentsTable.innerHTML = '';

        if (!payments.length) {
            paymentsTable.innerHTML = `
                <tr>
                    <td colspan="6">No payment records found.</td>
                </tr>
            `;
            return;
        }

        payments.forEach(payment => {
            const row = document.createElement('tr');

            [
                payment.id,
                payment.entry_id,
                payment.fee_rule_id,
                payment.penalty_id ?? 'N/A',
                Number(payment.amount_paid).toFixed(2),
                payment.payment_method
            ].forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.appendChild(cell);
            });

            paymentsTable.appendChild(row);
        });

    } catch (error) {
        console.error('Payment loading error:', error);
        paymentsTable.innerHTML = `
            <tr>
                <td colspan="6">Unable to load payments. Check the backend.</td>
            </tr>
        `;
    }
}

// PROCESS PAYMENT
paymentForm.addEventListener('submit', async function (event) {
    event.preventDefault();

    const paymentData = {
        entry_id: document.getElementById('entry_id').value,
        fee_rule_id: document.getElementById('fee_rule_id').value,
        penalty_id: document.getElementById('penalty_id').value || null,
        amount_paid: document.getElementById('amount_paid').value,
        payment_method: document.getElementById('payment_method').value
    };

    try {
        const response = await fetch(`${API_URL}/payments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Payment processing failed.');
        }

        alert('Payment recorded successfully!');

        paymentForm.reset();
        fetchPayments();

    } catch (error) {
        console.error('Payment processing error:', error);
        alert(error.message);
    }
});

// INITIAL LOAD
fetchPayments();