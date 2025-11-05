<?php 
  $activePage = 'cart';
  include 'includes/header.php';
  include 'includes/modals.php';
  if (!isset($_SESSION['user_id'])) {
    echo '<div class="container my-5"><div class="alert alert-warning mt-5">Please log in to view your cart.</div></div>';
    include 'includes/script.php';
    exit;
  }
  $cartData = include __DIR__ . '/db/cart_get.php';
?>

<body>
<div class="container my-5">
    <h2 class="mb-4 custom-h2-cart">My Cart</h2>
    <?php if ($cartData['count'] === 0): ?>
      <p class="mb-4 text-center">Your cart is currently empty.</p>
    <?php else: ?>
    <div class="row">
      <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3">
          <input type="checkbox" class="form-check-input me-2" id="selectAll">
          <label for="selectAll" class="form-check-label">Select all</label>
        </div>
        <?php foreach ($cartData['items'] as $item): ?>
        <div class="d-flex align-items-start border-bottom py-3 cart-item" data-product-id="<?php echo (int)$item['product_id']; ?>">
          <input type="checkbox" class="form-check-input me-3 mt-2 item-check">
          <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="me-3" style="width: 150px; height: auto;">
          <div class="flex-grow-1">
            <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
            <p class="text-muted mb-1">1 Box</p>
            <p class="fw-bold mb-2">Php <?php echo number_format((float)$item['unit_price'], 2); ?></p>
            <div class="d-flex align-items-center" style="gap:8px;">
              <button class="btn btn-outline-secondary btn-sm qty-minus">-</button>
              <input type="text" class="form-control form-control-sm text-center qty-input" value="<?php echo (int)$item['quantity']; ?>" style="width: 60px;">
              <button class="btn btn-outline-secondary btn-sm qty-plus">+</button>
              <a href="#" class="ms-2 text-danger text-decoration-none remove-item">REMOVE</a>
            </div>
          </div>
          <div class="text-end fw-semibold" style="min-width:120px;">Php <span class="item-total"><?php echo number_format((float)$item['total'], 2); ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-lg-4">
        <div class="border p-4" id="summaryBox">
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span id="subtotal">Php <?php echo number_format((float)$cartData['subtotal'], 2); ?></span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Shipping</span>
            <span class="text-muted">Calculated at next step</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-bold mb-3">
            <span>Total(<?php echo (int)$cartData['count']; ?>)</span>
            <span id="grandTotal">Php <?php echo number_format((float)$cartData['subtotal'], 2); ?></span>
          </div>
          <button class="btn btn-primary w-100" id="checkoutBtn">CHECK OUT</button>
        </div>
      </div>
    </div>
    <?php endif; ?>
<!--
  <div class="row">
    // left column (cart items)
    <div class="col-lg-8">
      <div class="d-flex align-items-center mb-3">
        <input type="checkbox" class="form-check-input me-2">
        <label class="form-check-label">Select all</label>
      </div>

      //cart item
      <div class="d-flex align-items-start border-bottom py-3">
        <input type="checkbox" class="form-check-input me-3 mt-2">
        <img src="img/products-img-banner/products-mofusand/mofusand-1.png" alt="Mofusand Pastries" class="me-3" style="width: 150px; height: auto;">
        <div class="flex-grow-1">
          <h5 class="mb-1">MOFUSAND Pastries</h5>
          <p class="text-muted mb-1">1 Box</p>
          <p class="fw-bold">Php 300.00</p>
          <div class="d-flex align-items-center">
            <button class="btn btn-outline-secondary btn-sm">-</button>
            <input type="text" class="form-control form-control-sm mx-2 text-center" value="1" style="width: 60px;">
            <button class="btn btn-outline-secondary btn-sm">+</button>
            <a href="#" class="ms-3 text-danger text-decoration-none">REMOVE</a>
          </div>
        </div>
      </div>

      //another cart item
      <div class="d-flex align-items-start border-bottom py-3">
        <input type="checkbox" class="form-check-input me-3 mt-2">
        <img src="img/products-img-banner/products-smiski/smiski-5.png" alt="Smiski Birthday" class="me-3" style="width: 150px; height: auto;">
        <div class="flex-grow-1">
          <h5 class="mb-1">SMISKI Birthday Series</h5>
          <p class="text-muted mb-1">1 Box</p>
          <p class="fw-bold">Php 300.00</p>
          <div class="d-flex align-items-center">
            <button class="btn btn-outline-secondary btn-sm">-</button>
            <input type="text" class="form-control form-control-sm mx-2 text-center" value="1" style="width: 60px;">
            <button class="btn btn-outline-secondary btn-sm">+</button>
            <a href="#" class="ms-3 text-danger text-decoration-none">REMOVE</a>
          </div>
        </div>
      </div>
    </div>

    // right rolumn (summary) 
    <div class="col-lg-4">
      <div class="border p-4">
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal</span>
          <span>Php 600.00</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Shipping</span>
          <span class="text-muted">Calculated at next step</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold mb-3">
          <span>Total(2)</span>
          <span>Php 600.00 </span>
        </div>
        <button class="btn btn-primary w-100">CHECK OUT</button>
      </div>
    </div>
  </div>
