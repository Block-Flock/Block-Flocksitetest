document.addEventListener('DOMContentLoaded', () => {
    const $navDropdowns = Array.prototype.slice.call(document.querySelectorAll('.navbar-item.has-dropdown'), 0);
  
    if($navDropdowns.length > 0) {
  
      // Add a click event on each of them
      $navDropdowns.forEach( el => {
        el.addEventListener('click', () => {
  
          // Get the target from the "data-target" attribute
          const target = el.dataset.target;
          const $target = document.getElementById(target);
  
          el.classList.toggle('is-active');
  
        });
      });
  
    }
  });