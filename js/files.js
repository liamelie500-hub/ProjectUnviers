const API_URL = 'http://localhost/univers/php/';

// Charger les fichiers
async function loadFiles() {
    try {
        const response = await fetch(`${API_URL}files.php?action=getAll`);
        const data = await response.json();
        
        if (data.success) {
            displayFiles(data.files);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Charger les derniers fichiers
async function loadLatestFiles() {
    try {
        const response = await fetch(`${API_URL}files.php?action=getLatest&limit=6`);
        const data = await response.json();
        
        if (data.success) {
            displayFiles(data.files, 'latest-files');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Afficher les fichiers
function displayFiles(files, containerId = 'files-grid') {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = files.map(file => `
        <div class="file-card" onclick="viewFile(${file.id})">
            <img src="${file.image || 'assets/images/default-file.jpg'}" alt="${file.name}" class="file-card-image">
            <div class="file-card-content">
                <h3 class="file-card-title">${file.name}</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">${file.description}</p>
                <div class="file-card-meta">
                    <span>📦 ${file.version}</span>
                    <span>💾 ${file.size}</span>
                    <span>📅 ${file.updated_at}</span>
                </div>
                <button class="btn btn-primary" style="margin-top: 1rem; width: 100%;" onclick="event.stopPropagation(); downloadFile(${file.id})">
                    Télécharger
                </button>
            </div>
        </div>
    `).join('');
}

// Voir les détails d'un fichier
function viewFile(fileId) {
    window.location.href = `file-detail.html?id=${fileId}`;
}

// Charger les détails d'un fichier
async function loadFileDetail() {
    const params = new URLSearchParams(window.location.search);
    const fileId = params.get('id');
    
    if (!fileId) {
        window.location.href = 'files.html';
        return;
    }

    try {
        const response = await fetch(`${API_URL}files.php?action=getOne&id=${fileId}`);
        const data = await response.json();
        
        if (data.success) {
            const file = data.file;
            const container = document.getElementById('file-detail');
            container.innerHTML = `
                <div class="glass" style="padding: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <img src="${file.image || 'assets/images/default-file.jpg'}" alt="${file.name}" style="width: 100%; border-radius: var(--radius);">
                    </div>
                    <div>
                        <h1 style="margin-bottom: 1rem;">${file.name}</h1>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">${file.full_description || file.description}</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1.5rem 0;">
                            <div class="glass" style="padding: 1rem;">
                                <span style="color: var(--text-secondary);">Version</span>
                                <div style="font-size: 1.2rem; font-weight: 600;">${file.version}</div>
                            </div>
                            <div class="glass" style="padding: 1rem;">
                                <span style="color: var(--text-secondary);">Taille</span>
                                <div style="font-size: 1.2rem; font-weight: 600;">${file.size}</div>
                            </div>
                            <div class="glass" style="padding: 1rem;">
                                <span style="color: var(--text-secondary);">Date</span>
                                <div style="font-size: 1.2rem; font-weight: 600;">${file.updated_at}</div>
                            </div>
                            <div class="glass" style="padding: 1rem;">
                                <span style="color: var(--text-secondary);">Téléchargements</span>
                                <div style="font-size: 1.2rem; font-weight: 600;">${file.downloads || 0}</div>
                            </div>
                        </div>
                        <div style="margin: 1.5rem 0;">
                            <h3>Nouveautés de cette version</h3>
                            <p style="color: var(--text-secondary);">${file.changelog || 'Aucune information disponible'}</p>
                        </div>
                        <button class="btn btn-primary" style="width: 100%;" onclick="downloadFile(${file.id})">
                            ⬇️ Télécharger ${file.name}
                        </button>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Télécharger un fichier
async function downloadFile(fileId) {
    const token = localStorage.getItem('token');
    if (!token) {
        alert('Veuillez vous connecter pour télécharger');
        return;
    }

    try {
        const response = await fetch(`${API_URL}files.php?action=download&id=${fileId}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = response.headers.get('Content-Disposition')?.split('filename=')[1] || 'fichier';
            a.click();
            window.URL.revokeObjectURL(url);
        } else {
            alert('Erreur lors du téléchargement');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Filtrer les fichiers
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');

    if (searchInput) {
        searchInput.addEventListener('input', filterFiles);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterFiles);
    }

    // Charger les fichiers selon la page
    if (window.location.pathname.includes('file-detail.html')) {
        loadFileDetail();
    } else if (window.location.pathname.includes('dashboard.html')) {
        loadLatestFiles();
    } else if (window.location.pathname.includes('files.html')) {
        loadFiles();
    }
});

function filterFiles() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const cards = document.querySelectorAll('.file-card');
    
    cards.forEach(card => {
        const title = card.querySelector('.file-card-title').textContent.toLowerCase();
        const description = card.querySelector('p').textContent.toLowerCase();
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        const matchesCategory = category === 'all' || card.dataset.category === category;
        
        card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
    });
}