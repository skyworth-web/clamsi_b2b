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
                        <!-- Left Column -->
                        <div class="col-lg-6">
                            <!-- Product Categories Section -->
                            <div class="card border-0 shadow-sm rounded-3 mb-4" style="box-shadow: 0 4px 12px rgba(134, 239, 172, 0.08);">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-bold" style="color: #166534;">
                                        <i class="bi bi-tags me-2" style="color: #22c55e;"></i>Product Categories
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-floating">
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach($masterCategories as $master)
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-success dropdown-toggle" type="button" id="dropdownMenuButton-{{ $master->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ $master->name }}
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $master->id }}">
                                                        @if($master->subcategories->count())
                                                            @foreach($master->subcategories as $sub)
                                                                <li class="px-3 py-1 text-secondary">{{ $sub->name }}</li>
                                                            @endforeach
                                                        @else
                                                            <li class="px-3 py-1 text-muted">No subcategories</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-text mt-2" style="color: #16a34a;">These categories will be used by AI to organize your products</div>
                                </div>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="card border-0 shadow-sm rounded-3 mb-4" style="box-shadow: 0 4px 12px rgba(134, 239, 172, 0.08);">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-bold" style="color: #166534;">
                                        <i class="bi bi-images me-2" style="color: #22c55e;"></i>Product Images
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="image-preview-grid" class="d-flex flex-wrap gap-3 mb-4"></div>
                                    
                                    <input type="file" id="image-files" name="image_files" class="d-none" accept="image/*" multiple />
                                    <div class="d-flex gap-3">
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
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="file" id="excel-upload" name="excel_upload" accept=".xls,.xlsx" class="d-none" />
                                        <label for="excel-upload" class="btn rounded-pill px-4 flex-grow-1 shadow-sm" 
                                            style="background-color: white; color: #22c55e; border: 1px solid #86efac;">
                                            <i class="bi bi-upload me-2"></i>Upload Excel (.xls, .xlsx)
                                        </label>
                                    </div>
                                    <div id="excel-file-name" class="mt-3 p-3 rounded-2 small" style="background-color: #dcfce7; color: #15803d;">
                                        No file selected
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
                                <div class="card-body d-flex flex-column" style="background-color: #dcfce7;">
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
window.uploadExcelFile = function() {
    const excelInput = document.getElementById('excel-upload');
    const file = excelInput.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('excel', file);
    formData.append('history', JSON.stringify(aiChatHistory));
    formData.append('message', '[excel upload]');
    const excelLabel = document.querySelector('label[for="excel-upload"]');
    excelLabel.textContent = 'Uploading...';
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
            document.getElementById('excel-file-name').textContent = 'No file selected';
            excelInput.value = '';
        }
    })
    .catch((e) => {
        const errorDiv = document.getElementById('ai-chat-error');
        errorDiv.textContent = 'Failed to upload Excel file.';
        errorDiv.classList.remove('d-none');
    })
    .finally(() => {
        excelLabel.textContent = 'Upload Excel (.xls, .xlsx)';
    });
}
document.getElementById('excel-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    document.getElementById('excel-file-name').textContent = file ? file.name : '';
    if (file) {
        window.uploadExcelFile();
    }
});

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
});
</script>

<style>
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
@endsection