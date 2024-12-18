// Unified modal management
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function showGroupDetails(groupId) {
    fetch(`/FINAL PROJECT/view/get_group_details.php?group_id=${groupId}`)
        .then(response => response.text())
        .then(html => {
            const groupModalContent = document.getElementById('groupModalContent');
            const groupModal = document.getElementById('groupModal');
            
            if (groupModalContent && groupModal) {
                groupModalContent.innerHTML = html;
                groupModal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load group details');
        });
}

function showGuideDetails(contentId) {
    fetch(`/FINAL PROJECT/view/get_guide_details.php?content_id=${contentId}`)
        .then(response => response.text())
        .then(html => {
            const guideModalContent = document.getElementById('guideModalContent');
            const guideModal = document.getElementById('guideModal');
            
            if (guideModalContent && guideModal) {
                guideModalContent.innerHTML = html;
                guideModal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load guide details');
        });
}

// Close modals when clicking outside the modal
window.onclick = function(event) {
    const groupModal = document.getElementById('groupModal');
    const guideModal = document.getElementById('guideModal');
    
    if (groupModal && event.target == groupModal) {
        groupModal.style.display = 'none';
    }
    
    if (guideModal && event.target == guideModal) {
        guideModal.style.display = 'none';
    }
};