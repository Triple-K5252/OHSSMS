// Show class assignment options only for selected subjects
function updateClassAssignments() {
    let selected = Array.from(document.getElementById('subjects').selectedOptions).map(o => o.value);
    document.querySelectorAll('.subject-classes').forEach(div => {
        div.classList.add('hidden');
    });
    selected.forEach(subj => {
        let el = document.getElementById('classes-for-' + subj);
        if (el) el.classList.remove('hidden');
    });
}

document.addEventListener('DOMContentLoaded', updateClassAssignments);