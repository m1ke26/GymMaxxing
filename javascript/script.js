'use strict';

/* ============================
   UTILITY FUNCTIONS
   ============================ */

function getCsrfToken() {
    var el = document.getElementById('csrf-token');
    return el ? el.value : '';
}

function isUserLoggedIn() {
    var el = document.getElementById('is-logged-in');
    return el && el.value === '1';
}

function showToast(message, type) {
    var existing = document.querySelector('.toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + (type || 'success');
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(function() {
        toast.classList.add('toast-visible');
    });

    setTimeout(function() {
        toast.classList.remove('toast-visible');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

/* ============================
   CLASSES PAGE — AJAX FILTERS
   ============================ */

function initClassFilters() {
    var filterType    = document.getElementById('filter-type');
    var filterTrainer = document.getElementById('filter-trainer');
    var filterDay     = document.getElementById('filter-day');
    var filterTime    = document.getElementById('filter-time');
    var classGrid     = document.getElementById('class-grid');

    if (!filterType || !filterTrainer || !filterDay || !classGrid) return;

    function fetchClasses() {
        var params = [];
        if (filterType.value)    params.push('type=' + encodeURIComponent(filterType.value));
        if (filterTrainer.value) params.push('trainerId=' + encodeURIComponent(filterTrainer.value));
        if (filterDay.value)     params.push('day=' + encodeURIComponent(filterDay.value));
        if (filterTime && filterTime.value) params.push('time=' + encodeURIComponent(filterTime.value));

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/classes.php' + (params.length ? '?' + params.join('&') : ''));
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            if (xhr.status === 200) {
                var classes = JSON.parse(xhr.responseText);
                renderClasses(classes, classGrid);
            }
        };

        xhr.send();
    }

    filterType.addEventListener('change', fetchClasses);
    filterTrainer.addEventListener('change', fetchClasses);
    filterDay.addEventListener('change', fetchClasses);
    if (filterTime) filterTime.addEventListener('change', fetchClasses);
}

function renderClasses(classes, container) {
    container.innerHTML = '';

    if (classes.length === 0) {
        container.innerHTML = '<p class="no-results">No classes found matching your filters.</p>';
        return;
    }

    classes.forEach(function(cls) {
        var card = document.createElement('article');
        card.className = 'class-card';
        card.setAttribute('data-class-id', cls.id);

        var enrollBtnHtml = '';
        if (isUserLoggedIn()) {
            var btnText  = cls.enrolled ? 'UNENROLL' : 'ENROLL NOW';
            var enrolled = cls.enrolled ? '1' : '0';
            enrollBtnHtml = '<button class="svc-btn enroll-btn" data-class-id="' + cls.id + '" data-enrolled="' + enrolled + '">' + btnText + '</button>';
        } else {
            enrollBtnHtml = '<a href="/pages/login.php" class="svc-btn">LOGIN TO ENROLL</a>';
        }

        var ratingHtml = cls.avgRating ? '<p><strong>Rating:</strong> ' + cls.avgRating + ' / 5</p>' : '';

        card.innerHTML =
            '<img src="/images/' + escapeHtml(cls.image || 'homepage_outdoor.png') + '" alt="' + escapeHtml(cls.title) + '">' +
            '<div class="class-card-content">' +
                '<span class="class-type-badge">' + escapeHtml(capitalize(cls.type)) + '</span>' +
                '<h2>' + escapeHtml(cls.title) + '</h2>' +
                '<p class="class-description">' + escapeHtml(cls.description || '') + '</p>' +
                '<div class="class-meta">' +
                    '<p><strong>Schedule:</strong> ' + escapeHtml(cls.schedule) + '</p>' +
                    '<p><strong>Trainer:</strong> ' + escapeHtml(cls.trainerName) + '</p>' +
                    '<p class="spots-info"><strong>Spots:</strong> <span class="spots-count">' + cls.enrollmentCount + '</span> / ' + cls.capacity + '</p>' +
                    ratingHtml +
                '</div>' +
                enrollBtnHtml +
            '</div>';

        container.appendChild(card);
    });

    // Re-attach enrollment listeners
    initEnrollButtons();
}

/* ============================
   ENROLLMENT — AJAX
   ============================ */

function initEnrollButtons() {
    var buttons = document.querySelectorAll('.enroll-btn');

    buttons.forEach(function(btn) {
        // Clone to remove old listeners
        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener('click', function() {
            var classId  = parseInt(newBtn.getAttribute('data-class-id'));
            var enrolled = newBtn.getAttribute('data-enrolled') === '1';
            var action   = enrolled ? 'unenroll' : 'enroll';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/enroll.php');
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onload = function() {
                var response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    newBtn.setAttribute('data-enrolled', response.enrolled ? '1' : '0');
                    newBtn.textContent = response.enrolled ? 'UNENROLL' : 'ENROLL NOW';

                    var card = newBtn.closest('.class-card');
                    if (card) {
                        var spotsSpan = card.querySelector('.spots-count');
                        if (spotsSpan) spotsSpan.textContent = response.enrollmentCount;
                    }

                    showToast(response.message, 'success');
                } else {
                    showToast(response.error || 'An error occurred.', 'error');
                }
            };

            xhr.onerror = function() {
                showToast('Network error. Please try again.', 'error');
            };

            xhr.send(JSON.stringify({
                classId:    classId,
                action:     action,
                csrf_token: getCsrfToken()
            }));
        });
    });
}

/* ============================
   PROFILE — UNENROLL
   ============================ */

function initProfileUnenroll() {
    var buttons = document.querySelectorAll('.unenroll-profile-btn');
    if (!buttons.length) return;

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var classId = parseInt(btn.getAttribute('data-class-id'));

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/enroll.php');
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onload = function() {
                var response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    var item = btn.closest('.profile-class-item');
                    if (item) item.remove();
                    showToast(response.message, 'success');
                } else {
                    showToast(response.error || 'An error occurred.', 'error');
                }
            };

            xhr.send(JSON.stringify({
                classId:    classId,
                action:     'unenroll',
                csrf_token: getCsrfToken()
            }));
        });
    });
}

