// تحقق بسيط على الجانب العميل
document.addEventListener('DOMContentLoaded', function () {
  // Helpers to show/hide forms
  function showLogin() {
    const reg = document.getElementById('registerForm');
    const log = document.getElementById('loginForm');
    if (reg) reg.style.display = 'none';
    if (log) log.style.display = 'block';
  }

  function showRegister() {
    const reg = document.getElementById('registerForm');
    const log = document.getElementById('loginForm');
    if (log) log.style.display = 'none';
    if (reg) reg.style.display = 'block';
  }

  // Wire toggle links if present
  const showLoginLink = document.getElementById('show-login');
  const showRegisterLink = document.getElementById('show-register');
  if (showLoginLink) showLoginLink.addEventListener('click', function (e) { e.preventDefault(); showLogin(); });
  if (showRegisterLink) showRegisterLink.addEventListener('click', function (e) { e.preventDefault(); showRegister(); });

  const form = document.getElementById('registerForm');
  const pwd = document.getElementById('password');
  const confirm = document.getElementById('confirm');
  const error = document.getElementById('error');
  const nameInput = document.getElementById('name');
  const emailInput = document.getElementById('email');
  const phoneInput = document.getElementById('phone');

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

    // منع الإرسال الافتراضي: سنحفظ مستخدمًا تجريبيًا في localStorage
    e.preventDefault();

    const users = JSON.parse(localStorage.getItem('users') || '[]');

    // التحقق من وجود البريد مسبقًا
    if (users.some(u => u.email === emailInput.value.trim())) {
      error.textContent = 'هذا البريد مسجل بالفعل. الرجاء تسجيل الدخول.';
      emailInput.focus();
      return;
    }

    const newUser = {
      name: nameInput.value.trim(),
      email: emailInput.value.trim(),
      phone: phoneInput.value.trim(),
      password: pwd.value
    };

    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));

    // بعد التسجيل الناجح: نظهر نموذج تسجيل الدخول ونمسح الحقول
    form.reset();
    error.textContent = 'تم إنشاء الحساب بنجاح. الرجاء تسجيل الدخول.';
    showLogin();
  });

  // -- تسجيل الدخول (موجود في نفس الملف كما طُلِب)
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const loginEmail = document.getElementById('login-email');
    const loginPassword = document.getElementById('login-password');
    const loginError = document.getElementById('login-error');

    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      loginError.textContent = '';

      if (!loginForm.checkValidity()) {
        loginError.textContent = 'يرجى تعبئة الحقول المطلوبة بشكل صالح.';
        return;
      }

      const users = JSON.parse(localStorage.getItem('users') || '[]');
      const found = users.find(u => u.email === loginEmail.value.trim());

      if (!found) {
        loginError.textContent = 'لم يتم العثور على حساب بهذا البريد. الرجاء التسجيل أولاً.';
        loginEmail.focus();
        return;
      }

      if (found.password !== loginPassword.value) {
        loginError.textContent = 'كلمة المرور غير صحيحة.';
        loginPassword.focus();
        return;
      }

      // تسجيل المستخدم الحالي (جلسة مبسطة)
      localStorage.setItem('currentUser', JSON.stringify({ name: found.name, email: found.email }));

      // إعادة التوجيه إلى صفحة المنتجات بعد تسجيل الدخول
      window.location.href = 'Products.html';
    });
  }
});