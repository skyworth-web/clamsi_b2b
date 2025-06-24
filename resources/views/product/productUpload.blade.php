@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="mb-4">Clamsi Chat-Based Product Upload</h3>
                    <div id="chat-container" class="mb-4" style="min-height:350px;">
                        <!-- Chat messages will be appended here -->
                    </div>
                    <div id="chat-input-area">
                        <!-- Dynamic input area -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let chatStep = 1;
let batchId = null;
let batchLabel = '';
let uploadedImages = [];

function appendBotMessage(message) {
    const chat = document.getElementById('chat-container');
    const msg = document.createElement('div');
    msg.className = 'mb-3';
    msg.innerHTML = `<div class='d-flex'><div class='bg-light p-3 rounded shadow-sm'><strong>Bot:</strong> ${message}</div></div>`;
    chat.appendChild(msg);
    chat.scrollTop = chat.scrollHeight;
}
function appendUserMessage(message) {
    const chat = document.getElementById('chat-container');
    const msg = document.createElement('div');
    msg.className = 'mb-3 text-end';
    msg.innerHTML = `<div class='d-inline-block bg-primary text-white p-3 rounded shadow-sm'><strong>You:</strong> ${message}</div>`;
    chat.appendChild(msg);
    chat.scrollTop = chat.scrollHeight;
}
function setInputArea(html) {
    document.getElementById('chat-input-area').innerHTML = html;
}
function startStep1() {
    appendBotMessage('Welcome! Ready to upload products. What would you like to name this upload batch?');
    setInputArea(`
        <form id='batch-label-form' onsubmit='event.preventDefault(); submitBatchLabel();'>
            <input type='text' class='form-control mb-2' id='batch-label-input' placeholder='e.g. SS25 June Collection' required />
            <button class='btn btn-primary'>Continue</button>
        </form>
    `);
}
function submitBatchLabel() {
    const label = document.getElementById('batch-label-input').value.trim();
    if (!label) return;
    appendUserMessage(label);
    batchLabel = label;
    setInputArea('<div class="text-muted">Saving batch...</div>');
    // TODO: Replace with real API call
    setTimeout(() => {
        batchId = 'BATCH123'; // Placeholder
        startStep2();
    }, 700);
}
function startStep2() {
    chatStep = 2;
    appendBotMessage('Great! Please upload your product images.');
    setInputArea(`
        <div id='image-upload-section'>
            <div class='mb-2'><strong>Product Images</strong></div>
            <div id='image-preview-grid' class='d-flex flex-wrap gap-3 mb-3'></div>
            <input type='file' id='image-files' class='d-none' accept='image/*' multiple />
            <button type='button' class='btn btn-outline-primary mb-2' onclick='triggerImageInput()'>
                <i class='bi bi-upload'></i> Add Images
            </button>
            <button id='upload-images-btn' class='btn btn-success' onclick='submitImages()' style='display:none;'>Upload Images</button>
        </div>
    `);
    // Reset previews and input
    window.selectedImages = [];
    renderImagePreviews();
    document.getElementById('image-files').addEventListener('change', handleImageSelection);
}
function triggerImageInput() {
    document.getElementById('image-files').click();
}
function handleImageSelection(e) {
    const files = Array.from(e.target.files);
    // Add new files, avoiding duplicates by name
    files.forEach(f => {
        if (!window.selectedImages.some(img => img.name === f.name && img.size === f.size)) {
            window.selectedImages.push(f);
        }
    });
    renderImagePreviews();
    // Reset input so same file can be re-added if removed
    e.target.value = '';
}
function renderImagePreviews() {
    const grid = document.getElementById('image-preview-grid');
    grid.innerHTML = '';
    // Show previews
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
    // Show upload button only if images selected
    document.getElementById('upload-images-btn').style.display = window.selectedImages.length ? 'inline-block' : 'none';
}
function removeImage(idx) {
    window.selectedImages.splice(idx, 1);
    renderImagePreviews();
}
function submitImages() {
    if (!window.selectedImages.length) return;
    appendUserMessage(`${window.selectedImages.length} image(s) selected.`);
    setInputArea('<div class="text-muted">Uploading images...</div>');
    // TODO: Replace with real API call
    setTimeout(() => {
        uploadedImages = window.selectedImages.map(f => f.name);
        appendBotMessage('Images uploaded! (Next steps coming soon...)');
        setInputArea('<div class="text-muted">Further steps will appear here.</div>');
    }, 1000);
}
// On page load, start the chat flow
window.onload = startStep1;
</script>
@endsection 