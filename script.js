document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    form.addEventListener('submit', (event) => {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!name || !email) {
            alert('Veuillez remplir tous les champs.');
            event.preventDefault();
        } else if (!validateEmail(email)) {
            alert('Veuillez entrer une adresse email valide.');
            event.preventDefault();
        }
    });
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
