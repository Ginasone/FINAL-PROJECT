let memberCount = 0;

function addMemberInput() {
    const container = document.getElementById('memberContainer');
    const newMemberDiv = document.createElement('div');
    newMemberDiv.classList.add('member-inputs');
    newMemberDiv.innerHTML = `
        <h4>Member ${++memberCount}</h4>
        <div class="form-group">
            <label>Stage Name</label>
            <input type="text" name="stage_names[]">
        </div>
        <div class="form-group">
            <label>Real Name</label>
            <input type="text" name="real_names[]">
        </div>
        <div class="form-group">
            <label>Birth Date</label>
            <input type="date" name="birth_dates[]">
        </div>
        <div class="form-group">
            <label>Nationality</label>
            <input type="text" name="nationalities[]">
        </div>
        <div class="form-group">
            <label>Position</label>
            <input type="text" name="positions[]">
        </div>
    `;
    container.appendChild(newMemberDiv);
}

function toggleNewGroupSection() {
    const groupSelect = document.getElementById('group_id');
    const newGroupSection = document.getElementById('newGroupSection');

    if (groupSelect.value === 'new') {
        newGroupSection.style.display = 'block';
    } else {
        newGroupSection.style.display = 'none';
    }
}