/* ============================
   EQUIPMENT FILTER
   ============================ */

function initEquipmentFilter() {
    var filter = document.getElementById('equipment-filter');
    var grid   = document.getElementById('equipment-grid');

    if (!filter || !grid) return;

    filter.addEventListener('change', function() {
        var selected = filter.value;
        var cards = grid.querySelectorAll('.equipment-card');

        cards.forEach(function(card) {
            if (!selected || card.getAttribute('data-type') === selected) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

/* ============================
   FLASH MESSAGES AUTO-HIDE
   ============================ */

function initFlashMessages() {
    var flashes = document.querySelectorAll('.flash-error, .flash-success');
    flashes.forEach(function(flash) {
        setTimeout(function() {
            flash.style.opacity = '0';
            setTimeout(function() { flash.remove(); }, 300);
        }, 5000);
    });
}

/* ============================
   ADMIN PANEL
   ============================ */

function initAdminPanel() {
    // Tab switching
    var tabs = document.querySelectorAll('.admin-tab');
    if (!tabs.length) return;

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            document.querySelectorAll('.admin-panel').forEach(function(p) { p.classList.add('hidden'); });
            var target = document.getElementById('tab-' + tab.getAttribute('data-tab'));
            if (target) target.classList.remove('hidden');
        });
    });

    initAdminUsers();
    initAdminClasses();
    initAdminEquipment();
}

function adminRequest(data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/admin.php');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function() {
        var response = JSON.parse(xhr.responseText);
        if (xhr.status === 200 && response.success) {
            showToast(response.message, 'success');
            if (callback) callback(response);
        } else {
            showToast(response.error || 'An error occurred.', 'error');
        }
    };
    xhr.onerror = function() { showToast('Network error.', 'error'); };
    data.csrf_token = getCsrfToken();
    xhr.send(JSON.stringify(data));
}

// ----- Users -----
function initAdminUsers() {
    // Toggle active/inactive
    document.querySelectorAll('.toggle-user-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var userId = parseInt(btn.getAttribute('data-user-id'));
            adminRequest({ entity: 'user', action: 'toggle', userId: userId }, function(res) {
                var row = btn.closest('tr');
                var statusSpan = row.querySelector('.user-status');
                if (res.active) {
                    statusSpan.textContent = 'Active';
                    statusSpan.className = 'user-status status-active';
                    btn.textContent = 'DEACTIVATE';
                    btn.setAttribute('data-active', '1');
                } else {
                    statusSpan.textContent = 'Inactive';
                    statusSpan.className = 'user-status status-inactive';
                    btn.textContent = 'ACTIVATE';
                    btn.setAttribute('data-active', '0');
                }
            });
        });
    });

    // Role change
    document.querySelectorAll('.role-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            var userId = parseInt(sel.getAttribute('data-user-id'));
            adminRequest({ entity: 'user', action: 'role', userId: userId, role: sel.value }, function() {});
        });
    });
}

