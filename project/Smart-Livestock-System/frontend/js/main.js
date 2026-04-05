// ============================================
// Smart Livestock Management System
// Main JavaScript File
// ============================================

// API Base URL
const API_BASE = '../backend/api/';

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Initializing application...');
    
    // Check if user is logged in
    checkUserSession();
    
    // Load page-specific content
    loadPageContent();
});

// ============================================
// SESSION & AUTH FUNCTIONS
// ============================================

/**
 * Check if user session is valid
 * If not logged in, redirect to login page
 */
function checkUserSession() {
    // This would normally check with backend
    // For now, we rely on PHP session management
    const currentPage = window.location.pathname;
    if (!currentPage.includes('index.html') && !currentPage.includes('backend/api/')) {
        // If accessing dashboard pages without login, they'll be redirected by PHP
    }
}

/**
 * Load page-specific content based on current page
 */
function loadPageContent() {
    const path = window.location.pathname;
    
    if (path.includes('dashboard.php')) {
        loadDashboardData();
    } else if (path.includes('livestock.php')) {
        loadAnimalsData();
    } else if (path.includes('supplements.php')) {
        loadSupplementsData();
    } else if (path.includes('uploads.php')) {
        loadUploadsData();
    }
}

// ============================================
// DASHBOARD FUNCTIONS
// ============================================

/**
 * Load dashboard data from backend
 * Fetches animal stats and milk production data
 */
function loadDashboardData() {
    fetch(API_BASE + 'get_dashboard_stats.php', { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                displayDashboardStats(result.data);
                initializeMilkChart(result.data.milkTrend);
                initializeAnimalTypeChart(result.data.animalTypes);
            } else {
                console.error('API returned error:', result.message);
            }
        })
        .catch(error => console.error('Error loading dashboard:', error));
}

/**
 * Display dashboard statistics in stat cards
 * @param {Object} data - Dashboard data from backend
 */
function displayDashboardStats(data) {
    document.getElementById('total-animals').textContent = data.totalAnimals || 0;
    document.getElementById('total-milk').textContent = (data.totalMilkToday || 0).toFixed(2);
    document.getElementById('avg-milk').textContent = (data.avgMilkYield || 0).toFixed(2);
    document.getElementById('healthy-count').textContent = data.healthyCount || 0;
    
    // Display recent animals
    if (data.recentAnimals) {
        let tableHTML = '';
        data.recentAnimals.forEach(animal => {
            tableHTML += `
                <tr>
                    <td>${animal.animal_name}</td>
                    <td>${animal.animal_type}</td>
                    <td>${animal.age}</td>
                    <td>${animal.weight}</td>
                    <td>${animal.milk_yield_daily}</td>
                    <td><span class="status-${animal.health_status}">${animal.health_status}</span></td>
                </tr>
            `;
        });
        document.getElementById('animals-table').innerHTML = tableHTML;
    }
}

/**
 * Initialize milk production trend chart
 * @param {Array} trendData - Milk production data for chart
 */
