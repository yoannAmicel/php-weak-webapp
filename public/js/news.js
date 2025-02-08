/**************************************************/
/*              C O M M E N T S                   */  
/**************************************************/
// Bouton permettant l'affichage de toute la description & commentaires
function toggleContent(element) {
    const content = element.previousElementSibling;
    const commentsSection = element.nextElementSibling;
    if (content.classList.contains('truncate')) {
        content.classList.remove('truncate');
        element.textContent = '<- less info';
        commentsSection.style.display = 'block';
    } else {
        content.classList.add('truncate');
        element.textContent = 'More info ->';
        commentsSection.style.display = 'none';
    }
}

// Bouton permettant d'afficher le formulaire de commentaire
function toggleAddCommentForm(button) {
    const form = button.parentElement.nextElementSibling; 
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        button.textContent = 'Hide Comment Form';
    } else {
        form.style.display = 'none';
        button.textContent = 'Add a Comment';
    }
}

/**************************************************/
/*                  P O P U P                     */  
/**************************************************/
// Popup pour la confirmation de suppression d'une news
function openNewsPopup(newsId, newsTitle) {
    const popup = document.getElementById('delete-news-popup');
    const input = document.getElementById('delete-news-id');
    const message = document.getElementById('popup-news-message');
    input.value = newsId;
    message.textContent = `Are you sure you want to delete the news "${newsTitle}"?`;
    popup.classList.remove('hidden');
}

function closeNewsPopup() {
    const popup = document.getElementById('delete-news-popup');
    popup.classList.add('hidden');
}
