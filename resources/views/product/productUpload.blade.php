@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center g-4">
        <div class="col-xxl-10">
            <!-- Main Card with Green Shadow -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="box-shadow: 0 10px 30px rgba(134, 239, 172, 0.1);">
                <!-- Card Header with Green Accent -->
                <div class="card-header p-4 border-0" style="background-color: #f0fdf4;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h4 mb-0 fw-bold" style="color: #166534;">Supplier Product Upload A Setup</h2>
                            <p class="mb-0" style="color: #15803d;">Configure your product upload settings</p>
                        </div>
                        <button class="btn rounded-pill px-4 shadow-sm" style="background-color:rgba(51, 196, 104, 0.96); color: white; border: none;">
                            <i class="bi bi-save me-2"></i>Save & Continue
                        </button>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4" style="background-color: #f0fdf4;">
                    <div class="row g-4">
                        <!-- Product Categories Section -->
                        <div class="col-lg-12">
                            <div class="card border-0 shadow-sm rounded-3 mb-4" style="box-shadow: 0 4px 12px rgba(134, 239, 172, 0.08);">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-bold" style="color: #166534;">
                                        <i class="bi bi-tags me-2" style="color: #22c55e;"></i>Product Categories
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-floating">
                                        <div class="d-flex flex-wrap gap-3 align-items-center">
                                            @foreach($masterCategories as $master)
                                                <div class="position-relative d-flex align-items-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-success dropdown-toggle" type="button" id="dropdownMenuButton-{{ $master->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                            {{ $master->name }}
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $master->id }}">
                                                            @if($master->subcategories->count())
                                                                @foreach($master->subcategories as $sub)
                                                                    <li class="px-3 py-1 text-secondary d-flex align-items-center justify-content-between">
                                                                        <span>{{ $sub->name }}</span>
                                                                        <button class="btn btn-link p-0 ms-2 text-danger" title="Delete Subcategory" onclick="deleteCategory({{ $sub->id }})">
                                                                            <i class="bi bi-x-lg"></i>
                                                                        </button>
                                                                    </li>
                                                                @endforeach
                                                            @else
                                                                <li class="px-3 py-1 text-muted">No subcategories</li>
                                                            @endif
                                                            <li class="d-flex justify-content-end">
                                                                <button class="btn btn-link p-0 text-success" title="Add Subcategory" onclick="showAddSubCategoryModal({{ $master->id }})">
                                                                    <i class="bi bi-plus-lg"></i>
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <button class="btn btn-link p-0 ms-1 text-danger position-absolute top-0 end-0" style="transform: translate(50%,-50%);" title="Delete Category" onclick="deleteCategory({{ $master->id }})">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                            <button class="btn btn-link p-0 text-success ms-2" title="Add Master Category" onclick="showAddMasterCategoryModal()">
                                                <i class="bi bi-plus-lg" style="font-size:1.5rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2" style="color: #16a34a;">These categories will be used by AI to organize your products</div>
                                </div>
                            </div>
                        </div>
                        <!-- Left Column -->
                        <div class="col-lg-6">
                            <!-- Image Upload Section -->
                            <div class="card border-0 shadow-sm rounded-3 mb-4" style="box-shadow: 0 4px 12px rgba(134, 239, 172, 0.08);">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-bold" style="color: #166534;">
                                        <i class="bi bi-images me-2" style="color: #22c55e;"></i>Product Images
                                    </h5>
                                </div>
                                <div class="card-body product-images-card-body">
                                    <div id="image-preview-grid" class="d-flex flex-wrap gap-3 mb-4"></div>
                                    <input type="file" id="image-files" name="image_files" class="d-none" accept="image/*" multiple />
                                    <div class="d-flex gap-3 mt-auto">
                                        <button type="button" class="btn rounded-pill px-4 shadow-sm" onclick="triggerImageInput()" 
                                            style="background-color: white; color:rgb(86, 230, 139); border: 1px solid #86efac;">
                                            <i class="bi bi-upload me-2"></i>Add Images
                                        </button>
                                        <button id="upload-images-btn" class="btn rounded-pill px-4 shadow-sm" onclick="submitImages()" style="display:none; background-color: #22c55e; color: white;">
                                            <i class="bi bi-cloud-arrow-up me-2"></i>Upload
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Excel Upload Section -->
                            <div class="card border-0 shadow-sm rounded-3" style="box-shadow: 0 4px 12px rgba(134, 239, 172, 0.08);">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-bold" style="color: #166534;">
                                        <i class="bi bi-file-earmark-excel me-2" style="color: #22c55e;"></i>Excel Upload for Prices & Stock
                                    </h5>
                                </div>
                                <div class="card-body excel-upload-card-body">
                                    <input type="file" id="excel-upload" name="excel_upload" accept=".xls,.xlsx" class="d-none" />
                                    <div class="d-flex gap-3 align-items-center mb-3">
                                        <button type="button" class="btn rounded-pill px-4 shadow-sm" id="open-excel-btn" style="background-color: white; color: #22c55e; border: 1px solid #86efac;">
                                            <i class="bi bi-folder2-open me-2"></i>Open Excel
                                        </button>
                                        <button type="button" class="btn rounded-pill px-4 shadow-sm" id="upload-excel-btn" style="background-color: #22c55e; color: white;" disabled>
                                            <i class="bi bi-cloud-arrow-up me-2"></i>Upload
                                        </button>
                                        <span id="excel-file-name" class="ms-2 small text-success">No file selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - AI Assistant -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100" style="box-shadow: 0 4px 12px rgba(134, 239, 172, 0.08);">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-bold" style="color: #166534;">
                                        <i class="bi bi-robot me-2" style="color: #22c55e;"></i>AI Assistant
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column ai-assistant-card-body" style="background-color: #dcfce7;">
                                    <!-- AI Tools -->
                                    <div class="mb-4">
                                        <button class="btn btn-sm rounded-pill mb-2 shadow-sm" onclick="tagProductCategory()" 
                                            style="background-color: white; color: #854d0e; border: 1px solid #86efac;">
                                            <i class="bi bi-tag-fill me-1"></i>Tag Product Category Per Item
                                        </button>
                                        <div class="d-flex gap-2 mb-3">
                                            <button class="btn btn-sm rounded-pill shadow-sm" onclick="organizeByStyle()" 
                                                style="background-color: white; color: #22c55e; border: 1px solid #86efac;">
                                                <i class="bi bi-funnel-fill me-1"></i>Organize By Style
                                            </button>
                                            <button class="btn btn-sm rounded-pill shadow-sm" onclick="sortByStyle()" 
                                                style="background-color: white; color:rgb(39, 197, 97); border: 1px solid #86efac;">
                                                <i class="bi bi-filter-square-fill me-1"></i>Sort By Style
                                            </button>
                                        </div>
                                        <button class="btn w-100 rounded-pill mb-3 shadow" onclick="startAISorting()" 
                                            style="background-color:rgba(77, 207, 125, 0.93); color: white; border: none;">
                                            <i class="bi bi-magic me-1"></i>Start AI Sorting 
                                            <span class="badge ms-2" style="background-color: white; color: #22c55e;">€0.20/item</span>
                                        </button>
                                    </div>

                                    <!-- AI Chat -->
                                    <div class="flex-grow-1 d-flex flex-column">
                                        <div id="ai-chat-error" class="alert alert-danger d-none mb-3" role="alert"></div>
                                        
                                        <!-- Chat Container -->
                                        <div class="card border-0 flex-grow-1 mb-3" style="background-color: white; border-radius: 12px;">
                                            <div id="ai-chat-history" class="card-body p-3" style="overflow-y:auto;"></div>
                                        </div>
                                        
                                        <!-- Input Area -->
                                        <div class="position-relative">
                                            <textarea class="form-control ps-4 pe-5" id="ai-chat-question" name="ai_chat_question" 
                                                rows="2" placeholder="Ask about product variants, sizes, or other details..."
                                                style="border-radius: 20px; border-color: #86efac;"></textarea>
                                            <button class="btn rounded-circle position-absolute end-0 top-0 me-2 mt-2 shadow-sm" 
                                                type="button" id="ai-chat-send-btn" onclick="sendAIChat()"
                                                style="width: 36px; height: 36px; background-color: #22c55e; color: white; border: none;">
                                                <i class="bi bi-send-fill"></i>
                                            </button>
                                        </div>
                                        <!-- <div class="form-text text-end mt-2" style="color: #16a34a;">Press <kbd>Enter</kbd> to send, <kbd>Shift+Enter</kbd> for new line</div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Section - COMPLETELY UNCHANGED from original -->
