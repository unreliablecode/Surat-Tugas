document.addEventListener('DOMContentLoaded', function () {
    
    // --- Technician Form Logic (from before) ---
    const addTechnicianBtn = document.getElementById('add-technician-btn');
    const technicianList = document.getElementById('technician-list');
    const technicianTemplate = document.getElementById('technician-template');

    // Add initial technician row on page load
    if (technicianList) {
        addTechnicianRow();
    }

    if (addTechnicianBtn) {
        addTechnicianBtn.addEventListener('click', addTechnicianRow);
    }

    function addTechnicianRow() {
        if (!technicianTemplate) return;
        const newRow = technicianTemplate.content.cloneNode(true);
        technicianList.appendChild(newRow);
        setupRowListeners(technicianList.lastElementChild);
    }

    function setupRowListeners(row) {
        // Remove button listener
        const removeBtn = row.querySelector('.remove-technician-btn');
        removeBtn.addEventListener('click', () => {
            if (technicianList.children.length > 1) {
                row.remove();
            } else {
                Swal.fire('Cannot Remove', 'At least one technician is required.', 'warning');
            }
        });

        // Autocomplete listener
        const nameInput = row.querySelector('.tech-name-input');
        const resultsContainer = row.querySelector('.autocomplete-results');

        nameInput.addEventListener('keyup', async (e) => {
            const query = e.target.value;
            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';
                return;
            }

            try {
                const response = await fetch(`../get_technicians.php?q=${query}`);
                const technicians = await response.json();
                
                resultsContainer.innerHTML = '';
                if (technicians.length > 0) {
                    technicians.forEach(tech => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-gray-200 cursor-pointer';
                        div.textContent = `${tech.name} (${tech.nik})`;
                        div.addEventListener('click', () => {
                            const parentRow = nameInput.closest('.technician-row');
                            parentRow.querySelector('[name="teknisi_nama[]"]').value = tech.name;
                            parentRow.querySelector('[name="teknisi_nik[]"]').value = tech.nik;
                            parentRow.querySelector('[name="teknisi_posisi[]"]').value = tech.position;
                            parentRow.querySelector('[name="teknisi_psa[]"]').value = tech.psa;
                            resultsContainer.style.display = 'none';
                        });
                        resultsContainer.appendChild(div);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.style.display = 'none';
                }
            } catch (error) {
                console.error('Error fetching technicians:', error);
            }
        });
        
        document.addEventListener('click', (e) => {
            if (resultsContainer && !resultsContainer.contains(e.target)) {
                 resultsContainer.style.display = 'none';
            }
        });
    }

    if (technicianList && technicianList.firstElementChild) {
        setupRowListeners(technicianList.firstElementChild);
    }
    // --- End of Technician Form Logic ---


    // --- NEW: Preview Modal Logic ---
    const previewModal = document.getElementById('previewModal');
    const closePreviewModalBtn = document.getElementById('closePreviewModal');
    const previewFrame = document.getElementById('previewFrame');

    if (previewModal && closePreviewModalBtn && previewFrame) {
        
        // Use event delegation to catch clicks on any preview button
        document.body.addEventListener('click', function(e) {
            // Find the closest .preview-btn ancestor
            const button = e.target.closest('.preview-btn');
            
            if (button) {
                const requestId = button.dataset.id;
                
                if (requestId) {
                    // Set the iframe source to our new preview generator script
                    // We use BASE_URL from the header, but relative path is safer
                    previewFrame.src = `../get_request_preview.php?id=${requestId}`;
                    // Show the modal
                    previewModal.classList.remove('hidden');
                }
            }
        });

        // Function to close the modal
        const closeModal = () => {
            previewModal.classList.add('hidden');
            // Clear the iframe to stop any loading and prevent old content flash
            previewFrame.src = 'about:blank'; 
        };

        // Close button click
        closePreviewModalBtn.addEventListener('click', closeModal);

        // Click on the dark overlay to close
        previewModal.addEventListener('click', function(e) {
            if (e.target === previewModal) {
                closeModal();
            }
        });
    }
    // --- End of Preview Modal Logic ---
});
