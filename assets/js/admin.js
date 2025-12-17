document.addEventListener('DOMContentLoaded', () => {

    const coursesTable = document.getElementById('courses-table-body');

    // Toggle lessons
    if (coursesTable) {
        coursesTable.addEventListener('click', (e) => {
            const btn = e.target.closest('.toggle-lessons');
            if (!btn) return;

            const row = btn.closest('tr');
            if (!row) return;

            const courseId = row.getAttribute('data-course-id');
            const lessonsRow = document.querySelector('.lessons-row[data-course-id="' + courseId + '"]');

            if (!lessonsRow) return;

            if (lessonsRow.style.display === 'none') {
                lessonsRow.style.display = '';
                btn.textContent = 'Hide';
            } else {
                lessonsRow.style.display = 'none';
                btn.textContent = 'Lessons';
            }
        });
    }

    // Delete course
    if (coursesTable) {
        coursesTable.addEventListener('click', async (e) => {
            const btn = e.target.closest('.delete-course');
            if (!btn) return;

            e.preventDefault();

            const row = btn.closest('tr');
            if (!row) return;

            const courseId = row.getAttribute('data-course-id');
            if (!courseId) return;

            if (!confirm('Delete this course?')) return;

            const form = new FormData();
            form.append('action', 'delete_course');
            form.append('course_id', courseId);

            const res = await fetch('index.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: form
            });

            if (!res.ok) return;

            const lessonsRow = document.querySelector('.lessons-row[data-course-id="' + courseId + '"]');
            if (lessonsRow) lessonsRow.remove();

            row.remove();
        });
    }

    // Delete lesson
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.delete-lesson');
        if (!btn) return;

        e.preventDefault();

        const lessonRow = btn.closest('.lesson-row');
        if (!lessonRow) return;

        const lessonId = lessonRow.getAttribute('data-lesson-id');
        if (!lessonId) return;

        if (!confirm('Delete this lesson?')) return;

        const form = new FormData();
        form.append('action', 'delete_lesson');
        form.append('lesson_id', lessonId);

        const res = await fetch('index.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: form
        });

        if (!res.ok) return;

        lessonRow.remove();
    });

    // Intercept delete forms (covers dashboard and admin delete forms without JS classes)
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form || !form.matches('form')) return;
        const actionEl = form.querySelector('input[name="action"]');
        if (!actionEl) return;
        const actionVal = actionEl.value;
        if (actionVal !== 'delete_course' && actionVal !== 'delete_lesson') return;

        e.preventDefault();
        if (!confirm(actionVal === 'delete_course' ? 'Delete this course? This will remove its lessons as well.' : 'Delete this lesson?')) return;

        const formData = new FormData(form);
        const endpoint = window.location.pathname || form.getAttribute('action') || 'index.php';
        try {
            const res = await fetch(endpoint, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
            console.debug('Delete form response', res.status, res.statusText);
            const ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
                let data;
                try { data = await res.json(); } catch (err) { const txt = await res.text(); console.error('Failed parsing JSON delete form response', err, txt); alert('Server returned invalid JSON. Check console.'); return; }
                if (data.ok) {
                    if (actionVal === 'delete_course') {
                        const cid = formData.get('course_id');
                        const tr = document.querySelector('tr[data-course-id="' + cid + '"]');
                        const lessonsRow = document.querySelector('.lessons-row[data-course-id="' + cid + '"]');
                        if (lessonsRow) lessonsRow.remove();
                        if (tr) tr.remove();
                    } else {
                        const lid = formData.get('lesson_id');
                        const lrow = document.querySelector('.lesson-row[data-lesson-id="' + lid + '"]') || form.closest('.lesson-row');
                        if (lrow) lrow.remove();
                    }
                } else {
                    alert(data.error || 'Failed to delete');
                }
            } else if (res.ok) {
                // non-JSON success
                if (actionVal === 'delete_course') {
                    const cid = formData.get('course_id');
                    const tr = document.querySelector('tr[data-course-id="' + cid + '"]');
                    const lessonsRow = document.querySelector('.lessons-row[data-course-id="' + cid + '"]');
                    if (lessonsRow) lessonsRow.remove();
                    if (tr) tr.remove();
                } else {
                    const lid = formData.get('lesson_id');
                    const lrow = document.querySelector('.lesson-row[data-lesson-id="' + lid + '"]') || form.closest('.lesson-row');
                    if (lrow) lrow.remove();
                }
            } else {
                const txt = await res.text();
                console.error('Unexpected delete form response', res.status, res.statusText, txt);
                alert('Server returned an unexpected response. See console.');
            }
        } catch (err) {
            console.error('Error handling delete form', err);
            alert('Error deleting item: ' + (err && err.message ? err.message : err));
        }
    });

    // Toggle admin role (delegated)
    document.getElementById('admins-list')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.toggle-admin');
        if (!btn) return;
        const container = btn.closest('[data-user-id]');
        const userId = container?.getAttribute('data-user-id');
        if (!userId) return;
        const isAdmin = btn.getAttribute('data-current-role') === '3';

        if (isAdmin && !confirm('Remove admin from this user?')) return;

        try {
            const form = new FormData();
            form.append('action', 'toggle_admin');
            form.append('user_id', userId);
            // admin pages live at /admin/index.php — use relative endpoint
            const res = await fetch('index.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form });
            const ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
                const data = await res.json();
                if (!data.ok) {
                    alert(data.error || 'Failed to toggle admin');
                    return;
                }
                if (data.role_id === 3) {
                    // promoted to admin
                    btn.textContent = 'Remove Admin';
                    btn.setAttribute('data-current-role', '3');
                } else {
                    // demoted - remove from list
                    if (container) container.remove();
                }
            } else if (res.ok) {
                // Non-JSON fallback: toggle UI conservatively
                if (isAdmin) {
                    if (container) container.remove();
                } else {
                    btn.textContent = 'Remove Admin';
                    btn.setAttribute('data-current-role', '3');
                }
            } else {
                const txt = await res.text();
                console.error('Unexpected response toggling admin', res.status, res.statusText, txt);
                alert('Server returned an error. See console.');
            }
        } catch (err) {
            console.error('Error toggling admin', err);
            alert('Error toggling admin: ' + (err && err.message ? err.message : err));
        }
    });

    // Toggle add course form
    const showAddCourseBtn = document.getElementById('show-add-course-btn');
    const addCourseForm = document.getElementById('add-course-form');
    const cancelAddCourseBtn = document.getElementById('cancel-add-course-btn');

    if (showAddCourseBtn && addCourseForm) {
        showAddCourseBtn.addEventListener('click', () => {
            addCourseForm.style.display = 'block';
            showAddCourseBtn.style.display = 'none';
        });
    }

    if (cancelAddCourseBtn && addCourseForm) {
        cancelAddCourseBtn.addEventListener('click', () => {
            addCourseForm.style.display = 'none';
            showAddCourseBtn.style.display = 'inline-block';
        });
    }

    // Show add lesson form
    document.querySelectorAll('.show-add-lesson-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const courseId = btn.getAttribute('data-course-id');
            const form = document.querySelector('.add-lesson-form[data-course-id="' + courseId + '"]');

            if (!form) return;

            form.style.display = 'block';
            btn.style.display = 'none';
        });
    });

    // Cancel add lesson
    document.querySelectorAll('.cancel-add-lesson-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('.add-lesson-form');
            if (!form) return;

            const courseId = form.getAttribute('data-course-id');
            form.style.display = 'none';

            const showBtn = document.querySelector('.show-add-lesson-btn[data-course-id="' + courseId + '"]');
            if (showBtn) showBtn.style.display = 'block';
        });
    });

});