<script>
document.addEventListener('DOMContentLoaded', function() {
// --- Image Upload Logic ---
window.selectedImages = [];
window.triggerImageInput = function() {
    document.getElementById('image-files').click();
}
document.getElementById('image-files').addEventListener('change', handleImageSelection);
function handleImageSelection(e) {
    const files = Array.from(e.target.files);
    files.forEach(f => {
        if (!window.selectedImages.some(img => img.name === f.name && img.size === f.size)) {
            window.selectedImages.push(f);
        }
    });
    renderImagePreviews();
    e.target.value = '';
}
function renderImagePreviews() {
    const grid = document.getElementById('image-preview-grid');
    grid.innerHTML = '';
    window.selectedImages.forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        const box = document.createElement('div');
        box.className = 'border rounded p-2 position-relative';
        box.style.width = '110px';
        box.style.height = '140px';
        box.style.display = 'flex';
        box.style.alignItems = 'center';
        box.style.justifyContent = 'center';
        box.style.background = '#fafafa';
        box.innerHTML = `
            <img src='${url}' alt='Product ${idx+1}' style='max-width:100%; max-height:120px;' />
            <button type='button' class='btn btn-sm btn-danger position-absolute top-0 end-0 m-1' style='z-index:2;' onclick='removeImage(${idx})'>&times;</button>
        `;
        grid.appendChild(box);
    });
    // Add "+" box
    const plusBox = document.createElement('div');
    plusBox.className = 'border rounded p-2';
    plusBox.style.width = '110px';
    plusBox.style.height = '140px';
    plusBox.style.display = 'flex';
    plusBox.style.alignItems = 'center';
    plusBox.style.justifyContent = 'center';
    plusBox.style.background = '#fafafa';
    plusBox.style.cursor = 'pointer';
    plusBox.onclick = triggerImageInput;
    grid.appendChild(plusBox);
    document.getElementById('upload-images-btn').style.display = window.selectedImages.length ? 'inline-block' : 'none';
}
window.removeImage = function(idx) {
    window.selectedImages.splice(idx, 1);
    renderImagePreviews();
}
window.submitImages = function() {
    if (!window.selectedImages.length) return;
    const formData = new FormData();
    window.selectedImages.forEach((file, idx) => {
        formData.append('images[]', file);
    });
    formData.append('history', JSON.stringify(aiChatHistory));
    // Optionally add a message or step indicator
    formData.append('message', '[image upload]');
    const sendBtn = document.getElementById('upload-images-btn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Uploading...';
    fetch('/api/ai-chat', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            const errorDiv = document.getElementById('ai-chat-error');
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('d-none');
        } else {
            aiChatHistory = Array.isArray(data.history) ? data.history : [];
            renderAIChatHistory();
            window.selectedImages = [];
            renderImagePreviews();
        }
    })
    .catch((e) => {
        const errorDiv = document.getElementById('ai-chat-error');
        errorDiv.textContent = 'Failed to upload images.';
        errorDiv.classList.remove('d-none');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Upload';
    });
}
window.onload = renderImagePreviews;