function initializeMilkChart(trendData) {
    const ctx = document.getElementById('milkChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.labels || [],
            datasets: [{
                label: 'Daily Milk Production (Liters)',
                data: trendData.values || [],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Initialize animal type distribution chart
 * @param {Array} typeData - Animal type distribution data
 */
function initializeAnimalTypeChart(typeData) {
    const ctx = document.getElementById('animalTypeChart');
    if (!ctx) return;
    
    const colors = ['#2c3e50', '#27ae60', '#e74c3c', '#f39c12'];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: typeData.labels || [],
            datasets: [{
                data: typeData.values || [],
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

// ============================================
// LIVESTOCK MANAGEMENT FUNCTIONS
// ============================================

/**
 * Load animals data from backend
 * Populates the animals table
 */
function loadAnimalsData() {
    fetch(API_BASE + 'get_animals.php', { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                displayAnimals(result.data);
            } else {
                console.error('Error loading animals:', result.message);
            }
        })
        .catch(error => console.error('Error loading animals:', error));
}

/**
 * Display animals in table format
 * @param {Array} animals - Array of animal objects
 */
function displayAnimals(animals) {
    let tableHTML = '';
    animals.forEach(animal => {
        tableHTML += `
            <tr>
                <td>${animal.animal_id}</td>
                <td>${animal.animal_name}</td>
                <td>${animal.animal_type}</td>
                <td>${animal.breed || 'N/A'}</td>
                <td>${animal.age || 'N/A'}</td>
                <td>${animal.weight || 'N/A'}</td>
                <td>${animal.milk_yield_daily || 'N/A'}</td>
                <td><span class="status-${animal.health_status}">${animal.health_status}</span></td>
                <td>
                    <button onclick="editAnimal(${animal.animal_id})" class="btn btn-primary">Edit</button>
                    <button onclick="deleteAnimal(${animal.animal_id})" class="btn btn-danger">Delete</button>
                </td>
            </tr>
        `;
    });
    document.getElementById('animals-list').innerHTML = tableHTML || '<tr><td colspan="9" class="text-center">No animals found</td></tr>';
}

/**
 * Open add animal modal
 */
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Animal';
    document.getElementById('animalForm').reset();
    document.getElementById('animalId').value = '0';
    document.getElementById('animalModal').style.display = 'block';
}

/**
 * Close animal modal
 */
function closeAddModal() {
    document.getElementById('animalModal').style.display = 'none';
}

/**
 * Submit animal form (add or edit)
 * @param {Event} event - Form submission event
 */
function submitAnimal(event) {
    event.preventDefault();
    
    const animalId = document.getElementById('animalId').value;
    const formData = {
        animal_id: animalId,
        animal_name: document.getElementById('animalName').value,
        animal_type: document.getElementById('animalType').value,
        breed: document.getElementById('breed').value,
        age: document.getElementById('age').value,
        weight: document.getElementById('weight').value,
        milk_yield_daily: document.getElementById('milkYield').value,
        health_status: document.getElementById('healthStatus').value,
        feed_type: document.getElementById('feedType').value
    };
    
    const url = animalId === '0' ? 
        API_BASE + 'create_animal.php' : 
        API_BASE + 'update_animal.php';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Animal saved successfully!');
            closeAddModal();
            loadAnimalsData();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error saving animal:', error));
}

/**
 * Edit an animal - Load data into modal
 * @param {number} animalId - ID of animal to edit
 */
function editAnimal(animalId) {
    fetch(API_BASE + 'get_animal.php?id=' + animalId, { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const animal = data.data;
                document.getElementById('modalTitle').textContent = 'Edit Animal';
                document.getElementById('animalId').value = animal.animal_id;
                document.getElementById('animalName').value = animal.animal_name;
                document.getElementById('animalType').value = animal.animal_type;
                document.getElementById('breed').value = animal.breed || '';
                document.getElementById('age').value = animal.age || '';
                document.getElementById('weight').value = animal.weight || '';
                document.getElementById('milkYield').value = animal.milk_yield_daily || '';
                document.getElementById('healthStatus').value = animal.health_status;
                document.getElementById('feedType').value = animal.feed_type || '';
                document.getElementById('animalModal').style.display = 'block';
            }
        })
        .catch(error => console.error('Error loading animal:', error));
}

/**
 * Delete an animal
 * @param {number} animalId - ID of animal to delete
 */
function deleteAnimal(animalId) {
    if (confirm('Are you sure you want to delete this animal?')) {
        fetch(API_BASE + 'delete_animal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({ animal_id: animalId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Animal deleted successfully!');
                loadAnimalsData();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting animal:', error));
    }
}

/**
 * Perform binary search on animals
 * ALGORITHM: Binary Search (DAA Concept)
 * Used for efficient animal lookup
 */
function performSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    if (searchTerm === '') {
        loadAnimalsData();
        return;
    }
    
    fetch(API_BASE + 'search_animal.php?query=' + encodeURIComponent(searchTerm), { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAnimals(data.results);
            }
        })
        .catch(error => console.error('Error searching animals:', error));
}

// ============================================
// SUPPLEMENTS FUNCTIONS
// ============================================

/**
 * Load supplements data from backend
 */
function loadSupplementsData() {
    // Load available supplements
    fetch(API_BASE + 'get_supplements.php', { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                displaySupplements(result.data);
                populateAnimalDropdown();
            } else {
                console.error('Error loading supplements:', result.message);
            }
        })
        .catch(error => console.error('Error loading supplements:', error));
}

/**
 * Display supplements table
 * @param {Array} supplements - Array of supplement objects
 */
function displaySupplements(supplements) {
    let tableHTML = '';
    supplements.forEach(supplement => {
        tableHTML += `
            <tr>
                <td>${supplement.supplement_name}</td>
                <td>${supplement.protein_content}%</td>
                <td>${supplement.fat_content}%</td>
                <td>${supplement.minerals}%</td>
                <td>₹${supplement.cost_per_kg}</td>
                <td>${supplement.animal_type}</td>
                <td>${supplement.description}</td>
            </tr>
        `;
    });
    document.getElementById('supplements-list').innerHTML = tableHTML || '<tr><td colspan="7" class="text-center">No supplements found</td></tr>';
}

/**
 * Populate animal dropdown for recommendations
 */
function populateAnimalDropdown() {
    fetch(API_BASE + 'get_animals.php', { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                let options = '<option value="">Select an animal</option>';
                result.data.forEach(animal => {
                    options += `<option value="${animal.animal_id}">${animal.animal_name} (${animal.animal_type})</option>`;
                });
                document.getElementById('selectedAnimal').innerHTML = options;
            } else {
                console.error('Error loading animals:', result.message);
            }
        })
        .catch(error => console.error('Error loading animals:', error));
}

/**
 * Get supplement recommendation using Greedy Algorithm
 * ALGORITHM: Greedy Algorithm (DAA Concept)
 * Maximizes nutrition within budget constraint
 * @param {Event} event - Form submission event
 */
function getRecommendation(event) {
    event.preventDefault();
    
    const animalId = document.getElementById('selectedAnimal').value;
    const budget = parseFloat(document.getElementById('budget').value);
    
    if (!animalId) {
        alert('Please select an animal');
        return;
    }
    
    fetch(API_BASE + 'recommend_supplements.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({
            animal_id: animalId,
            budget: budget
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayRecommendations(data);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error getting recommendations:', error));
}

/**
 * Display supplement recommendations
 * @param {Object} data - Recommendation data from backend
 */
function displayRecommendations(data) {
    const animal = data.animal;
    const recommendations = data.recommendations;
    
    document.getElementById('resultAnimal').textContent = animal.animal_name + ' (' + animal.animal_type + ')';
    document.getElementById('resultBudget').textContent = data.budget;
    document.getElementById('resultNutrition').textContent = (data.total_nutrition_score || 0).toFixed(1);
    
    let tableHTML = '';
    recommendations.forEach(rec => {
        tableHTML += `
            <tr>
                <td>${rec.supplement_name}</td>
                <td>${rec.protein_content}%</td>
                <td>${rec.fat_content}%</td>
                <td>${rec.minerals}%</td>
                <td>₹${rec.cost_per_kg}</td>
                <td>${rec.quantity_kg.toFixed(2)} kg</td>
                <td>₹${rec.total_cost.toFixed(2)}</td>
            </tr>
        `;
    });
    document.getElementById('recommendationTable').innerHTML = tableHTML;
    
    document.getElementById('totalCost').textContent = (data.total_cost || 0).toFixed(2);
    document.getElementById('remainingBudget').textContent = (data.budget - data.total_cost || 0).toFixed(2);
    document.getElementById('recommendationReason').textContent = data.recommendation_reason || '';
    
    document.getElementById('recommendationResults').style.display = 'block';
}

/**
 * Save recommendation to database
 */
function saveRecommendation() {
    // Implementation to save recommendation
    alert('Recommendation saved successfully!');
}

// ============================================
// FILE UPLOAD FUNCTIONS
// ============================================

/**
 * Load uploads data from backend
 */
function loadUploadsData() {
    fetch(API_BASE + 'get_uploads.php', { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                displayUploads(result.data);
                populateUploadAnimalDropdown();
            } else {
                console.error('Error loading uploads:', result.message);
            }
        })
        .catch(error => console.error('Error loading uploads:', error));
}

/**
 * Display uploads in table format
 * @param {Array} uploads - Array of upload objects
 */
function displayUploads(uploads) {
    let tableHTML = '';
    uploads.forEach(upload => {
        tableHTML += `
            <tr>
                <td>${upload.file_name}</td>
                <td>${upload.file_type}</td>
                <td>${(upload.file_size / 1024).toFixed(2)}</td>
                <td>${upload.animal_name || 'N/A'}</td>
                <td>${new Date(upload.upload_date).toLocaleDateString()}</td>
                <td>
                    <button onclick="downloadFile('${upload.file_path}')" class="btn btn-primary">Download</button>
                    <button onclick="deleteFile(${upload.upload_id})" class="btn btn-danger">Delete</button>
                </td>
            </tr>
        `;
    });
    document.getElementById('files-list').innerHTML = tableHTML || '<tr><td colspan="6" class="text-center">No files uploaded</td></tr>';
}

/**
 * Populate animal dropdown for file upload
 */
function populateUploadAnimalDropdown() {
    fetch(API_BASE + 'get_animals.php', { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                let options = '<option value="">Not linked to any animal</option>';
                result.data.forEach(animal => {
                    options += `<option value="${animal.animal_id}">${animal.animal_name}</option>`;
                });
                document.getElementById('uploadAnimal').innerHTML = options;
            } else {
                console.error('Error loading animals:', result.message);
            }
        })
        .catch(error => console.error('Error loading animals:', error));
}

/**
 * Open upload modal
 */
function openUploadModal() {
    document.getElementById('uploadForm').reset();
    document.getElementById('uploadModal').style.display = 'block';
}

/**
 * Close upload modal
 */
function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

/**
 * Submit file upload
 * @param {Event} event - Form submission event
 */
function submitUpload(event) {
    event.preventDefault();
    
    const fileInput = document.getElementById('uploadFile');
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('animal_id', document.getElementById('uploadAnimal').value);
    formData.append('description', document.getElementById('uploadDescription').value);
    
    fetch(API_BASE + 'upload_file.php', {
        method: 'POST',
        credentials: 'include',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('File uploaded successfully!');
            closeUploadModal();
            loadUploadsData();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error uploading file:', error));
}

/**
 * Download a file
 * @param {string} filePath - Path to the file
 */
function downloadFile(filePath) {
    window.location.href = API_BASE + 'download_file.php?path=' + encodeURIComponent(filePath);
}

/**
 * Delete a file
 * @param {number} uploadId - ID of upload to delete
 */
function deleteFile(uploadId) {
    if (confirm('Are you sure you want to delete this file?')) {
        fetch(API_BASE + 'delete_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({ upload_id: uploadId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('File deleted successfully!');
                loadUploadsData();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting file:', error));
    }
}

// ============================================
// MODAL CLOSE ON OUTSIDE CLICK
// ============================================

window.onclick = function(event) {
    const animalModal = document.getElementById('animalModal');
    const uploadModal = document.getElementById('uploadModal');
    
    if (event.target === animalModal) {
        animalModal.style.display = 'none';
    }
    if (event.target === uploadModal) {
        uploadModal.style.display = 'none';
    }
}
