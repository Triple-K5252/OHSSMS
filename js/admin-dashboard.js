// Admin Dashboard Logic - Handles all interactions without page reloads

class AdminDashboard {
    constructor() {
        this.currentPage = 'dashboard';
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadDashboardData();
        await this.loadSubjects();
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-link:not(.logout)').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.target.dataset.page;
                this.showPage(page);
            });
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await this.logout();
        });

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Forms
        document.getElementById('studentForm').addEventListener('submit', (e) => this.registerStudent(e));
        document.getElementById('teacherForm').addEventListener('submit', (e) => this.registerTeacher(e));
        document.getElementById('ntsForm').addEventListener('submit', (e) => this.registerNTS(e));
        document.getElementById('announcementForm').addEventListener('submit', (e) => this.createAnnouncement(e));
        document.getElementById('subjectForm').addEventListener('submit', (e) => this.addSubject(e));
    }

    showPage(pageId) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });

        // Show selected page
        document.getElementById(pageId).classList.add('active');

        // Update nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-page="${pageId}"]`).classList.add('active');

        this.currentPage = pageId;

        // Load page specific data
        if (pageId === 'manage-students') this.loadStudents();
        if (pageId === 'manage-staff') this.loadStaff();
        if (pageId === 'announcements') this.loadAnnouncements();
    }

    switchTab(tabId) {
        // Hide all tab content
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabId).classList.add('active');

        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    // ===== REGISTRATION FUNCTIONS =====

    async registerStudent(e) {
        e.preventDefault();

        const formData = {
            type: 'student',
            admission_no: document.getElementById('studentAdmissionNo').value,
            first_name: document.getElementById('studentFirstName').value,
            last_name: document.getElementById('studentLastName').value,
            dob: document.getElementById('studentDOB').value,
            form: document.getElementById('studentForm').value,
            stream: document.getElementById('studentStream').value,
            guardian_name: document.getElementById('guardianName').value,
            guardian_contact: document.getElementById('guardianContact').value
        };

        await this.submitRegistration(formData, 'studentForm');
    }

    async registerTeacher(e) {
        e.preventDefault();

        const formData = {
            type: 'teacher',
            first_name: document.getElementById('teacherFirstName').value,
            last_name: document.getElementById('teacherLastName').value,
            id_no: document.getElementById('teacherID').value,
            dob: document.getElementById('teacherDOB').value,
            password: document.getElementById('teacherPassword').value,
            subject_id: document.getElementById('teacherSubject').value,
            is_class_teacher: document.getElementById('isClassTeacher').checked ? 1 : 0
        };

        await this.submitRegistration(formData, 'teacherForm');
    }

    async registerNTS(e) {
        e.preventDefault();

        const formData = {
            type: 'nts',
            first_name: document.getElementById('ntsFirstName').value,
            last_name: document.getElementById('ntsLastName').value,
            id_no: document.getElementById('ntsID').value,
            password: document.getElementById('ntsPassword').value,
            position: document.getElementById('ntsPosition').value
        };

        await this.submitRegistration(formData, 'ntsForm');
    }

    async submitRegistration(data, formId) {
        try {
            const response = await API.request('/auth/register.php', 'POST', data);

            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message || `${data.type.charAt(0).toUpperCase() + data.type.slice(1)} registered successfully!`,
                    icon: 'success',
                    confirmButtonColor: '#27ae60'
                });

                // Reset form
                document.getElementById(formId).reset();

                // Reload data if on manage page
                if (this.currentPage === 'manage-students') this.loadStudents();
                if (this.currentPage === 'manage-staff') this.loadStaff();
            } else {
                throw new Error(response.message || 'Registration failed');
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#e74c3c'
            });
        }
    }

    // ===== ANNOUNCEMENTS =====

    async createAnnouncement(e) {
        e.preventDefault();

        const formData = {
            title: document.getElementById('announcementTitle').value,
            message: document.getElementById('announcementMessage').value,
            target: document.getElementById('announcementTarget').value,
            form: document.getElementById('announcementForm').value || null
        };

        try {
            const response = await API.request('/announcements/create.php', 'POST', formData);

            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Announcement posted successfully!',
                    icon: 'success',
                    confirmButtonColor: '#27ae60'
                });

                document.getElementById('announcementForm').reset();
                this.loadAnnouncements();
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error'
            });
        }
    }

    async loadAnnouncements() {
        try {
            const response = await API.request('/announcements/get.php');
            const container = document.getElementById('announcementsContainer');

            if (response.success && response.announcements.length > 0) {
                container.innerHTML = response.announcements.map(a => `
                    <div class="announcement-card">
                        <h3>${escapeHtml(a.title)}</h3>
                        <p>${escapeHtml(a.message)}</p>
                        <div class="announcement-meta">
                            Posted: ${new Date(a.created_at).toLocaleDateString()}
                            ${a.target ? `| Target: ${a.target}` : ''}
                        </div>
                        <div style="margin-top: 10px;">
                            <button class="btn btn-danger" style="padding: 8px 16px; font-size: 0.9rem;" 
                                    onclick="dashboard.deleteAnnouncement(${a.announcement_id})">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="no-data">No announcements yet.</p>';
            }
        } catch (error) {
            console.error('Error loading announcements:', error);
        }
    }

    async deleteAnnouncement(id) {
        const confirmed = await Swal.fire({
            title: 'Delete Announcement?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        });

        if (confirmed.isConfirmed) {
            try {
                const response = await API.request('/announcements/delete.php', 'DELETE', { id });

                if (response.success) {
                    Swal.fire('Deleted!', 'Announcement deleted successfully.', 'success');
                    this.loadAnnouncements();
                }
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    }

    // ===== SUBJECTS =====

    async loadSubjects() {
        try {
            const response = await API.request('/subjects/get.php');

            if (response.success) {
                // Populate teacher subject dropdown
                const select = document.getElementById('teacherSubject');
                select.innerHTML = '<option value="">Select Subject</option>';
                response.subjects.forEach(subject => {
                    select.innerHTML += `<option value="${subject.subject_id}">${escapeHtml(subject.subject_name)}</option>`;
                });

                // Display subjects
                this.displaySubjects(response.subjects);
            }
        } catch (error) {
            console.error('Error loading subjects:', error);
        }
    }

    displaySubjects(subjects) {
        const container = document.getElementById('subjectsContainer');

        if (subjects.length > 0) {
            container.innerHTML = subjects.map(subject => `
                <div class="subject-item">
                    <span>${escapeHtml(subject.subject_name)}</span>
                    <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;"
                            onclick="dashboard.deleteSubject(${subject.subject_id})">Delete</button>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="no-data">No subjects added yet.</p>';
        }
    }

    async addSubject(e) {
        e.preventDefault();

        const formData = {
            subject_name: document.getElementById('subjectName').value
        };

        try {
            const response = await API.request('/subjects/create.php', 'POST', formData);

            if (response.success) {
                Swal.fire('Success!', 'Subject added successfully!', 'success');
                document.getElementById('subjectForm').reset();
                await this.loadSubjects();
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            Swal.fire('Error!', error.message, 'error');
        }
    }

    async deleteSubject(id) {
        const confirmed = await Swal.fire({
            title: 'Delete Subject?',
            text: 'This will remove the subject from the system.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, delete it!'
        });

        if (confirmed.isConfirmed) {
            try {
                const response = await API.request('/subjects/delete.php', 'DELETE', { id });

                if (response.success) {
                    Swal.fire('Deleted!', 'Subject deleted successfully.', 'success');
                    await this.loadSubjects();
                }
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    }

    // ===== STUDENTS =====

    async loadStudents() {
        try {
            const response = await API.request('/students/get.php');

            if (response.success) {
                this.displayStudents(response.students);
            }
        } catch (error) {
            console.error('Error loading students:', error);
        }
    }

    displayStudents(students) {
        const tbody = document.querySelector('#studentsTable tbody');
        const noMessage = document.getElementById('noStudentsMessage');

        if (students.length > 0) {
            tbody.innerHTML = students.map(student => `
                <tr>
                    <td>${escapeHtml(student.admission_no)}</td>
                    <td>${escapeHtml(student.first_name + ' ' + student.last_name)}</td>
                    <td>Form ${student.form}</td>
                    <td>${escapeHtml(student.stream)}</td>
                    <td>${escapeHtml(student.guardian_contact)}</td>
                    <td>
                        <button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;"
                                onclick="dashboard.editStudent(${student.student_id})">Edit</button>
                        <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;"
                                onclick="dashboard.deleteStudent(${student.student_id})">Delete</button>
                    </td>
                </tr>
            `).join('');
            noMessage.style.display = 'none';
        } else {
            tbody.innerHTML = '';
            noMessage.style.display = 'block';
        }
    }

    async searchStudents() {
        const query = document.getElementById('studentSearch').value;

        if (!query.trim()) {
            this.loadStudents();
            return;
        }

        try {
            const response = await API.request(`/students/get.php?search=${encodeURIComponent(query)}`);

            if (response.success) {
                this.displayStudents(response.students);
            }
        } catch (error) {
            console.error('Error searching students:', error);
        }
    }

    async deleteStudent(id) {
        const confirmed = await Swal.fire({
            title: 'Delete Student?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, delete it!'
        });

        if (confirmed.isConfirmed) {
            try {
                const response = await API.request('/students/delete.php', 'DELETE', { id });

                if (response.success) {
                    Swal.fire('Deleted!', 'Student deleted successfully.', 'success');
                    this.loadStudents();
                }
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    }

    async editStudent(id) {
        alert('Edit functionality coming soon. You can delete and re-register for now.');
    }

    // ===== STAFF =====

    async loadStaff() {
        try {
            const teachersRes = await API.request('/staff/teachers.php');
            const ntsRes = await API.request('/staff/nts.php');

            if (teachersRes.success) {
                this.displayTeachers(teachersRes.staff);
            }

            if (ntsRes.success) {
                this.displayNTS(ntsRes.staff);
            }
        } catch (error) {
            console.error('Error loading staff:', error);
        }
    }

    displayTeachers(teachers) {
        const tbody = document.querySelector('#teachersTable tbody');
        const noMessage = document.getElementById('noTeachersMessage');

        if (teachers.length > 0) {
            tbody.innerHTML = teachers.map(teacher => `
                <tr>
                    <td>${escapeHtml(teacher.id_no)}</td>
                    <td>${escapeHtml(teacher.first_name + ' ' + teacher.last_name)}</td>
                    <td>${escapeHtml(teacher.subject_name || 'N/A')}</td>
                    <td><span class="badge">${teacher.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>
                        <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;"
                                onclick="dashboard.deleteTeacher(${teacher.staff_id})">Delete</button>
                    </td>
                </tr>
            `).join('');
            noMessage.style.display = 'none';
        } else {
            tbody.innerHTML = '';
            noMessage.style.display = 'block';
        }
    }

    displayNTS(staff) {
        const tbody = document.querySelector('#ntsTable tbody');
        const noMessage = document.getElementById('noNTSMessage');

        if (staff.length > 0) {
            tbody.innerHTML = staff.map(person => `
                <tr>
                    <td>${escapeHtml(person.id_no)}</td>
                    <td>${escapeHtml(person.first_name + ' ' + person.last_name)}</td>
                    <td>${escapeHtml(person.position || 'N/A')}</td>
                    <td><span class="badge">${person.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>
                        <button class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;"
                                onclick="dashboard.deleteNTS(${person.nts_id})">Delete</button>
                    </td>
                </tr>
            `).join('');
            noMessage.style.display = 'none';
        } else {
            tbody.innerHTML = '';
            noMessage.style.display = 'block';
        }
    }

    async deleteTeacher(id) {
        const confirmed = await Swal.fire({
            title: 'Delete Teacher?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, delete it!'
        });

        if (confirmed.isConfirmed) {
            try {
                const response = await API.request('/staff/delete.php', 'DELETE', { id, type: 'teacher' });

                if (response.success) {
                    Swal.fire('Deleted!', 'Teacher deleted successfully.', 'success');
                    this.loadStaff();
                }
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    }

    async deleteNTS(id) {
        const confirmed = await Swal.fire({
            title: 'Delete Non-Teaching Staff?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, delete it!'
        });

        if (confirmed.isConfirmed) {
            try {
                const response = await API.request('/staff/delete.php', 'DELETE', { id, type: 'nts' });

                if (response.success) {
                    Swal.fire('Deleted!', 'Non-Teaching Staff deleted successfully.', 'success');
                    this.loadStaff();
                }
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    }

    async searchTeachers() {
        const query = document.getElementById('teacherSearch').value;

        if (!query.trim()) {
            this.loadStaff();
            return;
        }

        try {
            const response = await API.request(`/staff/teachers.php?search=${encodeURIComponent(query)}`);

            if (response.success) {
                this.displayTeachers(response.staff);
            }
        } catch (error) {
            console.error('Error searching teachers:', error);
        }
    }

    // ===== DASHBOARD =====

    async loadDashboardData() {
        try {
            const studentsRes = await API.request('/students/count.php');
            const teachersRes = await API.request('/staff/count.php?type=teacher');
            const ntsRes = await API.request('/staff/count.php?type=nts');
            const subjectsRes = await API.request('/subjects/count.php');

            if (studentsRes.success) {
                document.getElementById('totalStudents').textContent = studentsRes.count;
            }
            if (teachersRes.success) {
                document.getElementById('totalTeachers').textContent = teachersRes.count;
            }
            if (ntsRes.success) {
                document.getElementById('totalNTS').textContent = ntsRes.count;
            }
            if (subjectsRes.success) {
                document.getElementById('totalSubjects').textContent = subjectsRes.count;
            }

            this.loadChart(studentsRes.count, teachersRes.count);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    loadChart(students, teachers) {
        const ctx = document.getElementById('overviewChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Students', 'Teachers'],
                    datasets: [{
                        data: [students, teachers],
                        backgroundColor: ['#007bff', '#27ae60'],
                        borderColor: ['#0056b3', '#219653'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    // ===== LOGOUT =====

    async logout() {
        try {
            await API.auth.logout();
            window.location.href = '../../login.html';
        } catch (error) {
            console.error('Logout error:', error);
        }
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize dashboard
const dashboard = new AdminDashboard();