// --- Excel Upload Logic ---
(function() {
    const excelInput = document.getElementById('excel-upload');
    const openExcelBtn = document.getElementById('open-excel-btn');
    const uploadExcelBtn = document.getElementById('upload-excel-btn');
    const excelFileName = document.getElementById('excel-file-name');
    let selectedExcelFile = null;

    openExcelBtn.addEventListener('click', function() {
        excelInput.click();
    });

    excelInput.addEventListener('change', function(e) {
        selectedExcelFile = e.target.files[0] || null;
        excelFileName.textContent = selectedExcelFile ? selectedExcelFile.name : 'No file selected';
        uploadExcelBtn.disabled = !selectedExcelFile;
    });

    uploadExcelBtn.addEventListener('click', function() {
        if (!selectedExcelFile) return;
        const formData = new FormData();
        formData.append('excel', selectedExcelFile);
        formData.append('history', JSON.stringify(window.aiChatHistory || []));
        formData.append('message', '[excel upload]');
        uploadExcelBtn.disabled = true;
        uploadExcelBtn.textContent = 'Uploading...';
        fetch('/api/ai-chat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                const errorDiv = document.getElementById('ai-chat-error');
                errorDiv.textContent = data.error;
                errorDiv.classList.remove('d-none');
            } else {
                window.aiChatHistory = Array.isArray(data.history) ? data.history : [];
                if (typeof renderAIChatHistory === 'function') renderAIChatHistory();
                excelFileName.textContent = 'No file selected';
                excelInput.value = '';
                selectedExcelFile = null;
            }
        })
        .catch((e) => {
            const errorDiv = document.getElementById('ai-chat-error');
            errorDiv.textContent = 'Failed to upload Excel file.';
            errorDiv.classList.remove('d-none');
        })
        .finally(() => {
            uploadExcelBtn.disabled = false;
            uploadExcelBtn.textContent = 'Upload';
        });
    });
})();

