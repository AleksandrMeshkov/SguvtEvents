function toggleGeneral() {
    const chk = document.getElementById('is_general');
    const sel = document.getElementById('institute_id');
    if (chk.checked) {
        sel.disabled = true;
        sel.required = false;
        sel.style.backgroundColor = '#eee';
    } else {
        sel.disabled = false;
        sel.required = true;
        sel.style.backgroundColor = '#fff';
    }
}

let partCounter = 0;
function addParticipantRow() {
    partCounter++;
    const container = document.getElementById('participants_container');
    const row = document.createElement('div');
    row.className = 'participant-card';
    row.id = 'part_' + partCounter;
    row.innerHTML = `
        <button type="button" class="btn-delete" onclick="document.getElementById('part_${partCounter}').remove()">✖ Удалить</button>
        <div class="form-grid" style="margin-right: 120px;">
            <div class="form-group"><label>Студент или Группа:</label><input type="text" name="participant_info[]" class="form-control" required></div>
            <div class="form-group"><label>Роль:</label><select name="role_in_event[]" class="form-control"><option value="Участник">Участник</option><option value="Победитель">Победитель</option><option value="Призер">Призер</option><option value="Волонтер">Волонтер</option></select></div>
            <div class="form-group" style="grid-column: span 2;"><label>Награда / Достижение:</label><input type="text" name="award_details[]" class="form-control"></div>
        </div>
    `;
    container.appendChild(row);
}

function toggleCustomLocation() {
    const sel = document.getElementById('location_select');
    const cust = document.getElementById('location_custom');
    if (!sel || !cust) return;
    if (sel.value === 'other') {
        cust.style.display = 'block';
        cust.disabled = false;
        cust.required = true;
        cust.focus();
    } else {
        cust.style.display = 'none';
        cust.disabled = true;
        cust.required = false;
        cust.value = '';
    }
}

// AJAX подгрузка факультетов по институту
function loadFaculties(instituteId, targetSelectId, selectedFacultyId) {
    const select = document.getElementById(targetSelectId);
    if (!select) return;
    
    select.innerHTML = '<option value="">-- Все --</option>';
    select.disabled = false;
    
    const url = instituteId ? 'api_faculties.php?institute_id=' + instituteId : 'api_faculties.php';
    
    fetch(url)
        .then(response => response.json())
        .then(faculties => {
            faculties.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.textContent = instituteId ? f.name : f.institute_short + ' — ' + f.name;
                if (selectedFacultyId && f.id == selectedFacultyId) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Ошибка загрузки факультетов:', err));
}

// Для форм добавления/редактирования (без "Все")
function loadFacultiesForForm(instituteId, targetSelectId, selectedFacultyId) {
    const select = document.getElementById(targetSelectId);
    if (!select) return;
    
    select.innerHTML = '<option value="">-- Выберите --</option>';
    
    if (!instituteId) {
        select.disabled = true;
        return;
    }
    
    select.disabled = false;
    
    fetch('api_faculties.php?institute_id=' + instituteId)
        .then(response => response.json())
        .then(faculties => {
            faculties.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.textContent = f.name;
                if (selectedFacultyId && f.id == selectedFacultyId) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Ошибка загрузки факультетов:', err));
}

function generatePDF(filename) {
    // Определяем текущую страницу
    const isReport = window.location.pathname.includes('report.php');
    const action = isReport ? 'report' : 'registry';
    
    // Получаем параметры фильтров из формы или URL
    const params = new URLSearchParams();
    params.set('action', action);
    params.set('type', 'pdf');
    
    // Берём значения из формы
    if (isReport) {
        const dateFrom = document.querySelector('input[name="date_from"]');
        const dateTo = document.querySelector('input[name="date_to"]');
        const instituteId = document.querySelector('select[name="institute_id"]');
        const facultyId = document.querySelector('select[name="faculty_id"]');
        
        if (dateFrom && dateFrom.value) params.set('date_from', dateFrom.value);
        if (dateTo && dateTo.value) params.set('date_to', dateTo.value);
        if (instituteId && instituteId.value) params.set('institute_id', instituteId.value);
        if (facultyId && facultyId.value) params.set('faculty_id', facultyId.value);
    } else {
        // Для реестра берём из URL или формы
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('date_from')) params.set('date_from', urlParams.get('date_from'));
        if (urlParams.get('date_to')) params.set('date_to', urlParams.get('date_to'));
        if (urlParams.get('institute_id')) params.set('institute_id', urlParams.get('institute_id'));
        if (urlParams.get('faculty_id')) params.set('faculty_id', urlParams.get('faculty_id'));
    }
    
    // Перенаправляем на серверный экспорт
    window.location.href = 'export.php?' + params.toString();
}

function generateWord(filename) {
    // Определяем текущую страницу
    const isReport = window.location.pathname.includes('report.php');
    const action = isReport ? 'report' : 'registry';
    
    // Получаем параметры фильтров из формы или URL
    const params = new URLSearchParams();
    params.set('action', action);
    params.set('type', 'word');
    
    // Берём значения из формы
    if (isReport) {
        const dateFrom = document.querySelector('input[name="date_from"]');
        const dateTo = document.querySelector('input[name="date_to"]');
        const instituteId = document.querySelector('select[name="institute_id"]');
        const facultyId = document.querySelector('select[name="faculty_id"]');
        
        if (dateFrom && dateFrom.value) params.set('date_from', dateFrom.value);
        if (dateTo && dateTo.value) params.set('date_to', dateTo.value);
        if (instituteId && instituteId.value) params.set('institute_id', instituteId.value);
        if (facultyId && facultyId.value) params.set('faculty_id', facultyId.value);
    } else {
        // Для реестра берём из URL или формы
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('date_from')) params.set('date_from', urlParams.get('date_from'));
        if (urlParams.get('date_to')) params.set('date_to', urlParams.get('date_to'));
        if (urlParams.get('institute_id')) params.set('institute_id', urlParams.get('institute_id'));
        if (urlParams.get('faculty_id')) params.set('faculty_id', urlParams.get('faculty_id'));
    }
    
    // Перенаправляем на серверный экспорт
    window.location.href = 'export.php?' + params.toString();
}