</div> -->


<?php include 'includes/script.php'; ?>
<script>
  (function(){
    function peso(n){ return new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(n); }
    function clamp(q){ q=parseInt(q||'1',10); if(isNaN(q)||q<1) q=1; return q; }
    function recalcRow($row){
      var unit = parseFloat($row.find('.fw-bold').text().replace(/[^0-9.]/g,''));
      var qty = clamp($row.find('.qty-input').val());
      $row.find('.qty-input').val(qty);
      var total = unit * qty;
      $row.find('.item-total').text(total.toFixed(2));
      return total;
    }
    function recalcSummary(){
      var sum = 0;
      $('.cart-item').each(function(){ sum += parseFloat($(this).find('.item-total').text()); });
      $('#subtotal, #grandTotal').text('Php ' + sum.toFixed(2));
    }
    $(document).on('click','.qty-minus',function(){
      var $row = $(this).closest('.cart-item');
      var $input = $row.find('.qty-input');
      $input.val(clamp($input.val())-1); if(parseInt($input.val(),10)<1){ $input.val(1); }
      recalcRow($row); recalcSummary();
      saveQty($row);
    });
    $(document).on('click','.qty-plus',function(){
      var $row = $(this).closest('.cart-item');
      var $input = $row.find('.qty-input');
      $input.val(clamp($input.val())+1);
      recalcRow($row); recalcSummary();
      saveQty($row);
    });
    $(document).on('input','.qty-input',function(){
      var $row = $(this).closest('.cart-item');
      recalcRow($row); recalcSummary();
      saveQty($row);
    });
    $(document).on('click','.remove-item',function(e){ e.preventDefault(); var $row=$(this).closest('.cart-item');
      updateServer($row.data('product-id'),0,function(){ $row.remove(); recalcSummary(); });
    });
    function saveQty($row){ updateServer($row.data('product-id'), clamp($row.find('.qty-input').val())); }
    function updateServer(productId, quantity, cb){
      $.post('/website-popmart/db/cart_update.php',{product_id:productId,quantity:quantity})
        .done(function(r){ if(typeof r==='string'){ try{ r=JSON.parse(r);}catch(e){} }
          if(r && r.success){ if(cb) cb(); } else { console.warn('Failed to update cart'); }
        });
    }

    // added here to make the checkout button works
    $(document).on('click','#checkoutBtn',function(){
      $(this).prop('disabled', true).text('Processing...');
      $.post('/website-popmart/db/checkout.php', {}, function(r){
        if(typeof r === 'string'){ try{ r = JSON.parse(r); }catch(e){} }
        if(r && r.success){
          alert('Checkout successful! Your order has been placed.');
          window.location.href = '/website-popmart/index.php';
        } else {
          alert('Checkout failed: ' + (r.message || 'Unknown error'));
          $('#checkoutBtn').prop('disabled', false).text('CHECK OUT');
        }
      }, 'json').fail(function(xhr, status, error){
        alert('Network error. Please try again.');
        $('#checkoutBtn').prop('disabled', false).text('CHECK OUT');
      });
    });
  })();
</script>