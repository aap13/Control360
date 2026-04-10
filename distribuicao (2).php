(function(){
  const KEY='assets_ti_theme';
  function apply(theme){
    document.body.classList.remove('theme-light','theme-dark');
    document.body.classList.add(theme==='light'?'theme-light':'theme-dark');
    const label=document.querySelector('[data-theme-label]');
    if(label) label.textContent = theme==='light' ? 'Tema claro' : 'Tema escuro';
  }
  window.toggleTheme=function(){
    const next=document.body.classList.contains('theme-light')?'dark':'light';
    localStorage.setItem(KEY,next);
    apply(next);
  }
  document.addEventListener('DOMContentLoaded', function(){
    apply(localStorage.getItem(KEY)||'dark');
  });
})();
