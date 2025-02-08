// Forcer la mise en majuscules sur le champ "Name" lors de la soumission
function forceUpperCase(event) {
    const nameField = document.getElementById('name');
    nameField.value = nameField.value.toUpperCase();
}

// Forcer la mise en majuscules sur le champ "Name" lors de la soumission
function forceUpperCase(event) {
    const nameField = document.getElementById('name');
    nameField.value = nameField.value.toUpperCase();
}

function openPopup(softwareId, softwareName) {
    const popup = document.getElementById('delete-popup');
    const input = document.getElementById('delete-software-id');
    const message = document.getElementById('popup-message');
    
    // Met à jour l'ID et le message
    input.value = softwareId;
    message.textContent = `Are you sure you want to delete the software "${softwareName}"?`;
    
    popup.classList.remove('hidden');
}

function closePopup() {
    const popup = document.getElementById('delete-popup');
    popup.classList.add('hidden');
}