// Profile Dropdown Toggle Function
function toggleProfileDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    console.log('🔽 Toggling profile dropdown');
    const menu = document.getElementById('profileDropdownMenu');
    const isShowing = menu.classList.contains('show');
    menu.classList.toggle('show');
    console.log(`✅ Profile dropdown ${isShowing ? 'closed' : 'opened'}`);
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.profile-dropdown');
    const menu = document.getElementById('profileDropdownMenu');
    if (dropdown && menu && !dropdown.contains(event.target)) {
        if (menu.classList.contains('show')) {
            console.log('🔼 Closing profile dropdown (clicked outside)');
            menu.classList.remove('show');
        }
    }
});
