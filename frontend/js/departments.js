// Departments page logic
if (!requireAuth()) throw new Error('Not authenticated');

let currentPage = 1;
let lastPage = 1;
let paginationMeta = null;
let paginationType = null;
let deleteDepartmentId = null;
let LoadedDepartments = [];

const departmentModal = new bootstrap.Modal(document.getElementById('departmentModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

function extractValue(val) {
    return Array.isArray(val) ? val[0] : val;
}

// Load departments
async function loadDepartments(page = 1, search = '', append = false) {
    const tbody = document.getElementById('departmentsBody');
    
    if (!append) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm"></div> Loading...</td></tr>';
        LoadedDepartments = [];
    }

    let url = `/departments?page=${page}`;
    if (search) {
        url += `&name=${encodeURIComponent(search)}`;
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
            LoadedDepartments = [...LoadedDepartments, ...(data.data || [])];
        } else {
            LoadedDepartments = data.data || [];
        }
        
        renderDepartments(LoadedDepartments);
        renderPagination();
    } catch (error) {
        console.error('Error loading departments:', error);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading departments</td></tr>';
    }
}

function renderDepartments(departments) {
    const tbody = document.getElementById('departmentsBody');
    
    if (departments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No departments found</td></tr>';
        return;
    }
    
    tbody.innerHTML = departments.map(dept => `
        <tr>
            <td>${escapeHtml(dept.name)}</td>
            <td>${dept.contacts_count || 0}</td>
            <td>
                <button class="btn btn-sm btn-primary me-1" 
                        onclick="openEditModal(${dept.id}, '${escapeHtml(dept.name).replace(/'/g, "\\'")}')">
                    Edit
                </button>
                <button class="btn btn-sm btn-danger" onclick="openDeleteModal(${dept.id})">
                    Delete
                </button>
            </td>
        </tr>
    `).join('');
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
    const search = document.getElementById('searchName').value.trim();
    
    let html = '<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">';
    
    if (from && to && total) {
        html += `<div class="text-muted small">
            Showing <strong>${from}</strong> to <strong>${to}</strong> of <strong>${total}</strong> departments
        </div>`;
    }
    
    if (links && links.length > 0) {
        html += '<nav aria-label="Departments pagination"><ul class="pagination pagination-sm mb-0 flex-wrap">';
        
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
                html += `<li class="${itemClass}">
                    <a class="page-link" href="#" onclick="loadDepartments(${page}, '${search}'); return false;"${isActive ? ' aria-current="page"' : ''}>${label}</a>
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
                All ${total} departments loaded
            </div>
        `;
    }
}

function loadMore() {
    const search = document.getElementById('searchName').value.trim();
    const btn = document.getElementById('loadMoreBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading more...';
    loadDepartments(currentPage + 1, search, true);
}

function decodeHtmlEntities(text) {
    const textArea = document.createElement('textarea');
    textArea.innerHTML = text;
    return textArea.value;
}

function searchDepartments() {
    const search = document.getElementById('searchName').value.trim();
    loadDepartments(1, search);
}

function clearSearch() {
    document.getElementById('searchName').value = '';
    loadDepartments(1);
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Department';
    document.getElementById('departmentId').value = '';
    document.getElementById('departmentForm').reset();
    departmentModal.show();
}

function openEditModal(id, name) {
    document.getElementById('modalTitle').textContent = 'Edit Department';
    document.getElementById('departmentId').value = id;
    document.getElementById('departmentName').value = name;
    departmentModal.show();
}

function openDeleteModal(id) {
    deleteDepartmentId = id;
    deleteModal.show();
}

// Event listeners
document.getElementById('departmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    
    const id = document.getElementById('departmentId').value;
    const isEdit = !!id;
    
    const departmentData = {
        name: document.getElementById('departmentName').value
    };
    
    try {
        const response = await apiRequest(
            isEdit ? `/departments/${id}` : '/departments',
            {
                method: isEdit ? 'PUT' : 'POST',
                body: JSON.stringify(departmentData)
            }
        );
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            showAlert(data.message || 'Department saved successfully');
            departmentModal.hide();
            loadDepartments(currentPage, document.getElementById('searchName').value.trim());
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message;
            showAlert(errors || 'Error saving department', 'danger');
        }
    } catch (error) {
        console.error('Error saving department:', error);
        showAlert('Error saving department', 'danger');
    }
    
    btn.disabled = false;
    btn.innerHTML = 'Save';
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!deleteDepartmentId) return;
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        const response = await apiRequest(`/departments/${deleteDepartmentId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            showAlert('Department deleted successfully');
            deleteModal.hide();
            loadDepartments(currentPage, document.getElementById('searchName').value.trim());
        } else {
            showAlert(data.message || 'Error deleting department', 'danger');
        }
    } catch (error) {
        console.error('Error deleting department:', error);
        showAlert('Error deleting department', 'danger');
    }
    
    this.disabled = false;
    this.innerHTML = 'Delete';
    deleteDepartmentId = null;
});

// Search on enter
document.getElementById('searchName').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchDepartments();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initNavbar('departments');
    loadDepartments();
});
