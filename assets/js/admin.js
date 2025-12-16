document.addEventListener('DOMContentLoaded', () => {
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    // Toggle lessons visibility per course
    $$('#courses-table-body .toggle-lessons').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const courseId = row?.getAttribute('data-course-id');
            const lessonsRow = document.querySelector(`.lessons-row[data-course-id="${courseId}"]`);
            if (lessonsRow) {
                lessonsRow.style.display = lessonsRow.style.display === 'none' ? '' : 'none';
            }
        });
    });

    // Delete course
    $$('#courses-table-body .delete-course').forEach(btn => {
        btn.addEventListener('click', async () => {
            const row = btn.closest('tr');
            const courseId = row?.getAttribute('data-course-id');
            if (!courseId) return;
            if (!confirm('Delete this course? This will remove its lessons as well.')) return;
            try {
                const form = new FormData();
                form.append('action', 'delete_course');
                form.append('course_id', courseId);
                const res = await fetch('index.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form });
                const data = await res.json();
                if (data.ok) {
                    const lessonsRow = document.querySelector(`.lessons-row[data-course-id="${courseId}"]`);
                    if (lessonsRow) lessonsRow.remove();
                    row.remove();
                } else {
                    alert('Failed to delete course');
                }
            } catch (e) {
                alert('Error deleting course');
            }
        });
    });

    // Delete lesson
    $$('.lessons-row .delete-lesson').forEach(btn => {
        btn.addEventListener('click', async () => {
            const lessonRow = btn.closest('.lesson-row');
            const lessonId = lessonRow?.getAttribute('data-lesson-id');
            if (!lessonId) return;
            if (!confirm('Delete this lesson?')) return;
            try {
                const form = new FormData();
                form.append('action', 'delete_lesson');
                form.append('lesson_id', lessonId);
                const res = await fetch('index.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form });
                const data = await res.json();
                if (data.ok) {
                    lessonRow.remove();
                } else {
                    alert('Failed to delete lesson');
                }
            } catch (e) {
                alert('Error deleting lesson');
            }
        });
    });

    // Toggle admin role for user
    $$('#admins-list .toggle-admin').forEach(btn => {
        btn.addEventListener('click', async () => {
            const container = btn.closest('[data-user-id]');
            const userId = container?.getAttribute('data-user-id');
            if (!userId) return;
            const isAdmin = btn.getAttribute('data-current-role') === '3';
            if (isAdmin && !confirm('Remove admin from this user?')) return;
            try {
                const form = new FormData();
                form.append('action', 'toggle_admin');
                form.append('user_id', userId);
                const res = await fetch('index.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form });
                const data = await res.json();
                if (data.ok) {
                    if (data.role_id === 3) {
                        // became admin
                        btn.textContent = 'Remove Admin';
                        btn.setAttribute('data-current-role', '3');
                    } else {
                        // removed admin -> remove from list
                        container.remove();
                    }
                } else {
                    alert(data.error || 'Failed to toggle admin');
                }
            } catch (e) {
                alert('Error toggling admin');
            }
        });
    });

    // Show/Hide Add Course Form
    const showAddCourseBtn = $('#show-add-course-btn');
    const addCourseForm = $('#add-course-form');
    const cancelAddCourseBtn = $('#cancel-add-course-btn');

    if (showAddCourseBtn && addCourseForm) {
        showAddCourseBtn.addEventListener('click', () => {
            addCourseForm.style.display = 'block';
            showAddCourseBtn.style.display = 'none';
        });
    }

    if (cancelAddCourseBtn && addCourseForm) {
        cancelAddCourseBtn.addEventListener('click', () => {
            addCourseForm.style.display = 'none';
            if (showAddCourseBtn) showAddCourseBtn.style.display = 'inline-block';
        });
    }

    // Show/Hide Add Lesson Forms
    $$('.show-add-lesson-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const courseId = btn.getAttribute('data-course-id');
            const form = document.querySelector(`.add-lesson-form[data-course-id="${courseId}"]`);
            if (form) {
                form.style.display = 'block';
                btn.style.display = 'none';
            }
        });
    });

    $$('.cancel-add-lesson-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('.add-lesson-form');
            if (form) {
                const courseId = form.getAttribute('data-course-id');
                form.style.display = 'none';
                const showBtn = document.querySelector(`.show-add-lesson-btn[data-course-id="${courseId}"]`);
                if (showBtn) showBtn.style.display = 'block';
            }
        });
    });
});

