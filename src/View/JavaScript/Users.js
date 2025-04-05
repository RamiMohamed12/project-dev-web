        // Keep toggleUserFields separate
        function toggleUserFields() {
            const typeElement = document.getElementById('add_user_type'); if (!typeElement) return;
            const type = typeElement.value;
            const studentFields = document.getElementById('student_specific_fields');
            const piloteCommonFields = document.getElementById('pilote_specific_fields');
            if (studentFields) studentFields.style.display = 'none';
            if (piloteCommonFields) piloteCommonFields.style.display = 'none';
            if (type === 'student' || type === 'pilote') { if (piloteCommonFields) piloteCommonFields.style.display = 'block'; }
            if (type === 'student') { if (studentFields) studentFields.style.display = 'block'; }
            const dobInput = document.getElementById('add_dob'); const yearSelect = document.getElementById('add_year'); const schoolInput = document.getElementById('add_school');
            if (dobInput) dobInput.required = (type === 'student');
            if (yearSelect) yearSelect.required = (type === 'student');
            if (schoolInput) { /* schoolInput.required = (type === 'student'); // Optional */ }
             const locationInput = document.getElementById('add_location');
             const phoneInput = document.getElementById('add_phone');
             if (locationInput) locationInput.required = (type === 'pilote'); // Example
             if (phoneInput) phoneInput.required = (type === 'pilote'); // Example
        }
        // Note: Moved DOMContentLoaded listener to bottom script block

