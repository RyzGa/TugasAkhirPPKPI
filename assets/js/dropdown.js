function toggleProfileDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    const menu = document.getElementById('profileDropdownMenu');
    menu.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.profile-dropdown');
    const menu = document.getElementById('profileDropdownMenu');
    if (dropdown && menu && !dropdown.contains(event.target)) {
        if (menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    }
});
