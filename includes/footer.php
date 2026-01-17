<!-- ================= FOOTER ================= -->
<footer class="custom-footer text-center py-3">
  <p class="mb-0">© 2025 POPMART | Designed for demo purposes</p>
</footer>

<!-- jquery and javascript -->
<script src="/website-popmart/dist/jquery.min.js"></script>
<script src="/website-popmart/dist/bootstrap.bundle.min.js"></script>

<!-- jquery validation for online -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script> -->

<!-- jquery validations locally -->
<script src="/website-popmart/dist/jquery.validate.min.js"></script>
<script src="/website-popmart/dist/additional-methods.min.js"></script>

<script src="/website-popmart/js/validation.js"></script>
<script src="/website-popmart/js/animation.js"></script>
<script src="/website-popmart/js/auth.js?v=<?php echo time(); ?>"></script>

  <script>
    // breadcrumb (guards to avoid errors on pages without these elements)
    (function(){
      try {
        const params = new URLSearchParams(window.location.search);
        const productName = params.get("name") || "Product";
        var el;
        el = document.getElementById("breadcrumb-product"); if (el) el.textContent = productName;
        el = document.getElementById("product-title"); if (el) el.textContent = productName;
        el = document.getElementById("product-card-title"); if (el) el.textContent = productName;
      } catch(e) { /* no-op */ }
    })();

    // Contact form submission via AJAX
    (function(){
      var form = document.getElementById('contactForm');
      if (!form) return;
      form.addEventListener('submit', function(e){
        e.preventDefault();
        var fd = new FormData(form);
        fetch('/website-popmart/db/contact_submit.php', { method: 'POST', body: fd })
          .then(function(r){ return r.json().catch(function(){ return { success:false, message:'Invalid response'}; }); })
          .then(function(res){
            var okToastEl = document.getElementById('globalToast');
            var errToastEl = document.getElementById('globalToastError');
            if (res.success){
              document.getElementById('globalToastMessage').textContent = 'Message sent! We\'ll get back to you soon.';
              new bootstrap.Toast(okToastEl, { delay: 2000 }).show();
              form.reset();
            } else {
              document.getElementById('globalToastErrorMessage').textContent = res.message || 'Failed to send message.';
              new bootstrap.Toast(errToastEl, { delay: 2500 }).show();
            }
          })
          .catch(function(){
            var errToastEl = document.getElementById('globalToastError');
            document.getElementById('globalToastErrorMessage').textContent = 'Network error. Please try again.';
            new bootstrap.Toast(errToastEl, { delay: 2500 }).show();
          });
      });
    })();
  </script>

</body>
</html>