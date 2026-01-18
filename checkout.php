<?php 
  $activePage = 'checkout';
  include 'includes/header.php';
  include 'includes/modals.php';
  if (!isset($_SESSION['user_id'])) {
    echo '<div class="container my-5"><div class="alert alert-warning mt-5">Please log in to proceed with checkout.</div></div>';
    include 'includes/script.php';
    exit;
  }
  
  // Get checkout data
  $checkoutData = include __DIR__ . '/db/checkout_get.php';
  
  if (isset($checkoutData['error'])) {
    echo '<div class="container my-5"><div class="alert alert-danger mt-5">' . htmlspecialchars($checkoutData['error']) . '</div></div>';
    echo '<div class="container my-5"><a href="/website-popmart/cart.php" class="btn btn-primary">← Back to Cart</a></div>';
    include 'includes/script.php';
    exit;
  }
  
  $user = $checkoutData['user'];
  $items = $checkoutData['items'];
  $subtotal = $checkoutData['subtotal'];
  $shippingFee = $checkoutData['shipping_fee'];
  $total = $checkoutData['total'];
  $itemCount = $checkoutData['item_count'];
?>

<body>
<div class="container my-5" style="margin-top: 100px !important; max-width: 900px;">
    
    <!-- Header: Back button on left, Checkout title centered -->
    <div class="border p-3 mb-4 d-flex align-items-center position-relative">
      <a href="/website-popmart/cart.php" class="btn btn-outline-secondary btn-sm">← Back</a>
      <h4 class="mb-0 position-absolute start-50 translate-middle-x">Checkout</h4>
    </div>
    
    <!-- Order Summary Section -->
    <div class="border p-4 mb-4">
      <h6 class="mb-3"><strong>Order Summary</strong></h6>
      <?php foreach ($items as $item): ?>
      <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
        <div class="border me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #f8f9fa; flex-shrink: 0;">
          <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-width: 50px; max-height: 50px; object-fit: contain;">
        </div>
        <div class="flex-grow-1">
          <div class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></div>
          <div class="text-muted small">Qty: <?php echo (int)$item['quantity']; ?></div>
        </div>
        <div class="text-end">
          <span>Php <?php echo number_format($item['total'], 2); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      
      <div class="d-flex justify-content-between mb-2">
        <span>Subtotal (<?php echo $itemCount; ?> items)</span>
        <span>Php <?php echo number_format($subtotal, 2); ?></span>
      </div>
      <div class="d-flex justify-content-between mb-2">
        <span>Shipping Fee</span>
        <span>Php <?php echo number_format($shippingFee, 2); ?></span>
      </div>
      <div class="d-flex justify-content-between fw-bold mt-2">
        <span>Total</span>
        <span>Php <?php echo number_format($total, 2); ?></span>
      </div>
    </div>
    
    <!-- Shipping Address Section -->
    <div class="border p-4 mb-4">
      <h6 class="mb-3"><strong>Shipping Address</strong></h6>
      <div id="shippingAddress">
        <p class="mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <p class="mb-1"><?php echo htmlspecialchars($user['contact_number']); ?></p>
        <p class="mb-3" id="addressDisplay">Not set</p>
        <button class="btn btn-outline-secondary btn-sm" id="changeAddressBtn">Change Address</button>
      </div>
      
      <div id="addressForm" style="display: none;">
        <form id="addressFormElement">
          <div class="mb-3">
            <textarea class="form-control" id="address" name="address" rows="3" placeholder="123 Main Street, Barangay Sample&#10;Manila, Metro Manila, 1000" required></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Save Address</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelAddressBtn">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Payment Method Section -->
    <div class="border p-4 mb-4">
      <h6 class="mb-2"><strong>Payment Method</strong></h6>
      <p class="text-muted small mb-3">Select your preferred payment method *</p>
      
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentCOD" value="COD">
        <label class="form-check-label" for="paymentCOD">
          <strong>Cash on Delivery (COD)</strong>
        </label>
        <div class="text-muted small ms-4">Pay when you receive your order</div>
      </div>

      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentGCash" value="GCash">
        <label class="form-check-label" for="paymentGCash">
          <strong>GCash</strong>
        </label>
        <div class="text-muted small ms-4">Pay via your GCash account</div>
      </div>

      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMaya" value="Maya">
        <label class="form-check-label" for="paymentMaya">
          <strong>Maya</strong>
        </label>
        <div class="text-muted small ms-4">Pay via your Maya wallet</div>
      </div>

      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentCard" value="Credit/Debit Card">
        <label class="form-check-label" for="paymentCard">
          <strong>Credit/Debit Card</strong>
        </label>
        <div class="text-muted small ms-4">Pay securely with Visa or Mastercard</div>
      </div>
    </div>
    
    <!-- Total Payment and Place Order Section -->
    <div class="border p-4">
      <div class="d-flex justify-content-between mb-3">
        <span>Total Payment</span>
        <span class="fw-bold">Php <?php echo number_format($total, 2); ?></span>
      </div>
      <button class="btn w-100" id="placeOrderBtn" style="background-color: #9ca3af; color: white; border: none;" disabled>Place Order</button>
      <p class="text-muted small text-center mt-2 mb-0">By placing order, you agree to our Terms & Conditions</p>
    </div>
