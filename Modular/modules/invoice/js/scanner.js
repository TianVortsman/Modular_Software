// Assumes html5-qrcode is loaded via CDN in the scanner.php view
const apiUrl = '../api/last-scan.php';
const statusEl = document.getElementById('scan-status');
let lastScannedBarcode = null;

function onScanSuccess(decodedText) {
    lastScannedBarcode = decodedText;
    fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ barcode: decodedText })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            statusEl.textContent = `Scanned: ${decodedText}`;
            statusEl.className = 'success';
            // Show image upload section
            document.getElementById('image-upload-section').style.display = 'block';
        } else {
            statusEl.textContent = 'Error sending scan!';
            statusEl.className = 'error';
        }
    })
    .catch(() => {
        statusEl.textContent = 'Network error!';
        statusEl.className = 'error';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const html5QrcodeScanner = new Html5QrcodeScanner(
        'qr-reader', { fps: 10, qrbox: 250 }
    );
    html5QrcodeScanner.render(onScanSuccess);

    // Image upload logic
    const takePhotoBtn = document.getElementById('take-photo-btn');
    const photoInput = document.getElementById('product-photo-input');
    const previewImg = document.getElementById('preview');
    const uploadStatus = document.getElementById('upload-status');

    if (takePhotoBtn && photoInput) {
        takePhotoBtn.addEventListener('click', () => photoInput.click());
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            // Show preview
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewImg.src = ev.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
            // Upload to backend
            if (!lastScannedBarcode) {
                uploadStatus.textContent = 'Scan a barcode first!';
                uploadStatus.className = 'error';
                return;
            }
            const formData = new FormData();
            formData.append('image', file);
            formData.append('barcode', lastScannedBarcode);
            fetch('../api/products.php?action=upload_image', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(res => res.json())
            .then(data => {
                uploadStatus.textContent = data.message || (data.success ? 'Image uploaded!' : 'Upload failed');
                uploadStatus.className = data.success ? 'success' : 'error';
                // Notify opener (main app) to update image if upload succeeded
                if (data.success && window.opener && lastScannedBarcode) {
                    window.opener.postMessage({ type: 'product-image-updated', barcode: lastScannedBarcode, time: Date.now() }, '*');
                }
            })
            .catch(() => {
                uploadStatus.textContent = 'Network error!';
                uploadStatus.className = 'error';
            });
        });
    }
}); 