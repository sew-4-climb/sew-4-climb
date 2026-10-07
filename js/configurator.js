let databaseStoffe = {};
let currentSection = 'superiore';

let userSelections = {
    superiore: null,
    fondo: null,
    lati: null,
    taschina: null
};

const sectionNames = {
    superiore: "1. Parte Superiore (Denim)",
    fondo: "2. Base / Fondo",
    lati: "3. Bande Laterali",
    taschina: "4. Taschina Frontale"
};

// Al caricamento, scarica le stoffe dal PHP
document.addEventListener("DOMContentLoaded", () => {
    fetchStoffe();
});

function fetchStoffe() {
    fetch('api/get_stoffe.php')
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                databaseStoffe = res.data;
                renderSwatches('superiore');
            } else {
                console.error("Errore caricamento stoffe:", res.error);
            }
        })
        .catch(err => console.error("Errore Fetch API:", err));
}

function selectSection(sectionId) {
    currentSection = sectionId;
    document.getElementById('currentSectionTitle').innerText = sectionNames[sectionId];
    renderSwatches(sectionId);
}

function renderSwatches(sectionId) {
    const container = document.getElementById('swatchContainer');
    container.innerHTML = '';

    const options = databaseStoffe[sectionId] || [];

    options.forEach(item => {
        const isSoldOut = item.quantita === 0;
        const isSelected = userSelections[sectionId] === item.pattern_id;

        const div = document.createElement('div');
        div.className = `swatch-item ${isSoldOut ? 'sold-out' : ''} ${isSelected ? 'selected' : ''}`;
        
        div.innerHTML = `
            <div class="swatch-img" style="background: ${item.colore_hex};"></div>
            <span class="swatch-label">${item.nome}</span>
            ${isSoldOut ? '<span class="sold-out-tag">SOLD OUT</span>' : ''}
        `;

        if (!isSoldOut) {
            div.onclick = () => applyFabric(sectionId, item.pattern_id);
        }

        container.appendChild(div);
    });
}

function applyFabric(sectionId, patternId) {
    userSelections[sectionId] = patternId;

    const element = document.getElementById(`part-${sectionId}`);
    
    // Rimuove l'effetto sfocato/fantasma
    element.classList.remove('ghost');
    
    // Applica il pattern/colore della stoffa
    element.setAttribute('fill', `url(#${patternId})`);

    renderSwatches(sectionId);
    updateProgress();
}

function updateProgress() {
    const selectedCount = Object.values(userSelections).filter(val => val !== null).length;
    const badge = document.getElementById('completionBadge');
    const btn = document.getElementById('btnAddToCart');

    badge.innerText = `${selectedCount} / 4 Scelti`;

    if (selectedCount === 4) {
        badge.className = "status-badge done";
        btn.disabled = false;
        btn.innerText = "Aggiungi al Carrello (Pezzo Unico)";
    } else {
        badge.className = "status-badge ok";
        btn.disabled = true;
        btn.innerText = `Seleziona ancora ${4 - selectedCount} parti`;
    }
}

function addToCart() {
    alert("Sacchettino aggiunto al carrello!\n\nCombinazione unica scelta:\n" + JSON.stringify(userSelections, null, 2));
}