// API.js - Handles all API calls to PHP backend

const API = {
    baseURL: '/api',

    // Helper method for all requests
    async request(endpoint, method = 'GET', data = null) {
        try {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include' // Include cookies for session
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.baseURL}${endpoint}`, options);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // Authentication
    auth: {
        login(username, password, role) {
            return API.request('/auth/login.php', 'POST', {
                username,
                password,
                role
            });
        },

        logout() {
            return API.request('/auth/logout.php', 'POST');
        },

        checkSession() {
            return API.request('/auth/check-session.php');
        }
    },

    // Students
    students: {
        getAll() {
            return API.request('/students/get.php');
        },

        getById(id) {
            return API.request(`/students/get.php?id=${id}`);
        },

        create(data) {
            return API.request('/students/create.php', 'POST', data);
        },

        update(id, data) {
            return API.request('/students/update.php', 'PUT', { id, ...data });
        },

        delete(id) {
            return API.request('/students/delete.php', 'DELETE', { id });
        },

        search(query) {
            return API.request(`/students/get.php?search=${query}`);
        }
    },

    // Staff
    staff: {
        getAll() {
            return API.request('/staff/get.php');
        },

        getById(id) {
            return API.request(`/staff/get.php?id=${id}`);
        },

        create(data) {
            return API.request('/staff/create.php', 'POST', data);
        },

        update(id, data) {
            return API.request('/staff/update.php', 'PUT', { id, ...data });
        },

        delete(id) {
            return API.request('/staff/delete.php', 'DELETE', { id });
        }
    },

    // Non-Teaching Staff
    nts: {
        getAll() {
            return API.request('/nts/get.php');
        },

        create(data) {
            return API.request('/nts/create.php', 'POST', data);
        },

        update(id, data) {
            return API.request('/nts/update.php', 'PUT', { id, ...data });
        },

        delete(id) {
            return API.request('/nts/delete.php', 'DELETE', { id });
        }
    },

    // Announcements
    announcements: {
        getAll() {
            return API.request('/announcements/get.php');
        },

        create(data) {
            return API.request('/announcements/create.php', 'POST', data);
        },

        update(id, data) {
            return API.request('/announcements/update.php', 'PUT', { id, ...data });
        },

        delete(id) {
            return API.request('/announcements/delete.php', 'DELETE', { id });
        }
    }
};