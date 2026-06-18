const togglePasswordVisibility = (button) => {
    const wrapper = button.closest('[data-password-field]');
    const passwordInput = wrapper?.querySelector('[data-login-password]');
    const visibleIcon = button.querySelector('[data-password-visible-icon]');
    const hiddenIcon = button.querySelector('[data-password-hidden-icon]');

    if (!passwordInput) {
        return;
    }

    const isHidden = passwordInput.type === 'password';

    passwordInput.type = isHidden ? 'text' : 'password';
    button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
    button.setAttribute('aria-pressed', String(isHidden));

    visibleIcon?.classList.toggle('hidden', !isHidden);
    hiddenIcon?.classList.toggle('hidden', isHidden);
};

const bindPasswordToggles = () => {
    document.querySelectorAll('[data-login-password-toggle]').forEach((button) => {
        if (button.dataset.passwordToggleBound === 'true') {
            return;
        }

        button.dataset.passwordToggleBound = 'true';
        button.addEventListener('click', (event) => {
            event.preventDefault();
            togglePasswordVisibility(button);
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindPasswordToggles);
} else {
    bindPasswordToggles();
}