// --- AI Assistant Placeholder Functions ---
window.sortByStyle = function() { alert('Sort By Style (AI logic placeholder)'); }
window.sortByCategoryAndStyle = function() { alert('Sort By Category and per Style (AI logic placeholder)'); }
window.tagProductCategory = function() { alert('Tag Product Category Per Item (AI logic placeholder)'); }
window.organizeByStyle = function() { alert('Organize By Style (AI logic placeholder)'); }
window.startAISorting = function() { alert('Start AI Sorting (AI logic placeholder)'); }

// --- AI Assistant AJAX Chat ---
let aiChatHistory = [];

function renderAIChatHistory() {
    const historyDiv = document.getElementById('ai-chat-history');
    historyDiv.innerHTML = '';
    aiChatHistory.forEach(entry => {
        if (entry.user && entry.user !== '[image]') {
            historyDiv.innerHTML += `<div class='mb-1'><b>You:</b> ${escapeHtml(entry.user)}</div>`;
        } else if (entry.user === '[image]') {
            historyDiv.innerHTML += `<div class='mb-1'><b>You:</b> <i>Sent an image</i></div>`;
        }
        if (entry.ai) {
            historyDiv.innerHTML += `<div class='mb-2'><b>AI:</b> ${escapeHtml(entry.ai)}` + (entry.category ? `<br/><span class='badge bg-info text-dark'>Category: ${escapeHtml(entry.category)}</span>` : '') + `</div>`;
        }
    });
    historyDiv.scrollTop = historyDiv.scrollHeight;
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function (c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
    });
}

window.sendAIChat = function() {
    const question = document.getElementById('ai-chat-question').value.trim();
    if (!question) return;
    const formData = new FormData();
    formData.append('message', question);
    formData.append('history', JSON.stringify(aiChatHistory));
    // Disable send button
    const sendBtn = document.getElementById('ai-chat-send-btn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';
    // Hide previous error
    document.getElementById('ai-chat-error').classList.add('d-none');
    fetch('/api/ai-chat', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            const errorDiv = document.getElementById('ai-chat-error');
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('d-none');
        } else {
            console.log("data: ", data)
            aiChatHistory = Array.isArray(data.history) ? data.history : [];
            renderAIChatHistory();
            document.getElementById('ai-chat-question').value = '';
        }
    })
    .catch((e) => {
        console.log("error:", e)
        const errorDiv = document.getElementById('ai-chat-error');
        errorDiv.textContent = 'Failed to contact AI.';
        errorDiv.classList.remove('d-none');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send';
    });
}

// Send message on Enter, newline on Shift+Enter
const chatTextarea = document.getElementById('ai-chat-question');
if (chatTextarea) {
    chatTextarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            window.sendAIChat();
        }
    });
}

// Initial render
renderAIChatHistory();

// Clear chat history on page load (local and server)
aiChatHistory = [];
renderAIChatHistory();
fetch('/api/ai-chat/clear', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    }
});

// Category Modals and AJAX
window.showAddMasterCategoryModal = function() {
    var modal = new bootstrap.Modal(document.getElementById('addMasterCategoryModal'));
    document.getElementById('addMasterCategoryForm').reset();
    modal.show();
}
window.showAddSubCategoryModal = function(parentId) {
    var modal = new bootstrap.Modal(document.getElementById('addSubCategoryModal'));
    document.getElementById('addSubCategoryForm').reset();
    document.getElementById('subCategoryParentId').value = parentId;
    modal.show();
}
document.getElementById('addMasterCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('parent_id', 0);
    fetch('/categories', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert(data.message || data.error);
        } else {
            location.reload();
        }
    });
});
document.getElementById('addSubCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    fetch('/categories', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert(data.message || data.error);
        } else {
            location.reload();
        }
    });
});
window.deleteCategory = function(id) {
    // Show modal instead of confirm
    window.categoryToDelete = id;
    document.getElementById('moveToCategory').value = '';
    document.getElementById('deleteProductsCheck').checked = false;
    document.getElementById('deleteCategoryError').style.display = 'none';
    var modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    modal.show();
}
document.getElementById('confirmDeleteCategoryBtn').onclick = function() {
    var id = window.categoryToDelete;
    var moveTo = document.getElementById('moveToCategory').value;
    var deleteProducts = document.getElementById('deleteProductsCheck').checked ? 1 : 0;
    if (!moveTo && !deleteProducts) {
        document.getElementById('deleteCategoryError').textContent = 'Please select a category to move products or choose to delete products.';
        document.getElementById('deleteCategoryError').style.display = 'block';
        return;
    }
    var formData = new FormData();
    formData.append('category_id', id);
    formData.append('move_to_category_id', moveTo);
    formData.append('delete_products', deleteProducts);
    fetch('/categories/delete-with-products', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            document.getElementById('deleteCategoryError').textContent = data.error || data.message;
            document.getElementById('deleteCategoryError').style.display = 'block';
        } else {
            location.reload();
        }
    })
    .catch(() => {
        document.getElementById('deleteCategoryError').textContent = 'Failed to delete category.';
        document.getElementById('deleteCategoryError').style.display = 'block';
    });
};
});
</script>

