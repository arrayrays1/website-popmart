<?php 
  $activePage = 'cart';
  include 'includes/header.php';
  include 'includes/modals.php';
  if (!isset($_SESSION['user_id'])) {
    echo '<div class="container my-5"><div class="alert alert-warning" style="margin-top: 100px;">Please log in to view your cart.</div></div>';
    include 'includes/script.php';
    exit;
  }
  $cartData = include __DIR__ . '/db/cart_get.php';
?>

<body>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 custom-h2-cart">Shopping Cart</h2>
      <div>
        <a href="/website-popmart/index.php" class="btn btn-outline-secondary btn-sm me-2">← Back</a>
        <button class="btn btn-outline-danger btn-sm me-2" id="deleteSelectedBtn">Delete Selected</button>
        <button class="btn btn-outline-secondary btn-sm" id="editBtn">Edit</button>
      </div>
    </div>
    <?php if ($cartData['count'] === 0): ?>
      <p class="mb-4 text-center">Your cart is currently empty.</p>
    <?php else: ?>
    <div class="row">
      <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3">
          <input type="checkbox" class="form-check-input me-2" id="selectAll">
          <label for="selectAll" class="form-check-label">Select All</label>
        </div>
        <?php foreach ($cartData['items'] as $item): ?>
        <div class="d-flex align-items-start border-bottom py-3 cart-item" data-product-id="<?php echo (int)$item['product_id']; ?>" data-stock="<?php echo (int)$item['stock']; ?>">
          <input type="checkbox" class="form-check-input me-3 mt-2 item-check" checked>
          <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="me-3" style="width: 150px; height: auto;">
          <div class="flex-grow-1">
            <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
            <p class="mb-1 <?php echo (int)$item['stock'] <= 3 ? 'text-danger' : ''; ?>">
              Stock: <?php echo (int)$item['stock']; ?> remaining
            </p>
            <p class="fw-bold mb-2">Php <?php echo number_format((float)$item['unit_price'], 2); ?></p>
            <div class="d-flex align-items-center" style="gap:8px;">
              <button class="btn btn-outline-secondary btn-sm qty-minus">-</button>
              <input type="text" class="form-control form-control-sm text-center qty-input" value="<?php echo (int)$item['quantity']; ?>" style="width: 60px;" max="<?php echo (int)$item['stock']; ?>">
              <button class="btn btn-outline-secondary btn-sm qty-plus">+</button>
              <a href="#" class="ms-2 text-danger text-decoration-none remove-item" style="display:none;">REMOVE</a>
            </div>
          </div>
          <div class="text-end fw-semibold" style="min-width:120px;">Php <span class="item-total"><?php echo number_format((float)$item['total'], 2); ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-lg-4">
        <div class="border p-4" id="summaryBox">
          <div class="d-flex justify-content-between mb-2">
            <span>Selected Items:</span>
            <span id="selectedCount">0</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span id="subtotal">Php 0.00</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Shipping</span>
            <span class="text-muted">Calculated at next step</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-bold mb-3">
            <span>Total:</span>
            <span id="grandTotal">Php 0.00</span>
          </div>
          <button class="btn btn-dark w-100" id="checkoutBtn" style="background-color: #333; color: white;">Checkout</button>
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
    var editMode = false;
    
    function peso(n){ return new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(n); }
    function clamp(q, max){ 
      q=parseInt(q||'1',10); 
      if(isNaN(q)||q<1) q=1; 
      if(max && q>max) q=max;
      return q; 
    }
    
    function getStock($row){
      var s = $row.data('stock');
      return (typeof s !== 'undefined' && s !== '') ? parseInt(s, 10) : 999999;
    }
    
    function recalcRow($row){
      var unit = parseFloat($row.find('.fw-bold').text().replace(/[^0-9.]/g,''));
      var stock = getStock($row);
      var qty = clamp($row.find('.qty-input').val(), stock);
      $row.find('.qty-input').val(qty);
      $row.find('.qty-input').attr('max', stock);
      
      $row.find('.qty-minus').prop('disabled', qty <= 1);
      $row.find('.qty-plus').prop('disabled', qty >= stock);
      
      var total = unit * qty;
      $row.find('.item-total').text(total.toFixed(2));
      return total;
    }
    
    function recalcSummary(){
      var sum = 0;
      var count = 0;
      $('.cart-item').each(function(){
        var $row = $(this);
        if($row.find('.item-check').is(':checked')){
          sum += parseFloat($row.find('.item-total').text());
          count += parseInt($row.find('.qty-input').val(), 10);
        }
      });
      $('#subtotal, #grandTotal').text('Php ' + sum.toFixed(2));
      $('#selectedCount').text(count);
    }
    
    function toggleEditMode(){
      editMode = !editMode;
      if(editMode){
        $('#editBtn').text('Cancel');
        $('.remove-item').show();
        $('.qty-minus, .qty-plus, .qty-input').prop('disabled', true);
      } else {
        $('#editBtn').text('Edit');
        $('.remove-item').hide();
        $('.qty-minus, .qty-plus, .qty-input').prop('disabled', false);
      }
    }
    
    // Select All functionality
    $('#selectAll').on('change', function(){
      $('.item-check').prop('checked', $(this).is(':checked'));
      recalcSummary();
    });
    
    // Individual checkbox change
    $(document).on('change', '.item-check', function(){
      var allChecked = $('.item-check').length === $('.item-check:checked').length;
      $('#selectAll').prop('checked', allChecked);
      recalcSummary();
    });
    
    // Edit button
    $('#editBtn').on('click', toggleEditMode);
    
    // Quantity controls
    $(document).on('click','.qty-minus',function(){
      if(editMode) return;
      var $row = $(this).closest('.cart-item');
      var $input = $row.find('.qty-input');
      var stock = getStock($row);
      var newVal = clamp($input.val(), stock) - 1;
      if(newVal < 1) newVal = 1;
      $input.val(newVal);
      recalcRow($row); 
      recalcSummary();
      saveQty($row);
    });
    
    $(document).on('click','.qty-plus',function(){
      if(editMode) return;
      var $row = $(this).closest('.cart-item');
      var $input = $row.find('.qty-input');
      var stock = getStock($row);
      var newVal = clamp($input.val(), stock) + 1;
      if(newVal > stock) newVal = stock;
      $input.val(newVal);
      recalcRow($row); 
      recalcSummary();
      saveQty($row);
    });
    
    $(document).on('input','.qty-input',function(){
      if(editMode) return;
      var $row = $(this).closest('.cart-item');
      recalcRow($row); 
      recalcSummary();
      saveQty($row);
    });
    
    // Remove item
    $(document).on('click','.remove-item',function(e){ 
      e.preventDefault(); 
      var $row=$(this).closest('.cart-item');
      updateServer($row.data('product-id'),0,function(){ 
        $row.remove(); 
        recalcSummary();
        if($('.cart-item').length === 0){
          location.reload();
        }
      });
    });
    
    function saveQty($row){ 
      updateServer($row.data('product-id'), clamp($row.find('.qty-input').val(), getStock($row))); 
    }
    
    function updateServer(productId, quantity, cb){
      $.post('/website-popmart/db/cart_update.php',{product_id:productId,quantity:quantity})
        .done(function(r){ 
          if(typeof r==='string'){ try{ r=JSON.parse(r);}catch(e){} }
          if(r && r.success){ 
            if(cb) cb(); 
          } else { 
            console.warn('Failed to update cart'); 
          }
        });
    }

    $('#deleteSelectedBtn').on('click', function(){
      var selectedIds = [];
      $('.cart-item').each(function(){
        var $row = $(this);
        if($row.find('.item-check').is(':checked')){
          selectedIds.push($row.data('product-id'));
        }
      });
      
      if(selectedIds.length === 0){
        alert('Please select items to delete.');
        return;
      }
      
      if(!confirm('Are you sure you want to delete ' + selectedIds.length + ' item(s)?')){
        return;
      }
      
      $.post('/website-popmart/db/cart_remove_bulk.php', {product_ids: selectedIds}, function(r){
        if(typeof r === 'string'){ try{ r=JSON.parse(r);}catch(e){} }
        if(r && r.success){
          location.reload();
        } else {
          alert('Failed to delete items.');
        }
      });
    });
    
    $(document).on('click','#checkoutBtn',function(){
      var selectedItems = [];
      $('.cart-item').each(function(){
        var $row = $(this);
        if($row.find('.item-check').is(':checked')){
          selectedItems.push({
            product_id: $row.data('product-id'),
            quantity: parseInt($row.find('.qty-input').val(), 10)
          });
        }
      });
      
      if(selectedItems.length === 0){
        alert('Please select at least one item to checkout.');
        return;
      }
      
      // Create a form to submit items via POST
      var form = $('<form>', {
        'method': 'POST',
        'action': '/website-popmart/checkout.php'
      });
      
      $.each(selectedItems, function(index, item){
        form.append($('<input>', {
          'type': 'hidden',
          'name': 'items[' + index + '][product_id]',
          'value': item.product_id
        }));
        form.append($('<input>', {
          'type': 'hidden',
          'name': 'items[' + index + '][quantity]',
          'value': item.quantity
        }));
      });
      
      $('body').append(form);
      form.submit();
    });
    
    recalcSummary();
  })();
</script>