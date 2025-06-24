@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="mb-4">Supplier Product Upload A Setup</h3>
                    <!-- Product Categories Section -->
                    <div class="mb-4">
                        <label for="product-categories" class="form-label fw-bold">Product Categories :</label>
                        <textarea id="product-categories" class="form-control" rows="3" placeholder="Add Default product Categories that will be use by AI to Input Products"></textarea>
                        <div class="form-text">Add Default product Categories that will be use by AI to Input Products</div>
                    </div>
                    <div class="row">
                        <!-- Product Image & Excel Upload -->
                        <div class="col-lg-6">
                            <div class="mb-4">
                                <div class="mb-2 fw-bold">Product Image</div>
                                <div id="image-preview-grid" class="d-flex flex-wrap gap-3 mb-3"></div>
                                <input type="file" id="image-files" class="d-none" accept="image/*" multiple />
                                <button type="button" class="btn btn-outline-primary mb-2" onclick="triggerImageInput()">
                                    <i class="bi bi-upload"></i> Add Images
                                </button>
                                <button id="upload-images-btn" class="btn btn-success" onclick="submitImages()" style="display:none;">Upload Images</button>
                            </div>
                            <div class="mb-4">
                                <div class="fw-bold mb-2">Excel Upload for Prices & Stock</div>
                                <input type="file" id="excel-upload" accept=".xls,.xlsx" class="d-none" />
                                <label for="excel-upload" class="btn btn-outline-primary">
                                    Upload Excel (.xls, .xlsx)
                                </label>
                                <span id="excel-file-name" class="ms-2 text-muted"></span>
                            </div>
                        </div>
                        <!-- AI Assistant Panel -->
                        <div class="col-lg-6">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body">
                                    <div class="fw-bold mb-2">AI assistant</div>
                                    <div class="mb-2">
                                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="sortByStyle()">Sort By Style</button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="sortByCategoryAndStyle()">Sort By Category and per Style</button>
                                    </div>
                                    <div class="mb-2">
                                        <button class="btn btn-warning btn-sm" onclick="tagProductCategory()">Tag Product Category Per Item</button>
                                    </div>
                                    <div class="mb-2">
                                        <button class="btn btn-outline-primary btn-sm me-2" onclick="organizeByStyle()">Organize By Style</button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="sortByStyle()">Sort By Style</button>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-success w-100" onclick="startAISorting()">Start AI Sorting <span class="ms-2 text-muted small">€0.20 per item</span></button>
                                    </div>
                                    <div class="mb-3">
                                        <div class="fw-bold">Chat</div>
                                        <textarea class="form-control mb-2" id="ai-chat-question" rows="5" placeholder="make questions about variants i.e. sizes to apply to all"></textarea>
                                        <button class="btn btn-primary btn-sm" onclick="sendAIChat()">Send</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary btn-lg">Save & Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// --- Image Upload Logic (existing) ---
window.selectedImages = [];
function triggerImageInput() {
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
    plusBox.innerHTML = `<span class='fs-1 text-secondary'>+</span>`;
    plusBox.onclick = triggerImageInput;
    grid.appendChild(plusBox);
    document.getElementById('upload-images-btn').style.display = window.selectedImages.length ? 'inline-block' : 'none';
}
function removeImage(idx) {
    window.selectedImages.splice(idx, 1);
    renderImagePreviews();
}
function submitImages() {
    if (!window.selectedImages.length) return;
    alert(window.selectedImages.length + ' image(s) selected. (Placeholder for upload logic)');
}
window.onload = renderImagePreviews;
// --- Excel Upload Logic ---
document.getElementById('excel-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    document.getElementById('excel-file-name').textContent = file ? file.name : '';
});
// --- AI Assistant Placeholder Functions ---
function sortByStyle() { alert('Sort By Style (AI logic placeholder)'); }
function sortByCategoryAndStyle() { alert('Sort By Category and per Style (AI logic placeholder)'); }
function tagProductCategory() { alert('Tag Product Category Per Item (AI logic placeholder)'); }
function organizeByStyle() { alert('Organize By Style (AI logic placeholder)'); }
function startAISorting() { alert('Start AI Sorting (AI logic placeholder)'); }
function sendAIChat() {
    const question = document.getElementById('ai-chat-question').value.trim();
    if (question) {
        alert('AI Chat: ' + question + ' (AI logic placeholder)');
        document.getElementById('ai-chat-question').value = '';
    }
}
</script>
@endsection 