// ----- Classes -----
function initAdminClasses() {
    var addBtn     = document.getElementById('add-class-btn');
    var cancelBtn  = document.getElementById('cancel-class-btn');
    var wrapper    = document.getElementById('class-form-wrapper');
    var form       = document.getElementById('class-form');
    var formTitle  = document.getElementById('class-form-title');
    var editIdEl   = document.getElementById('class-edit-id');

    if (!addBtn || !form) return;

    addBtn.addEventListener('click', function() {
        form.reset();
        editIdEl.value = '';
        formTitle.textContent = 'Add New Class';
        wrapper.classList.remove('hidden');
    });

    cancelBtn.addEventListener('click', function() {
        wrapper.classList.add('hidden');
        form.reset();
    });

    // Edit buttons
    document.querySelectorAll('.edit-class-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editIdEl.value = btn.getAttribute('data-id');
            document.getElementById('class-title').value       = btn.getAttribute('data-title');
            document.getElementById('class-type').value        = btn.getAttribute('data-type');
            document.getElementById('class-schedule').value    = btn.getAttribute('data-schedule');
            document.getElementById('class-capacity').value    = btn.getAttribute('data-capacity');
            document.getElementById('class-trainer').value     = btn.getAttribute('data-trainer');
            document.getElementById('class-image').value       = btn.getAttribute('data-image');
            document.getElementById('class-description').value = btn.getAttribute('data-description');
            formTitle.textContent = 'Edit Class';
            wrapper.classList.remove('hidden');
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-class-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this class? This cannot be undone.')) return;
            var id = parseInt(btn.getAttribute('data-id'));
            adminRequest({ entity: 'class', action: 'delete', id: id }, function() {
                var row = btn.closest('tr');
                if (row) row.remove();
            });
        });
    });

    // Submit form (create or update)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var editId = editIdEl.value;
        var data = {
            entity:      'class',
            action:      editId ? 'update' : 'create',
            title:       document.getElementById('class-title').value,
            type:        document.getElementById('class-type').value,
            schedule:    document.getElementById('class-schedule').value,
            capacity:    parseInt(document.getElementById('class-capacity').value),
            trainerId:   parseInt(document.getElementById('class-trainer').value),
            image:       document.getElementById('class-image').value,
            description: document.getElementById('class-description').value
        };
        if (editId) data.id = parseInt(editId);

        adminRequest(data, function() {
            location.reload();
        });
    });
}

// ----- Equipment -----
function initAdminEquipment() {
    var addBtn     = document.getElementById('add-equip-btn');
    var cancelBtn  = document.getElementById('cancel-equip-btn');
    var wrapper    = document.getElementById('equip-form-wrapper');
    var form       = document.getElementById('equip-form');
    var formTitle  = document.getElementById('equip-form-title');
    var editIdEl   = document.getElementById('equip-edit-id');

    if (!addBtn || !form) return;

    addBtn.addEventListener('click', function() {
        form.reset();
        editIdEl.value = '';
        formTitle.textContent = 'Add New Equipment';
        wrapper.classList.remove('hidden');
    });

    cancelBtn.addEventListener('click', function() {
        wrapper.classList.add('hidden');
        form.reset();
    });

    // Edit buttons
    document.querySelectorAll('.edit-equip-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editIdEl.value = btn.getAttribute('data-id');
            document.getElementById('equip-name').value        = btn.getAttribute('data-name');
            document.getElementById('equip-type').value        = btn.getAttribute('data-type');
            document.getElementById('equip-status').value      = btn.getAttribute('data-status');
            document.getElementById('equip-description').value = btn.getAttribute('data-description');
            formTitle.textContent = 'Edit Equipment';
            wrapper.classList.remove('hidden');
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-equip-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this equipment? This cannot be undone.')) return;
            var id = parseInt(btn.getAttribute('data-id'));
            adminRequest({ entity: 'equipment', action: 'delete', id: id }, function() {
                var row = btn.closest('tr');
                if (row) row.remove();
            });
        });
    });

    // Submit form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var editId = editIdEl.value;
        var data = {
            entity:      'equipment',
            action:      editId ? 'update' : 'create',
            name:        document.getElementById('equip-name').value,
            type:        document.getElementById('equip-type').value,
            status:      document.getElementById('equip-status').value,
            description: document.getElementById('equip-description').value
        };
        if (editId) data.id = parseInt(editId);

        adminRequest(data, function() {
            location.reload();
        });
    });
}

