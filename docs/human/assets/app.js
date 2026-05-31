document.querySelectorAll('pre').forEach((pre) => {
  const wrapper = document.createElement('div');
  wrapper.className = 'code-wrap';
  pre.parentNode.insertBefore(wrapper, pre);
  wrapper.appendChild(pre);

  const button = document.createElement('button');
  button.className = 'copy-code';
  button.type = 'button';
  button.textContent = 'Copiar';
  button.addEventListener('click', async () => {
    await navigator.clipboard.writeText(pre.innerText);
    button.textContent = 'Copiado';
    setTimeout(() => {
      button.textContent = 'Copiar';
    }, 1200);
  });
  wrapper.appendChild(button);
});

const current = location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.nav a').forEach((link) => {
  if (link.getAttribute('href') === current) {
    link.setAttribute('aria-current', 'page');
  }
});
