// auth.js - Handles authentication flow

class Auth {
    constructor() {
        this.user = null;
        this.isAuthenticated = false;
        this.checkSession();
    }

    async checkSession() {
        try {
            const response = await API.auth.checkSession();
            if (response.success) {
                this.user = response.user;
                this.isAuthenticated = true;
                this.redirect();
            }
        } catch (error) {
            console.log('No active session');
        }
    }

    async login(username, password, role) {
        try {
            const response = await API.auth.login(username, password, role);
            
            if (response.success) {
                this.user = response.user;
                this.isAuthenticated = true;
                this.redirect();
                return response;
            } else {
                throw new Error(response.message || 'Login failed');
            }
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    async logout() {
        try {
            await API.auth.logout();
            this.user = null;
            this.isAuthenticated = false;
            window.location.href = '/login.html';
        } catch (error) {
            console.error('Logout error:', error);
        }
    }

    redirect() {
        if (!this.user) return;

        const dashboards = {
            'admin': '/pages/admin/dashboard.html',
            'teacher': '/pages/teacher/dashboard.html',
            'student': '/pages/student/dashboard.html',
            'nts': '/pages/nts/dashboard.html'
        };

        const dashboard = dashboards[this.user.role];
        if (dashboard) {
            window.location.href = dashboard;
        }
    }

    getUser() {
        return this.user;
    }
}

// Initialize auth on page load
const auth = new Auth();

// Handle login form submission
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            const errorDiv = document.getElementById('errorMessage');

            if (!role) {
                errorDiv.textContent = 'Please select a role';
                errorDiv.style.display = 'block';
                return;
            }

            const result = await auth.login(username, password, role);

            if (!result.success) {
                errorDiv.textContent = result.message;
                errorDiv.style.display = 'block';
            }
        });
    }
});