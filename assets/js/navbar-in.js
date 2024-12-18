document.addEventListener('DOMContentLoaded', function() {
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const mainNav = document.getElementById('mainNav');
    const accountBtn = document.getElementById('accountBtn');
    const accountDropdown = document.getElementById('accountDropdown');

    // Create View More Modal
    const viewMoreModal = document.createElement('div');
    viewMoreModal.classList.add('nav-modal');
    viewMoreModal.id = 'viewMoreModal';

    // Clone navigation links to modal
    const navLinks = mainNav.querySelectorAll('a');
    navLinks.forEach(link => {
        const modalLink = link.cloneNode(true);
        viewMoreModal.appendChild(modalLink);
    });

    // Create Account Modal
    const accountModal = document.createElement('div');
    accountModal.classList.add('account-modal');
    accountModal.id = 'accountModal';

    // Create account modal links
    const udashboardLink = document.createElement('a');
    udashboardLink.href = 'user-dashboard.php';
    udashboardLink.textContent = 'My Dashboard';

    const newsLink = document.createElement('a');
    newsLink.href = 'news_upload.php';
    newsLink.textContent = 'News Upload';

    const videoLink = document.createElement('a');
    videoLink.href = 'video_upload.php';
    videoLink.textContent = 'Video Upload';

    const memberLink = document.createElement('a');
    memberLink.href = 'member_guide_upload.php';
    memberLink.textContent = 'Member Guide Upload';

    const logoutLink = document.createElement('a');
    logoutLink.href = 'logout.php';
    logoutLink.textContent = 'Logout';

    accountModal.appendChild(udashboardLink);
    accountModal.appendChild(newsLink);
    accountModal.appendChild(videoLink);
    accountModal.appendChild(memberLink);
    accountModal.appendChild(logoutLink);

    // Append modals to body
    document.body.appendChild(viewMoreModal);
    document.body.appendChild(accountModal);

    // View More Button Toggle
    viewMoreBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        viewMoreModal.classList.toggle('show');
        accountModal.classList.remove('show');
    });

    // Account Button Toggle
    accountBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        accountModal.classList.toggle('show');
        viewMoreModal.classList.remove('show');
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (!accountBtn.contains(event.target)) {
            accountModal.classList.remove('show');
        }
        if (!viewMoreBtn.contains(event.target)) {
            viewMoreModal.classList.remove('show');
        }
    });
});