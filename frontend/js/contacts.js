// Contacts page logic
if (!requireAuth()) throw new Error('Not authenticated');

let currentPage = 1;
let lastPage = 1;
let paginationMeta = null;
let paginationType = null;
let deleteContactId = null;
let allDepartments = [];
let LoadedContacts = [];

const contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

function extractValue(val) {
    return Array.isArray(val) ? val[0] : val;
}

// Load departments for dropdown
async function loadDepartments() {
    try {
        const response = await apiRequest('/departments?per_page=100');
        const data = await response.json();
        
        if (data.success && data.data) {
            allDepartments = data.data;
            
            // Populate search dropdown
            const searchSelect = document.getElementById('searchDepartment');
            searchSelect.innerHTML = '<option value="">All Departments</option>';
            allDepartments.forEach(dept => {
                searchSelect.innerHTML += `<option value="${dept.id}">${escapeHtml(dept.name)}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

// Render department checkboxes
function renderDepartmentCheckboxes(selectedIds = []) {
    const container = document.getElementById('departmentsCheckboxes');
    
    if (allDepartments.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No departments available</p>';
        return;
    }
    
    container.innerHTML = allDepartments.map(dept => `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="${dept.id}" 
                   id="dept${dept.id}" ${selectedIds.includes(dept.id) ? 'checked' : ''}>
            <label class="form-check-label" for="dept${dept.id}">
                ${escapeHtml(dept.name)}
            </label>
        </div>
    `).join('');
}

// Load contacts
async function loadContacts(page = 1, search = new URLSearchParams(), append = false) {
    const tbody = document.getElementById('contactsBody');
    
    if (!append) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner-border spinner-border-sm"></div> Loading...</td></tr>';
        LoadedContacts = [];
    }

    let url = `/contacts?page=${page}`;
    if (search.toString()) {
        url += `&${search.toString()}`;
    }

    try {
        const response = await apiRequest(url);
        const data = await response.json();
        
        const meta = data.meta || {};
        paginationType = meta.pagination_type || 'pagination';
        currentPage = extractValue(meta.current_page) || 1;
        lastPage = extractValue(meta.last_page) || 1;
        paginationMeta = {
            from: extractValue(meta.from),
            to: extractValue(meta.to),
            total: extractValue(meta.total),
            per_page: extractValue(meta.per_page),
            has_more_pages: meta.has_more_pages,
            links: meta.links || []
        };

        if (append) {
            LoadedContacts = [...LoadedContacts, ...(data.data || [])];
        } else {
            LoadedContacts = data.data || [];
        }
        
        renderContacts(LoadedContacts);
        renderPagination();
    } catch (error) {
        console.error('Error loading contacts:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading contacts</td></tr>';
    }
}

function renderContacts(contacts) {
    const tbody = document.getElementById('contactsBody');
    
    if (contacts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No contacts found</td></tr>';
        return;
    }
    
    tbody.innerHTML = contacts.map(contact => {
        const departments = contact.departments?.map(d => escapeHtml(d.name)).join(', ') || '-';
        return `
            <tr>
                <td>${escapeHtml(contact.first_name)} ${escapeHtml(contact.last_name)}</td>
                <td class="mobile-hide">${escapeHtml(contact.phone_number)}</td>
                <td class="mobile-hide">${escapeHtml(contact.city || '-')}</td>
                <td class="mobile-hide">${contact.birthdate || '-'}</td>
                <td>${departments}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="openEditModal(${contact.id})">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(${contact.id})">
                        Delete
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination() {
    if (paginationType === 'load_more') {
        renderLoadMore();
    } else {
        renderStandardPagination();
    }
}

function renderStandardPagination() {
    const container = document.getElementById('paginationContainer');
    const { from, to, total, links } = paginationMeta;
    
    const name = document.getElementById('searchName').value.trim();
    const phone = document.getElementById('searchPhone').value.trim();
    const department = document.getElementById('searchDepartment').value;
    
    const params = new URLSearchParams();
    if (name) params.append('name', name);
    if (phone) params.append('phone', phone);
    if (department) params.append('department_id', department);
    const search = params.toString();
    
    let html = '<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">';
    
    if (from && to && total) {
        html += `<div class="text-muted small">
            Showing <strong>${from}</strong> to <strong>${to}</strong> of <strong>${total}</strong> contacts
        </div>`;
    }
    
    if (links && links.length > 0) {
        html += '<nav aria-label="Contacts pagination"><ul class="pagination pagination-sm mb-0 flex-wrap">';
        
        links.forEach(link => {
            const isDisabled = link.url === null;
            const isActive = link.active;
            const page = link.page;
            
            const label = decodeHtmlEntities(link.label);
            
            let itemClass = 'page-item';
            if (isDisabled) itemClass += ' disabled';
            if (isActive) itemClass += ' active';
            
            if (isDisabled) {
                html += `<li class="${itemClass}">
                    <span class="page-link">${label}</span>
                </li>`;
            } else {
                const searchParam = search ? `, new URLSearchParams('${search}')` : '';
                html += `<li class="${itemClass}">
                    <a class="page-link" href="#" onclick="loadContacts(${page}${searchParam}); return false;"${isActive ? ' aria-current="page"' : ''}>${label}</a>
                </li>`;
            }
        });
        
        html += '</ul></nav>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function renderLoadMore() {
    const container = document.getElementById('paginationContainer');
    const { from, to, total } = paginationMeta;

    if (paginationMeta.has_more_pages) {
        container.innerHTML = `
            <div class="text-center">
                <button class="btn btn-outline-primary" id="loadMoreBtn" onclick="loadMore()">
                    Load More (${to} of ${total})
                </button>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="text-center text-muted">
                All ${total} contacts loaded
            </div>
        `;
    }
}

function loadMore() {
    const params = new URLSearchParams();
    
    const name = document.getElementById('searchName').value.trim();
    const phone = document.getElementById('searchPhone').value.trim();
    const department = document.getElementById('searchDepartment').value;

    if (name) params.append('name', name);
    if (phone) params.append('phone', phone);
    if (department) params.append('department_id', department);

    const btn = document.getElementById('loadMoreBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading more...';

    loadContacts(currentPage + 1, params, true);
}

function decodeHtmlEntities(text) {
    const textArea = document.createElement('textarea');
    textArea.innerHTML = text;
    return textArea.value;
}

function searchContacts() {
    const params = new URLSearchParams();
    
    const name = document.getElementById('searchName').value.trim();
    const phone = document.getElementById('searchPhone').value.trim();
    const department = document.getElementById('searchDepartment').value;

    if (name) params.append('name', name);
    if (phone) params.append('phone', phone);
    if (department) params.append('department_id', department);
    
    loadContacts(1, params);
}

function clearSearch() {
    document.getElementById('searchName').value = '';
    document.getElementById('searchPhone').value = '';
    document.getElementById('searchDepartment').value = '';
    loadContacts(1);
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Contact';
    document.getElementById('contactId').value = '';
    document.getElementById('contactForm').reset();
    renderDepartmentCheckboxes([]);
    contactModal.show();
}

async function openEditModal(id) {
    document.getElementById('modalTitle').textContent = 'Edit Contact';
    document.getElementById('contactForm').reset();
    
    try {
        const response = await apiRequest(`/contacts/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const contact = data.data;
            document.getElementById('contactId').value = contact.id;
            document.getElementById('firstName').value = contact.first_name;
            document.getElementById('lastName').value = contact.last_name;
            document.getElementById('phoneNumber').value = contact.phone_number;
            document.getElementById('city').value = contact.city || '';
            document.getElementById('birthdate').value = contact.birthdate || '';
            
            const selectedIds = contact.departments?.map(d => d.id) || [];
            renderDepartmentCheckboxes(selectedIds);
            
            contactModal.show();
        }
    } catch (error) {
        console.error('Error loading contact:', error);
        showAlert('Error loading contact', 'danger');
    }
}

function openDeleteModal(id) {
    deleteContactId = id;
    deleteModal.show();
}

// Event listeners
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    
    const id = document.getElementById('contactId').value;
    const isEdit = !!id;
    
    const departmentIds = [];
    document.querySelectorAll('#departmentsCheckboxes input:checked').forEach(cb => {
        departmentIds.push(parseInt(cb.value));
    });
    
    const contactData = {
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        phone_number: document.getElementById('phoneNumber').value,
        city: document.getElementById('city').value || null,
        birthdate: document.getElementById('birthdate').value || null,
        department_ids: departmentIds
    };
    
    try {
        const response = await apiRequest(
            isEdit ? `/contacts/${id}` : '/contacts',
            {
                method: isEdit ? 'PUT' : 'POST',
                body: JSON.stringify(contactData)
            }
        );
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            showAlert(data.message || 'Contact saved successfully');
            contactModal.hide();
            loadContacts(currentPage);
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message;
            showAlert(errors || 'Error saving contact', 'danger');
        }
    } catch (error) {
        console.error('Error saving contact:', error);
        showAlert('Error saving contact', 'danger');
    }
    
    btn.disabled = false;
    btn.innerHTML = 'Save';
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!deleteContactId) return;
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        const response = await apiRequest(`/contacts/${deleteContactId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            showAlert('Contact deleted successfully');
            deleteModal.hide();
            loadContacts(currentPage);
        } else {
            showAlert(data.message || 'Error deleting contact', 'danger');
        }
    } catch (error) {
        console.error('Error deleting contact:', error);
        showAlert('Error deleting contact', 'danger');
    }
    
    this.disabled = false;
    this.innerHTML = 'Delete';
    deleteContactId = null;
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initNavbar('contacts');
    loadDepartments();
    loadContacts();
});
