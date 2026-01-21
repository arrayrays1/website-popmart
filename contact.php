<?php
  $activePage = 'contact';
  include 'includes/header.php';
  include 'includes/modals.php';

  $isLoggedIn = isset($_SESSION['user_id']);
  $userName = '';
  $userEmail = '';

  if ($isLoggedIn) {
    require_once 'db/db_connect.php';
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      $userName = $user['first_name'] . ' ' . $user['last_name'];
      $userEmail = $user['email'];
    }
  }
?>

  <section class="contact-div text-center mt-5 pt-5">
    <img src="img/contact-banner.png" alt="Popmart customer service banner" class="img-fluid contact-banner" style="width: 100%;">
  </section>

<!-- ================= CONTACT FORM SECTION ================= -->
<section class="contact-form-section py-5 mt-5">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6 text-center text-lg-start">
        <img src="img/hirono-flying.png" alt="Popmart character" class="img-fluid mb-3 contact-img" style="max-width: 350px;">
        <h2 class="fw-bold mb-3">Got questions?<br><span class="text-muted">Send us an email</span></h2>
        <?php if ($isLoggedIn): ?>
          <p class="text-success"><i class="bi bi-person-check"></i> You're logged in as a registered customer. Your queries will be tracked and prioritized.</p>
        <?php endif; ?>
        <p>We’d love to hear from you! Whether you have questions, feedback, or partnership inquiries, our team is here to help.</p>
        <p>Reach out to us through our contact form or email, and we’ll get back to you as soon as possible. Your thoughts matter to us — let’s stay connected!</p>
      </div>

      <div class="col-lg-6">
        <form id="contactForm" class="p-4 bg-light rounded-4 shadow-sm contact-form" novalidate>
          <div class="mt-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($userName); ?>" <?php echo $isLoggedIn ? 'readonly' : ''; ?>>
          </div>

          <div class="mt-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="someone@email.com" value="<?php echo htmlspecialchars($userEmail); ?>" <?php echo $isLoggedIn ? 'readonly' : ''; ?>>
          </div>

          <div class="mt-3">
            <label for="message" class="form-label">Your Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Write your message..."></textarea>
          </div>

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-dark px-4 py-2 rounded-pill">Send Message</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>


<!-- ================= CONTACT SECTION ================= -->
<section class="py-5 contact-section">
  <div class="container">
    <h1 class="custom-h1-contact text-center mb-5">Contact Us</h1>

    <div class="row g-4">

      <div class="col-md-6">
        <div class="p-4 border rounded shadow-sm h-100">
          <h3>Customer Support</h3>
          <p>For questions about online products or customer service, please reach out to our support team.</p>
          <p><strong>Email:</strong> support@popmart.com</p>
          <p><strong>Address:</strong><br>Nos. 721 - 725 Nathan Road, Flat/Rm 1405A, 14/F, Mongkok, Kowloon, New Territories, Hong Kong SAR</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 border rounded shadow-sm h-100">
          <h3>Global Supply & Business</h3>
          <p>POP MART provides products worldwide. With Roboshop vending machines, strategic alliances, and more, we’re here to support your needs.</p>
          <p><strong>Asia:</strong> asia@popmart.com</p>
          <p><strong>North America:</strong> us@popmart.com</p>
          <p><strong>Europe:</strong> eu@popmart.com</p>
          <p><strong>Australia:</strong> au@popmart.com</p>
          <p><strong>New Zealand:</strong> nz@popmart.com</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 border rounded shadow-sm h-100">
          <h3>Licensing</h3>
          <p>We explore co-branding opportunities to expand our creative universe. Each IP is backed by a visionary artist, enabling unique collaborations and one-of-a-kind products.</p>
          <p><strong>Email:</strong> brandlicensing@popmart.com</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 border rounded shadow-sm h-100">
          <h3>Collaborations</h3>
          <p>POP MART thrives on creativity and innovation. We welcome collaborations with influencers, media, PR, and artists to create fun and impactful projects.</p>
          <p><strong>Email:</strong> collab@popmart.com</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 border rounded shadow-sm h-100">
          <h3>General Inquiries</h3>
          <p><strong>Email:</strong> info@popmart.com</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 border rounded shadow-sm h-100">
          <h3>Media Inquiries</h3>
          <p><strong>Email:</strong> press@popmart.com</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal">
      <div class="modal-body text-center p-5">
        <h2 class="fw-bold mb-2">WELCOME TO</h2>
        <div class="mb-4">
          <img src="img/pop-mart-logo.png" alt="Popmart Logo" class="login-logo">
        </div>

        <form id="loginForm" class="text-start" novalidate>
          <div class="mt-3 position-relative">
            <label for="loginEmail" class="form-label fw-semibold">Email</label>
            <input type="text" id="loginEmail" name="loginEmail" class="form-control pe-5" placeholder="name@email.com" required>
            <i class="bi bi-envelope login-icon right"></i>
          </div>

          <div class="mt-3 position-relative">
            <label for="loginPassword" class="form-label fw-semibold">Password</label>
            <input type="password" id="loginPassword" name="loginPassword" class="form-control pe-5" placeholder="Enter your password" autocomplete="off" required>
            <i class="bi bi-eye toggle-password"></i>
          </div>
          

          <button type="submit" class="btn btn-danger w-100 rounded-pill mt-4">Login</button>
        </form>

        <p class="mt-3 mb-0 small">
          Don’t have an account?
          <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal"
             class="text-decoration-none fw-semibold text-primary">Sign Up here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- SIGNUP MODAL -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content signup-modal">
      <div class="modal-body text-center p-5">
        <div class="mb-2">
          <img src="img/pop-mart-logo.png" alt="Popmart Logo" class="login-logo">
        </div>
        <h2 class="fw-bold mb-4">Create your account</h2>

        <form id="signupForm" class="text-start" novalidate>
          <div class="row g-2">
            <div class="mt-3 position-relative">
              <label for="firstName" class="form-label">First Name</label>
              <input type="text" class="form-control" id="firstName" name="firstName" placeholder="first name" required>
            </div>
            <div class="mt-3 position-relative">
              <label for="lastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="lastName" placeholder="last name" required>
            </div>
          </div>

          <div class="mt-3 position-relative">
            <label for="signupEmail" class="form-label">Email Address</label>
            <input type="text" class="form-control" id="signupEmail" name="signupEmail" placeholder="someone@email.com" required>
          </div>

          <div class="mt-3 position-relative">
            <label for="contactNumber" class="form-label">Contact Number</label>
            <div class="input-group">
              <span class="input-group-text bg-light" id="countryCode">+63</span>
              <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="9XXXXXXXXX" maxlength="10" inputmode="numeric" required>
            </div>
          </div>

          <div class="mt-3 position-relative">
            <label for="signupPassword" class="form-label">Password</label>
            <input type="password" class="form-control pe-5" id="signupPassword" name="signupPassword" placeholder="Enter your password" autocomplete="off" required>
            <i class="bi bi-eye toggle-password icon-eye"></i>
          </div>

          <div class="mt-3 position-relative">
            <label for="confirmPassword" class="form-label">Re-type your password</label>
            <input type="password" class="form-control pe-5" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" autocomplete="off" required>
            <i class="bi bi-eye toggle-password icon-eye"></i>
          </div>

          <button type="submit" class="btn btn-danger w-100 rounded-pill mt-4">Sign Up</button>
        </form>

        <p class="mt-3 mb-0 small">
          Already have an account?
          <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal" class="text-decoration-none fw-semibold text-primary">Login Here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>