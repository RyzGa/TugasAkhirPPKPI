// Profile Dropdown Toggle Function
function toggleProfileDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    const menu = document.getElementById('profileDropdownMenu');
    menu.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.profile-dropdown');
    const menu = document.getElementById('profileDropdownMenu');
    if (dropdown && menu && !dropdown.contains(event.target)) {
        menu.classList.remove('show');
    }
});
