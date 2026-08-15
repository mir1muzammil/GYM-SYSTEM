</main>
</div>

<script>
// Auto-close alerts after 5s
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => el.style.opacity = '0', 5000);
  setTimeout(() => el.remove(), 5500);
  el.style.transition = 'opacity 0.5s';
});
</script>
</body>
</html>