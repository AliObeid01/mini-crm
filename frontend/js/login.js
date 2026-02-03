// Login page logic
redirectIfAuthenticated();

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('loginBtn');
    const alertBox = document.getElementById('alertBox');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging in...';
    alertBox.innerHTML = '';
    
    try {
        const response = await apiRequest('/auth/login', {
            method: 'POST',
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            localStorage.setItem('token', data.data.token);
            localStorage.setItem('user', JSON.stringify(data.data.user));
            
            window.location.href = 'index.html';
        } else {
            const errorMsg = data.message || 'Login failed';
            alertBox.innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
        }
    } catch (error) {
        alertBox.innerHTML = '<div class="alert alert-danger">Connection error. Please try again.</div>';
    }
    
    btn.disabled = false;
    btn.innerHTML = 'Login';
});
