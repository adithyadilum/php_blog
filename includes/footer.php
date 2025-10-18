  </main>
  <footer id="contact" class="relative overflow-hidden border-t border-cream bg-linen/95 py-16">
    <div class="pointer-events-none absolute inset-0 opacity-80" style="background: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.9) 0%, rgba(250,246,233,0.55) 45%, rgba(244,237,213,0.35) 75%, rgba(244,237,213,0.1) 100%);"></div>
    <div class="relative mx-auto flex w-full max-w-6xl flex-col gap-12 px-6">
      <div class="grid gap-10 text-sm text-charcoal/75 md:grid-cols-[1.3fr_1fr] md:items-start">
        <div class="space-y-6">
          <p class="text-xs uppercase tracking-[0.3em] text-charcoal/55">Connect</p>
          <div class="space-y-4">
            <a href="mailto:adithyadilum11@gmail.com" class="inline-flex items-center gap-3 rounded-full border border-charcoal/20 bg-white/70 px-5 py-3 text-xs uppercase tracking-[0.28em] text-charcoal transition hover:border-charcoal/50 hover:text-charcoal/85">
              Email the studio
              <svg aria-hidden="true" class="h-3.5 w-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                <path d="M4 8l8 5 8-5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5 19h14a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <div class="flex flex-wrap gap-3 text-xs uppercase tracking-[0.28em]">
              <a href="https://twitter.com" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-charcoal/20 bg-white/60 px-4 py-2 transition hover:border-charcoal/50 hover:text-charcoal/80">Twitter</a>
              <a href="https://github.com" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-charcoal/20 bg-white/60 px-4 py-2 transition hover:border-charcoal/50 hover:text-charcoal/80">GitHub</a>
              <a href="https://linkedin.com" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-charcoal/20 bg-white/60 px-4 py-2 transition hover:border-charcoal/50 hover:text-charcoal/80">LinkedIn</a>
            </div>
          </div>
          <p class="text-xs uppercase tracking-[0.3em] text-charcoal/55">Explore</p>
          <div class="flex flex-wrap gap-4 text-xs uppercase tracking-[0.28em]">
            <a href="#stories" class="hover:text-charcoal transition">Stories</a>
            <a href="/php_blog/posts/create.php" class="hover:text-charcoal transition">Publish</a>
            <a href="#contact" class="hover:text-charcoal transition">Contact</a>
          </div>
        </div>

        <div class="space-y-5 text-right md:text-left">
          <span class="text-xs uppercase tracking-[0.4em] text-charcoal/55">Stay in the studio</span>
          <p class="font-heading text-2xl text-charcoal md:text-3xl">Stories, systems, and sketches that bridge analog warmth with digital clarity.</p>
          <p class="font-sans text-sm text-charcoal/70 md:text-base">We profile the rituals, tools, and collaborations that help modern makers publish boldly. Drop us a note or follow along as we document the craft behind every release.</p>
        </div>
      </div>

      <div class="flex flex-col items-center justify-between gap-6 border-t border-charcoal/10 pt-8 text-center text-xs uppercase tracking-[0.3em] text-charcoal/55">
        <h2 class="font-heading text-10xl text-charcoal md:text-7xl lg:text-8xl">Paper &amp; Pixels</h2>
        <p class="font-sans text-[0.7rem]">&copy; <?php echo date('Y'); ?> Paper &amp; Pixels · Crafted by Adithya Dilum</p>
      </div>
    </div>
  </footer>
  <?php if (!empty($page_extra_scripts)) {
    echo $page_extra_scripts;
  } ?>
  <script src="/php_blog/assets/js/app.js"></script>
  </body>

  </html>