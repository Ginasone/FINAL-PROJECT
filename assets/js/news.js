function toggleNewsDetails(contentId) {
    const detailsElement = document.getElementById('news-details-' + contentId);
    const readMoreElement = document.getElementById('read-more-' + contentId);
    
    if (detailsElement.style.display === 'block') {
        detailsElement.style.display = 'none';
        readMoreElement.textContent = 'Read More';
    } else {
        detailsElement.style.display = 'block';
        readMoreElement.textContent = 'Close';
    }
}