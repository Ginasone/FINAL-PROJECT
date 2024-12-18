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
    const loginLink = document.createElement('a');
    loginLink.href = 'login.php';
    loginLink.textContent = 'Login';

    const signupLink = document.createElement('a');
    signupLink.href = 'signup.php';
    signupLink.textContent = 'Sign Up';

    accountModal.appendChild(loginLink);
    accountModal.appendChild(signupLink);

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