<style>
    :root {
        --product-images-box-height: 420px;
    }
    .product-images-card-body {
        min-height: var(--product-images-box-height);
        height: var(--product-images-box-height);
        background-color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    #image-preview-grid {
        flex: 1 1 auto;
        min-height: 120px;
        overflow-y: auto;
        margin-bottom: 1rem;
    }
    .excel-upload-card-body {
        min-height: 100px;
    }
    .ai-assistant-card-body {
        min-height: var(--product-images-box-height);
        height: var(--product-images-box-height);
        background-color: #dcfce7;
        display: flex;
        flex-direction: column;
    }
    /* Custom Green Theme */
    .bg-green-50 { background-color:rgba(239, 250, 242, 0.87); }
    .bg-green-100 { background-color:rgb(232, 255, 240); }
    
    /* Smooth shadows */
    .shadow-sm { box-shadow: 0 1px 3px rgba(134, 239, 172, 0.1); }
    .shadow { box-shadow: 0 4px 6px rgba(134, 239, 172, 0.1); }
    .shadow-lg { box-shadow: 0 10px 15px rgba(134, 239, 172, 0.1); }
    
    /* Custom scrollbar for chat */
    #ai-chat-history {
        height: 300px;
        scrollbar-width: thin;
    }
    #ai-chat-history::-webkit-scrollbar {
        width: 6px;
    }
    #ai-chat-history::-webkit-scrollbar-track {
        background:rgb(232, 253, 239);
        border-radius: 10px;
    }
    #ai-chat-history::-webkit-scrollbar-thumb {
        background:rgb(160, 229, 185);
        border-radius: 10px;
    }
    
    /* Hover effects */
    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    
    /* Image grid styling */
    #image-preview-grid {
        min-height: 160px;
    }

    .dropdown-menu {
        min-width: 200px;
    }
    .dropdown-toggle::after {
        margin-left: 0.5em;
    }
    .d-flex.flex-wrap.gap-3 > .dropdown {
        margin-bottom: 0.5rem;
    }
</style>

<!-- Delete/Move Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Do you want to move the products in this category to another category/subcategory, or delete them?</p>
        <div class="mb-3">
          <label for="moveToCategory" class="form-label">Move products to:</label>
          <select class="form-select" id="moveToCategory">
            <option value="">-- Select Category --</option>
            @foreach($masterCategories as $master)
              <option value="{{ $master->id }}">{{ $master->name }}</option>
              @foreach($master->subcategories as $sub)
                <option value="{{ $sub->id }}">&nbsp;&nbsp;{{ $sub->name }}</option>
              @endforeach
            @endforeach
          </select>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" id="deleteProductsCheck">
          <label class="form-check-label" for="deleteProductsCheck">
            Delete all products in this category
          </label>
        </div>
        <div id="deleteCategoryError" class="text-danger mt-2" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteCategoryBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Master Category Modal -->
<div class="modal fade" id="addMasterCategoryModal" tabindex="-1" aria-labelledby="addMasterCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addMasterCategoryForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addMasterCategoryModalLabel">Add Master Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="masterCategoryName" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="masterCategoryName" name="name" required>
          </div>
          <input type="hidden" name="parent_id" value="0">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Sub Category Modal -->
<div class="modal fade" id="addSubCategoryModal" tabindex="-1" aria-labelledby="addSubCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addSubCategoryForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addSubCategoryModalLabel">Add Subcategory</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="subCategoryName" class="form-label">Subcategory Name</label>
            <input type="text" class="form-control" id="subCategoryName" name="name" required>
          </div>
          <input type="hidden" id="subCategoryParentId" name="parent_id" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection