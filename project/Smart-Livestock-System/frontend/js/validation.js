// ============================================
// Smart Livestock Management System
// Form Validation JavaScript
// ============================================

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean} - True if valid email
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate password strength
 * @param {string} password - Password to validate
 * @returns {Object} - Object with isValid and message
 */
function validatePassword(password) {
    let isValid = true;
    let messages = [];
    
    if (password.length < 8) {
        isValid = false;
        messages.push('Password must be at least 8 characters long');
    }
    
    if (!/[A-Z]/.test(password)) {
        isValid = false;
        messages.push('Password must contain an uppercase letter');
    }
    
    if (!/[a-z]/.test(password)) {
        isValid = false;
        messages.push('Password must contain a lowercase letter');
    }
    
    if (!/[0-9]/.test(password)) {
        isValid = false;
        messages.push('Password must contain a number');
    }
    
    return {
        isValid: isValid,
        message: messages.join(', ')
    };
}

/**
 * Validate phone number format
 * @param {string} phone - Phone number to validate
 * @returns {boolean} - True if valid phone
 */
function validatePhone(phone) {
    const phoneRegex = /^[0-9]{10}$/;
    return phoneRegex.test(phone.replace(/[\s\-()]/g, ''));
}

/**
 * Validate numeric input
 * @param {string} value - Value to validate
 * @returns {boolean} - True if numeric
 */
function validateNumeric(value) {
    return /^[0-9]+(\.[0-9]{1,2})?$/.test(value);
}

/**
 * Validate file size
 * @param {number} fileSizeBytes - File size in bytes
 * @param {number} maxSizeMB - Maximum size in MB
 * @returns {boolean} - True if file size is acceptable
 */
function validateFileSize(fileSizeBytes, maxSizeMB = 5) {
    const maxSizeBytes = maxSizeMB * 1024 * 1024;
    return fileSizeBytes <= maxSizeBytes;
}

/**
 * Validate file type
 * @param {string} fileName - Name of file
 * @param {Array} allowedExtensions - Array of allowed extensions
 * @returns {boolean} - True if file type is allowed
 */
function validateFileType(fileName, allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']) {
    const extension = fileName.split('.').pop().toLowerCase();
    return allowedExtensions.includes(extension);
}

/**
 * Validate animal form
 * @returns {Object} - Validation result with isValid and errors
 */
function validateAnimalForm() {
    const errors = [];
    
    const animalName = document.getElementById('animalName').value.trim();
    if (!animalName) {
        errors.push('Animal name is required');
    }
    
    const animalType = document.getElementById('animalType').value;
    if (!animalType) {
        errors.push('Animal type is required');
    }
    
    const age = document.getElementById('age').value;
    if (age && !validateNumeric(age)) {
        errors.push('Age must be a valid number');
    }
    
    const weight = document.getElementById('weight').value;
    if (weight && !validateNumeric(weight)) {
        errors.push('Weight must be a valid number');
    }
    
    const milkYield = document.getElementById('milkYield').value;
    if (milkYield && !validateNumeric(milkYield)) {
        errors.push('Milk yield must be a valid number');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

/**
 * Validate recommendation form
 * @returns {Object} - Validation result
 */
function validateRecommendationForm() {
    const errors = [];
    
    const animalId = document.getElementById('selectedAnimal').value;
    if (!animalId) {
        errors.push('Please select an animal');
    }
    
    const budget = document.getElementById('budget').value;
    if (!budget || !validateNumeric(budget)) {
        errors.push('Please enter a valid budget');
    }
    
    if (budget && budget < 100) {
        errors.push('Budget must be at least ₹100');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

/**
 * Display form errors
 * @param {Array} errors - Array of error messages
 */
function displayFormErrors(errors) {
    if (errors.length > 0) {
        const errorMessage = errors.join('\n');
        alert('Please fix the following errors:\n\n' + errorMessage);
    }
}

/**
 * Show form success message
 * @param {string} message - Success message
 */
function showSuccessMessage(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #27ae60;
        color: white;
        padding: 1rem;
        border-radius: 5px;
        z-index: 2000;
        animation: slideIn 0.3s;
    `;
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        successDiv.remove();
    }, 3000);
}

/**
 * Show form error message
 * @param {string} message - Error message
 */
function showErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #e74c3c;
        color: white;
        padding: 1rem;
        border-radius: 5px;
        z-index: 2000;
        animation: slideIn 0.3s;
    `;
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}