</div>

<?php include 'includes/script.php'; ?>
<script>
$(document).ready(function(){
  var selectedItems = <?php echo json_encode(array_map(function($item) { return ['product_id' => $item['product_id'], 'quantity' => $item['quantity']]; }, $items)); ?>;
  var shippingAddress = localStorage.getItem('shippingAddress') || '';
  
  // Load saved address
  if (shippingAddress) {
    $('#addressDisplay').text(shippingAddress);
    $('#address').val(shippingAddress);
  }
  
  // Change Address button
  $('#changeAddressBtn').on('click', function(){
    $('#shippingAddress').hide();
    $('#addressForm').show();
  });
  
  $('#cancelAddressBtn').on('click', function(){
    $('#addressForm').hide();
    $('#shippingAddress').show();
  });
  
  // Save address
  $('#addressFormElement').on('submit', function(e){
    e.preventDefault();
    var address = $('#address').val().trim();
    if (!address) {
      alert('Please enter a valid address');
      return;
    }
    localStorage.setItem('shippingAddress', address);
    $('#addressDisplay').text(address);
    $('#addressForm').hide();
    $('#shippingAddress').show();
    validateForm();
  });
  
  // Payment method change
  $(document).on('change', 'input[name="paymentMethod"]', function(){
    validateForm();
  });
  
  function validateForm(){
    var paymentMethod = $('input[name="paymentMethod"]:checked').val();
    var address = $('#addressDisplay').text();
    var hasAddress = address && address !== 'Not set' && address.trim() !== '';
    
    if (paymentMethod && hasAddress) {
      $('#placeOrderBtn').prop('disabled', false);
      $('#placeOrderBtn').css({'background-color': '#dc2626', 'cursor': 'pointer'});
    } else {
      $('#placeOrderBtn').prop('disabled', true);
      $('#placeOrderBtn').css({'background-color': '#9ca3af', 'cursor': 'not-allowed'});
    }
  }
  
  // Place Order
  $(document).on('click', '#placeOrderBtn', function(e){
    e.preventDefault();
    
    if ($(this).prop('disabled')) {
      return;
    }
    
    var paymentMethod = $('input[name="paymentMethod"]:checked').val();
    var address = $('#addressDisplay').text();
    
    if (!paymentMethod) {
      alert('Please select a payment method');
      return;
    }
    
    if (!address || address === 'Not set' || address.trim() === '') {
      alert('Please enter your shipping address');
      $('#changeAddressBtn').click();
      return;
    }
    
    var $btn = $(this);
    $btn.prop('disabled', true).text('Processing...').css('background-color', '#9ca3af');
    
    // Prepare items array for POST
    var formData = {
      payment_method: paymentMethod,
      shipping_address: address
    };
    
    for (var i = 0; i < selectedItems.length; i++) {
      formData['items[' + i + '][product_id]'] = selectedItems[i].product_id;
      formData['items[' + i + '][quantity]'] = selectedItems[i].quantity;
    }
    
    $.ajax({
      url: '/website-popmart/db/checkout.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function(r){
        if(r && r.success){
          localStorage.removeItem('shippingAddress');
          alert('Order placed successfully! You can now view your order details.');
          window.location.href = '/website-popmart/orders.php';
        } else {
          alert('Checkout failed: ' + (r.message || 'Unknown error'));
          $btn.prop('disabled', false).text('Place Order');
          validateForm();
        }
      },
      error: function(xhr, status, error){
        var errorMsg = 'Network error. Please try again.';
        try {
          var response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMsg = response.message;
          }
        } catch(e) {}
        alert(errorMsg);
        $btn.prop('disabled', false).text('Place Order');
        validateForm();
      }
    });
  });
  
  // Initial validation
  validateForm();
});
</script>
</body>
</html>
