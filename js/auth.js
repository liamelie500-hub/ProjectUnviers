// Gestion de l'authentification
const API_URL = 'http://localhost/univers/php/';

// Connexion
async function handleLogin(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch(`${API_URL}auth.php?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            window.location.href = 'dashboard.html';
        } else {
            alert(data.message || 'Erreur de connexion');
        }
    } catch (error) {
        alert('Une erreur est survenue');
        console.error(error);
    }
}

// Inscription
async function handleRegister(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
        alert('Les mots de passe ne correspondent pas');
        return;
    }

    if (password.length < 8) {
        alert('Le mot de passe doit contenir au moins 8 caractères');
        return;
    }

    try {
        const response = await fetch(`${API_URL}auth.php?action=register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
            window.location.href = 'login.html';
        } else {
            alert(data.message || 'Erreur lors de l\'inscription');
        }
    } catch (error) {
        alert('Une erreur est survenue');
        console.error(error);
    }
}

// Déconnexion
function handleLogout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

// Vérification de l'authentification
function checkAuth() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'login.html';
        return false;
    }
    return true;
}

// Chargement des informations utilisateur
function loadUserInfo() {
    const user = JSON.parse(localStorage.getItem('user'));
    if (user) {
        document.getElementById('username-display').textContent = user.username;
        document.getElementById('user-email').textContent = user.email;
    }
}