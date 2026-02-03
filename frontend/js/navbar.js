// Navbar component
function initNavbar(activePage) {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    
    const navbarHTML = `
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.html">Mini CRM</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link ${activePage === 'contacts' ? 'active' : ''}" href="index.html">Contacts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link ${activePage === 'departments' ? 'active' : ''}" href="departments.html">Departments</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <span class="text-white me-3" id="userName">${user.name || 'Admin'}</span>
                        <button class="btn btn-outline-light btn-sm" onclick="logout()">Logout</button>
                    </div>
                </div>
            </div>
        </nav>
    `;
    
    // Insert navbar at the beginning of body
    document.body.insertAdjacentHTML('afterbegin', navbarHTML);
}
