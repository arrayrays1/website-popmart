<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal">
      <div class="modal-body text-center p-5">
        <h2 class="fw-bold mb-2">WELCOME TO</h2>
        <div class="mb-4">
          <img src="/website-popmart/img/pop-mart-logo.png" alt="Popmart Logo" class="login-logo">
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
          
          <div id="loginFeedback" class="mt-3"></div>


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

<!-- ADD TO CART CONFIRM MODAL -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addToCartModalLabel">Confirm Add to Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="cartModalImage" src="" alt="Product" style="width: 230px; height: auto;" class="mb-3">
        <h5 id="cartModalTitle" class="mb-2"></h5>
        <div id="cartModalStock" class="text-muted mb-3"></div>
        <div class="d-flex justify-content-center align-items-center mb-3" style="gap: 8px;">
          <button class="btn btn-outline-secondary btn-sm" id="qtyMinus">-</button>
          <input type="text" id="qtyInput" class="form-control text-center" style="width: 60px;" value="1">
          <button class="btn btn-outline-secondary btn-sm" id="qtyPlus">+</button>
        </div>
        <div class="mb-2">Price: <span id="cartModalPrice"></span></div>
        <div class="fs-5 fw-semibold">Total: <span id="cartModalTotal"></span></div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmAddToCartBtn">Confirm Add to Cart</button>
      </div>
    </div>
  </div>
  <!-- data holders -->
  <input type="hidden" id="cartModalProductId" value="">
  <input type="hidden" id="cartModalUnitPrice" value="">
</div>

<!-- Global toast container -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1100">
  <div id="globalToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 260px;">
    <div class="d-flex">
      <div class="toast-body" id="globalToastMessage">Added to your cart!</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  <div id="globalToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 260px;">
    <div class="d-flex">
      <div class="toast-body" id="globalToastErrorMessage">Something went wrong.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  
</div>

<!-- PRODUCT DETAIL MODAL (with Reviews) -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productDetailModalLabel">Product Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5 text-center">
            <img id="detailModalImage" src="" alt="Product" style="max-width: 100%; height: auto;" class="mb-3">
          </div>
          <div class="col-md-7">
            <h4 id="detailModalTitle" class="mb-2"></h4>
            <div id="detailModalRating" class="mb-2">
              <span class="text-warning" id="detailStars"></span>
              <span class="text-muted ms-2" id="detailAvgRating"></span>
              <span class="text-muted" id="detailTotalReviews"></span>
            </div>
            <p class="fs-4 fw-bold text-danger mb-2" id="detailModalPrice"></p>
            <p class="mb-2" id="detailModalStock"></p>
            <p class="text-muted" id="detailModalDescription"></p>
            <button class="btn btn-danger w-100 mt-3" id="detailAddToCartBtn">Add to Cart</button>
          </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Reviews Section -->
        <div id="reviewsSection">
          <h5 class="mb-3">Customer Reviews</h5>
          
          <!-- Reviews List -->
          <div id="reviewsList">
            <p class="text-muted">Loading reviews...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="detailModalProductId" value="">
  <input type="hidden" id="detailModalProductStock" value="">
</div>

<!-- WRITE REVIEW MODAL -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="writeReviewModalLabel">Write a Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
            <img id="reviewModalImage" src="" alt="Product" style="width: 60px; height: 60px; object-fit: cover;" class="me-3 rounded">
            <h6 class="mb-0" id="reviewModalProductName">Product Name</h6>
        </div>
        
        <div id="submitReviewForm" class="p-1">
            <div class="mb-3">
              <label class="form-label">Your Rating *</label>
              <div id="ratingStars" class="fs-4">
                <i class="bi bi-star rating-star" data-rating="1"></i>
                <i class="bi bi-star rating-star" data-rating="2"></i>
                <i class="bi bi-star rating-star" data-rating="3"></i>
                <i class="bi bi-star rating-star" data-rating="4"></i>
                <i class="bi bi-star rating-star" data-rating="5"></i>
              </div>
              <input type="hidden" id="selectedRating" value="0">
            </div>
            <div class="mb-3">
              <label for="reviewText" class="form-label">Your Review (optional)</label>
              <textarea class="form-control" id="reviewText" rows="3" placeholder="Share your experience with this product..."></textarea>
            </div>
            <button class="btn btn-primary w-100" id="submitReviewBtn">Submit Review</button>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="reviewModalProductId" value="">
  <input type="hidden" id="reviewModalOrderId" value="">
</div>

<!-- SIGNUP MODAL -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content signup-modal">
      <div class="modal-body text-center p-5">
        <div class="mb-2">
          <img src="/website-popmart/img/pop-mart-logo.png" alt="Popmart Logo" class="login-logo">
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

          <div id="signupFeedback" class="mt-3"></div>

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