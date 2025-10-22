<?php
$page_title = 'New Assignment Request';
include_once '../partials/header.php';
include_once '../partials/sidebar.php';

// Generate the next request number
$year = date('Y');
$month = date('m'); // Get current month
$stmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE YEAR(created_at) = ?");
$stmt->execute([$year]);
$count = $stmt->fetchColumn() + 1;

// Use the correct current month and year for the default number
$request_number = sprintf("%03d/UM.000/TA-0500/%s-%s", $count, $month, $year);
?>

<h2 class="text-3xl font-semibold text-gray-800">Create New Assignment Letter (Surat Tugas)</h2>

<div class="mt-8 bg-white p-8 rounded-lg shadow-md">
    <form action="../actions/create_request.php" method="POST" id="requestForm">
        <!-- Letter Head -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="request_number" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                <!-- MODIFIED: Removed 'readonly' attribute -->
                <input type="text" name="request_number" id="request_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="<?= htmlspecialchars($request_number); ?>">
                
                <!-- NEW: Checkbox for current month/year -->
                <div class="mt-2 flex items-center">
                    <input type="checkbox" id="set_current_date" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="set_current_date" class="ml-2 block text-sm text-gray-900">Set month and year as current</label>
                </div>
            </div>
            <div>
                <label for="letter_date" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                <input type="date" name="letter_date" id="letter_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="<?= date('Y-m-d'); ?>" required>
            </div>
        </div>

        <!-- Purpose of Assignment -->
        <div class="mb-6">
            <label for="purpose_text" class="block text-sm font-medium text-gray-700">Tugas / Keperluan</label>
            <textarea name="purpose_text" id="purpose_text" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: Untuk melaksanakan Sertifikasi teknisi B2B dan Kunjungan ke sekolah-sekolah..." required></textarea>
        </div>

        <!-- Technicians Section -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Data Teknisi</h3>
            <div id="technician-list" class="space-y-4">
                <!-- Technician rows will be added here by JS -->
            </div>
            <button type="button" id="add-technician-btn" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Teknisi
            </button>
        </div>

        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                <i class="fa-solid fa-paper-plane mr-2"></i> Submit Request
            </button>
        </div>
    </form>
</div>

<!-- Template for new technician row (hidden) -->
<template id="technician-template">
    <div class="technician-row grid grid-cols-1 md:grid-cols-9 gap-4 items-center p-4 bg-gray-50 rounded-lg border">
        <div class="md:col-span-2 relative">
            <label class="block text-xs font-medium text-gray-600">Nama Teknisi</label>
            <input type="text" name="teknisi_nama[]" class="tech-name-input mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
            <div class="autocomplete-results absolute z-10 w-full bg-white border rounded-md mt-1 shadow-lg" style="display: none;"></div>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600">NIK</label>
            <input type="text" name="teknisi_nik[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600">Posisi</label>
            <input type="text" name="teknisi_posisi[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600">PSA</label>
            <input type="text" name="teknisi_psa[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
        </div>
        <div class="md:col-span-1 flex items-end justify-center">
            <button type="button" class="remove-technician-btn bg-red-500 hover:bg-red-600 text-white p-2 rounded-full h-10 w-10 flex items-center justify-center">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>
    </div>
</template>

<!-- NEW: JavaScript for the checkbox -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkbox = document.getElementById('set_current_date');
    const numberInput = document.getElementById('request_number');

    checkbox.addEventListener('change', () => {
        if (checkbox.checked) {
            const now = new Date();
            // Format to 'MM' (e.g., '01', '02', '12')
            const month = String(now.getMonth() + 1).padStart(2, '0'); 
            const year = now.getFullYear();
            const newDateSuffix = `${month}-${year}`;
            
            let currentValue = numberInput.value;
            
            // Regex to find and replace the date part (e.g., /XX-XXXX at the end)
            // It looks for a slash, two digits, a hyphen, and four digits, at the end of the string.
            const regex = /\/\d{2}-\d{4}$/;
            
            if (regex.test(currentValue)) {
                // If it finds the pattern, replace it
                numberInput.value = currentValue.replace(regex, `/${newDateSuffix}`);
            } else {
                // If it doesn't find the pattern, check if the last char is a slash
                if (currentValue.slice(-1) !== '/') {
                    currentValue += '/';
                }
                // Append the new date suffix
                numberInput.value += newDateSuffix;
            }
        }
    });
});
</script>

<?php include_once '../partials/footer.php'; ?>
