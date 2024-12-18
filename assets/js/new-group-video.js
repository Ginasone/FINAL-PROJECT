document.addEventListener('DOMContentLoaded', function() {
    const existingGroupSelect = document.getElementById('existing_group');
    const newGroupFields = document.getElementById('new_group_fields');

    existingGroupSelect.addEventListener('change', function() {
        if (this.value === 'new') {
            newGroupFields.style.display = 'block';
            // Make new group fields required
            document.getElementById('new_group_name').setAttribute('required', 'required');
            document.getElementById('debut_date').setAttribute('required', 'required');
            document.getElementById('agency').setAttribute('required', 'required');
        } else {
            newGroupFields.style.display = 'none';
            // Remove required attribute from new group fields
            document.getElementById('new_group_name').removeAttribute('required');
            document.getElementById('debut_date').removeAttribute('required');
            document.getElementById('agency').removeAttribute('required');
        }
    });
});