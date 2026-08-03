function getAlertHost(){
    let host = document.getElementById('alert-host');
    if(host){
        return host;
    }
    host = document.createElement('div');
    host.id = 'alert-host';
    host.className = 'alert-host';
    document.body.insertBefore(host, document.body.firstChild);
    return host;
}

function initHeaderMenu(){
    const toggle = document.getElementById('menu-toggle');
    const panel = document.getElementById('header-nav-panel');
    if(!toggle || !panel || toggle.dataset.bound){
        return;
    }

    const closePanel = () => panel.classList.remove('open');
    const togglePanel = (e) => {
        e.stopPropagation();
        panel.classList.toggle('open');
    };

    toggle.addEventListener('click', togglePanel);
    document.addEventListener('click', (e) => {
        if(!panel.contains(e.target) && e.target !== toggle){
            closePanel();
        }
    });
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape'){
            closePanel();
        }
    });
    toggle.dataset.bound = '1';
}

function showNotice(msg, type){
    const host = getAlertHost();
    const kind = type || 'info';
    const box = document.createElement('div');
    box.className = `fb-alert fb-alert-${kind}`;

    const text = document.createElement('span');
    text.className = 'fb-alert-text';
    text.textContent = String(msg);

    const closeBtn = document.createElement('button');
    closeBtn.className = 'fb-alert-close';
    closeBtn.type = 'button';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', function(){
        box.remove();
    });

    box.appendChild(text);
    box.appendChild(closeBtn);
    host.appendChild(box);

    // Auto-dismiss all notifications; errors stay slightly longer.
    const ttl = kind === 'error' ? 6500 : (kind === 'success' ? 3500 : 4500);
    setTimeout(function(){
        box.remove();
    }, ttl);
}

// Backward-compatible helper used by existing pages
function floatingAlert(msg, type){
    showNotice(msg, type || 'info');
}

// Make existing <script>alert(...)</script> render as centered modal.
window.alert = function(msg){
    showNotice(String(msg), 'error');
};

document.addEventListener('DOMContentLoaded', initHeaderMenu);

// AJAX fetch example (update grade without reload)
function updateGrade(studentId, subjectId, grade){
    fetch('update_grade.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`student_id=${studentId}&subject_id=${subjectId}&grade=${grade}`
    }).then(res=>res.text()).then(data=>{
        floatingAlert('Grade updated', 'success');
        // Optionally, refresh the table
        location.reload();
    });
}

// Password validation before submit
function checkPassword(inputId){
    const pass = document.getElementById(inputId).value;
    const hasMinLength = pass.length >= 8;
    const hasUpper = /[A-Z]/.test(pass);
    const hasSymbol = /[^A-Za-z0-9]/.test(pass);
    if(!(hasMinLength && (hasUpper || hasSymbol))){
        floatingAlert("Password must be at least 8 chars and include uppercase or symbol", 'error');
        return false;
    }
    return true;
}
