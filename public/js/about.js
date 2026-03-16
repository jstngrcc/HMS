
document.addEventListener('DOMContentLoaded', function() {
  const quotes = document.querySelectorAll('.quote-slider blockquote');
  const names = document.querySelectorAll('.quote-slider .guest-name');
  let index = 0;

  function showQuote(i) {
    for (let q = 0; q < quotes.length; q++) {
      quotes[q].style.display = (q === i) ? 'block' : 'none';
    }
    for (let n = 0; n < names.length; n++) {
      names[n].style.display = (n === i) ? 'block' : 'none';
    }
  }

  // Show the first quote initially
  showQuote(index);

  // Auto-swipe every 3 seconds
  setInterval(function() {
    index = (index + 1) % quotes.length;
    showQuote(index);
  }, 3000);
});
