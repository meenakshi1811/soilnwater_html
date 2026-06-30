(function () {
  function enhancePasswordInput(input) {
    if (input.dataset.passwordToggleReady === 'true' || input.closest('.password-toggle-field')) {
      return;
    }

    input.dataset.passwordToggleReady = 'true';

    const wrapper = document.createElement('div');
    wrapper.className = 'password-toggle-field';

    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'password-toggle-btn';
    button.setAttribute('aria-label', 'Show password');
    button.setAttribute('aria-pressed', 'false');
    button.innerHTML = ''
      + '<i class="fa-regular fa-eye password-toggle-icon password-toggle-icon--show" aria-hidden="true"></i>'
      + '<i class="fa-solid fa-eye-slash password-toggle-icon password-toggle-icon--hide d-none" aria-hidden="true"></i>';

    button.addEventListener('click', function () {
      const showPassword = input.type === 'password';
      input.type = showPassword ? 'text' : 'password';
      button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
      button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
      button.querySelector('.password-toggle-icon--show').classList.toggle('d-none', showPassword);
      button.querySelector('.password-toggle-icon--hide').classList.toggle('d-none', !showPassword);
    });

    wrapper.appendChild(button);
  }

  function initPasswordToggles(root) {
    const scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('input[type="password"]').forEach(enhancePasswordInput);
  }

  function init() {
    initPasswordToggles(document);

    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType !== 1) {
            return;
          }

          if (node.matches && node.matches('input[type="password"]')) {
            enhancePasswordInput(node);
          }

          initPasswordToggles(node);
        });
      });
    });

    if (document.body) {
      observer.observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.initPasswordToggles = initPasswordToggles;
})();