/* ============================
   PASSWORD TOGGLE
   ============================ */

function initPasswordToggle() {
    var toggles = document.querySelectorAll('.toggle-password');
    toggles.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = btn.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                btn.style.opacity = '1';
                btn.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                btn.style.opacity = '';
                btn.setAttribute('aria-label', 'Show password');
            }
        });
    });
}

/* ============================
   REVIEWS — MODAL + AJAX
   ============================ */

function initReviews() {
    var modal      = document.getElementById('review-modal');
    var closeBtn   = document.getElementById('modal-close');
    var form       = document.getElementById('review-form');
    var classIdEl  = document.getElementById('review-class-id');
    var ratingEl   = document.getElementById('review-rating');
    var stars      = document.querySelectorAll('#star-rating .star');

    if (!modal || !form) return;

    // Star rating interaction
    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            var value = parseInt(star.getAttribute('data-value'));
            ratingEl.value = value;
            stars.forEach(function(s) {
                s.classList.toggle('active', parseInt(s.getAttribute('data-value')) <= value);
            });
        });
    });

    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.add('hidden');
    });

    // Open review modal when clicking on a class card (enrolled only)
    document.addEventListener('click', function(e) {
        var card = e.target.closest('.class-card');
        if (!card) return;
        if (e.target.closest('.enroll-btn') || e.target.closest('a')) return;

        var enrollBtn = card.querySelector('.enroll-btn');
        if (!enrollBtn || enrollBtn.getAttribute('data-enrolled') !== '1') return;

        var classId = card.getAttribute('data-class-id');
        classIdEl.value = classId;

        // Reset form
        ratingEl.value = 0;
        stars.forEach(function(s) { s.classList.remove('active'); });
        document.getElementById('review-comment').value = '';

        // Load existing review
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/reviews.php?classId=' + classId);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.review) {
                    ratingEl.value = data.review.rating;
                    document.getElementById('review-comment').value = data.review.comment || '';
                    stars.forEach(function(s) {
                        s.classList.toggle('active', parseInt(s.getAttribute('data-value')) <= data.review.rating);
                    });
                }
            }
        };
        xhr.send();

        modal.classList.remove('hidden');
    });

    // Submit review
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var rating = parseInt(ratingEl.value);
        if (rating < 1 || rating > 5) {
            showToast('Please select a rating (1-5 stars).', 'error');
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/reviews.php');
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onload = function() {
            var response = JSON.parse(xhr.responseText);
            if (xhr.status === 200 && response.success) {
                showToast(response.message, 'success');
                modal.classList.add('hidden');
            } else {
                showToast(response.error || 'An error occurred.', 'error');
            }
        };

        xhr.send(JSON.stringify({
            classId:    parseInt(classIdEl.value),
            rating:     rating,
            comment:    document.getElementById('review-comment').value,
            csrf_token: getCsrfToken()
        }));
    });
}

/* ============================
   TIER SELECTION — JOIN US
   ============================ */

function initTierSelect() {
    var buttons = document.querySelectorAll('.tier-select-btn');
    if (!buttons.length) return;

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tier = btn.getAttribute('data-tier');
            var tierName = tier.toUpperCase();

            if (!confirm('Change your membership to ' + tierName + '?')) return;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/tier.php');
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onload = function() {
                var response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    showToast(response.message, 'success');
                    // Mark the selected plan visually
                    document.querySelectorAll('.tier-select-btn').forEach(function(b) {
                        b.textContent = 'SELECT »';
                    });
                    btn.textContent = 'CURRENT PLAN';
                } else {
                    showToast(response.error || 'An error occurred.', 'error');
                }
            };

            xhr.onerror = function() {
                showToast('Network error. Please try again.', 'error');
            };

            xhr.send(JSON.stringify({
                tier:       tier,
                csrf_token: getCsrfToken()
            }));
        });
    });
}

/* ============================
   HELPER FUNCTIONS
   ============================ */

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/* ============================
   INIT ON DOM READY
   ============================ */

document.addEventListener('DOMContentLoaded', function() {
    initClassFilters();
    initEnrollButtons();
    initProfileUnenroll();
    initEquipmentFilter();
    initAdminPanel();
    initFlashMessages();
    initPasswordToggle();
    initReviews();
    initTierSelect();
});
