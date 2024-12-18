function toggleGroupInput() {
    var groupSelect = document.getElementById('group');
    var newGroupField = document.getElementById('new-group-container');
    var newGroupInput = document.getElementById('new_group_name');
    
    if (groupSelect.value === 'new') {
        newGroupField.style.display = 'block';
        newGroupInput.required = true;
    } else {
        newGroupField.style.display = 'none';
        newGroupInput.required = false;
    }
}

window.onload = function() {
    toggleGroupInput();
};
