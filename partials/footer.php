        </div>
    </div>
</main>
</div>

<!-- NEW: Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden">
    <!-- Modal content -->
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col">
        <!-- Modal header -->
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Preview Surat Tugas</h3>
            <button id="closePreviewModal" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
        </div>
        <!-- Modal body (iframe) -->
        <div class="flex-1 p-0">
            <!-- Iframe will be loaded with the preview content -->
            <iframe id="previewFrame" src="about:blank" class="w-full h-full border-0"></iframe>
        </div>
    </div>
</div>
<!-- End of Modal -->

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
    // Handle session messages with SweetAlert
    const params = new URLSearchParams(window.location.search);
    const messageContainer = document.getElementById('message-container');

    if (params.has('error')) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: decodeURIComponent(params.get('error')),
        });
    }
    if (params.has('success')) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: decodeURIComponent(params.get('success')),
        });
    }
    // Clean URL after showing alert
    if (params.has('error') || params.has('success')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
</body>
</html>
