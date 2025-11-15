// تحقق بسيط على الجانب العميل
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('registerForm');
  const pwd = document.getElementById('password');
  const confirm = document.getElementById('confirm');
  const error = document.getElementById('error');

  form.addEventListener('submit', function (e) {
    error.textContent = '';

    if (pwd.value !== confirm.value) {
      e.preventDefault();
      error.textContent = 'كلمتا المرور غير متطابقتين.';
      confirm.focus();
      return;
    }

    if (!form.checkValidity()) {
      // HTML5 validation message (fallback)
      e.preventDefault();
      error.textContent = 'يرجى تعبئة الحقول المطلوبة بشكل صالح.';
      return;
    }

    // هنا يمكن إرسال النموذج فعلياً أو عمل طلب AJAX إلى الخادم
    // مثال: احتفظ بمنع الإرسال أثناء التطوير إذا أردت
    // e.preventDefault();
    // console.log('form valid, submit to server');
  });
});