document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bmiForm');
    const inputs = {
        name: document.getElementById('name'),
        age: document.getElementById('age'),
        address: document.getElementById('address'),
        contact: document.getElementById('contact'),
        weight: document.getElementById('weight'),
        height: document.getElementById('height')
    };

    const errorElements = {
        name: document.getElementById('nameError'),
        age: document.getElementById('ageError'),
        address: document.getElementById('addressError'),
        contact: document.getElementById('contactError'),
        weight: document.getElementById('weightError'),
        height: document.getElementById('heightError')
    };

    // Display Sri Lankan time in the form
    function displaySriLankanTime() {
        const timeElement = document.createElement('div');
        timeElement.id = 'sriLankanTime';
        timeElement.style.cssText = `
            text-align: center;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #4b6cb7;
            padding: 8px;
            background: #f0f5ff;
            border-radius: 5px;
            border: 1px solid #d1e0ff;
        `;
        
        function updateTime() {
            const now = new Date();
            // Sri Lanka is UTC+5:30
            const sriLankaOffset = 5.5 * 60 * 60 * 1000; // 5.5 hours in milliseconds
            const sriLankaTime = new Date(now.getTime() + sriLankaOffset);
            
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            
            const formattedTime = sriLankaTime.toLocaleDateString('en-US', options);
            timeElement.innerHTML = `<i class="fas fa-clock"></i> Sri Lankan Time (IST): ${formattedTime}`;
        }
        
        updateTime();
        setInterval(updateTime, 1000);
        
        // Insert after form info
        const formInfo = document.querySelector('.form-info');
        if (formInfo) {
            formInfo.parentNode.insertBefore(timeElement, formInfo.nextSibling);
        }
    }

    // Call the function to display Sri Lankan time
    displaySriLankanTime();

    // Real-time validation
    Object.keys(inputs).forEach(key => {
        inputs[key].addEventListener('blur', validateField);
        inputs[key].addEventListener('input', clearError);
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Validate all fields
        Object.keys(inputs).forEach(key => {
            if (!validateField.call(inputs[key])) {
                isValid = false;
            }
        });

        if (isValid) {
            // Show loading state
            const submitBtn = form.querySelector('.submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';
            submitBtn.disabled = true;

            // Submit the form after a brief delay to show loading state
            setTimeout(() => {
                form.submit();
            }, 500);
        } else {
            // Scroll to first error
            const firstError = document.querySelector('.error-message:not(:empty)');
            if (firstError) {
                firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.parentElement.querySelector('input').focus();
            }
        }
    });

    // Form reset
    form.addEventListener('reset', function() {
        Object.keys(errorElements).forEach(key => {
            errorElements[key].textContent = '';
            inputs[key].classList.remove('error');
        });
    });

    // Field validation functions
    function validateField() {
        const field = this;
        const fieldName = field.id;
        const value = field.value.trim();
        let error = '';

        switch(fieldName) {
            case 'name':
                if (value === '') {
                    error = 'Name is required';
                } else if (!/^[a-zA-Z\s]{2,50}$/.test(value)) {
                    error = 'Name must contain only letters and spaces (2-50 characters)';
                }
                break;

            case 'age':
                if (value === '') {
                    error = 'Age is required';
                } else if (!/^\d+$/.test(value)) {
                    error = 'Age must be a whole number';
                } else if (parseInt(value) < 1 || parseInt(value) > 120) {
                    error = 'Age must be between 1 and 120';
                }
                break;

            case 'address':
                if (value === '') {
                    error = 'Address is required';
                } else if (value.length < 5) {
                    error = 'Address must be at least 5 characters';
                }
                break;

            case 'contact':
                if (value === '') {
                    error = 'Contact number is required';
                } else if (!/^[\d\s\-\+\(\)]{10,20}$/.test(value)) {
                    error = 'Enter a valid phone number (10-20 digits)';
                }
                break;

            case 'weight':
                if (value === '') {
                    error = 'Weight is required';
                } else if (!/^\d+(\.\d+)?$/.test(value)) {
                    error = 'Enter a valid weight (e.g., 70.5)';
                } else if (parseFloat(value) < 1 || parseFloat(value) > 300) {
                    error = 'Weight must be between 1 and 300 kg';
                }
                break;

            case 'height':
                if (value === '') {
                    error = 'Height is required';
                } else if (!/^\d+(\.\d+)?$/.test(value)) {
                    error = 'Enter a valid height (e.g., 175.2)';
                } else if (parseFloat(value) < 50 || parseFloat(value) > 250) {
                    error = 'Height must be between 50 and 250 cm';
                }
                break;
        }

        // Update error display
        errorElements[fieldName].textContent = error;
        if (error) {
            field.classList.add('error');
            return false;
        } else {
            field.classList.remove('error');
            return true;
        }
    }

    function clearError() {
        const fieldName = this.id;
        errorElements[fieldName].textContent = '';
        this.classList.remove('error');
    }

    // Add input formatting for contact number
    inputs.contact.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length > 0) {
            if (value.length <= 3) {
                value = value;
            } else if (value.length <= 6) {
                value = `(${value.substring(0,3)}) ${value.substring(3)}`;
            } else {
                value = `(${value.substring(0,3)}) ${value.substring(3,6)}-${value.substring(6,10)}`;
            }
        }
        
        e.target.value = value;
    });

    // Add character counter for address
    inputs.address.addEventListener('input', function() {
        const charCount = this.value.length;
        const maxChars = 200;
        
        if (charCount > maxChars) {
            this.value = this.value.substring(0, maxChars);
            errorElements.address.textContent = `Maximum ${maxChars} characters allowed`;
            this.classList.add('error');
        } else if (charCount > 0) {
            errorElements.address.textContent = `${charCount}/${maxChars} characters`;
            this.classList.remove('error');
        }
    });
});