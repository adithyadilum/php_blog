  </main>
  <footer id="contact" class="bg-sand border-t border-cream py-6">
    <div class="max-w-6xl mx-auto px-8 text-center text-sm text-charcoal/70 flex flex-col gap-3">
      <p class="font-sans">&copy; <?php echo date('Y'); ?> Paper & Pixels · Crafted by Adithya Dilum</p>
      <div class="flex justify-center gap-4 text-xs uppercase tracking-[0.28em]">
        <a href="https://twitter.com" target="_blank" rel="noopener" class="hover:opacity-70 transition">Twitter</a>
        <a href="https://instagram.com" target="_blank" rel="noopener" class="hover:opacity-70 transition">Instagram</a>
        <a href="mailto:adithyadilum11@gmail.com" class="hover:opacity-70 transition">Email</a>
      </div>
    </div>
  </footer>
  <?php if (!empty($page_extra_scripts)) {
    echo $page_extra_scripts;
  } ?>
  <script src="/php_blog/assets/js/app.js"></script>
  </body>

  </html>