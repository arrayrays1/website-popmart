<?php 
  $activePage = 'orders';
  include 'includes/header.php';
  include 'includes/modals.php';
  
  if (!isset($_SESSION['user_id'])) {
    echo '<div class="container my-5"><div class="alert alert-warning mt-5">Please log in to view your orders.</div></div>';
    include 'includes/script.php';
    include 'includes/footer.php';
    exit;
  }
  
  $result = include __DIR__ . '/db/orders_get.php';
  $orders = $result['success'] ? $result['orders'] : [];
?>

<div class="container my-5 pt-5">
    <h2 class="mb-4">My Orders</h2>
    
    <?php if (empty($orders)): ?>
      <div class="alert alert-info">You haven't placed any orders yet.</div>
      <a href="products.php" class="btn btn-primary">Start Shopping</a>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
              <span class="fw-bold">Order #<?php echo date("mdy", strtotime($order['created_at'])) . "-" . str_pad($order['id'], 4, "0", STR_PAD_LEFT); ?></span>
              <span class="text-muted mx-2">|</span>
              <span class="text-muted"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
            </div>
            <div>
              <span class="badge <?php
                switch($order['status']) {
                  case 'Pending': echo 'bg-warning text-dark'; break;
                  case 'To Ship': echo 'bg-info'; break;
                  case 'To Deliver': echo 'bg-primary'; break;
                  case 'Delivered': echo 'bg-success'; break;
                  default: echo 'bg-secondary';
                }
              ?>"><?php echo htmlspecialchars($order['status']); ?></span>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-3">
                <strong>Total:</strong> Php <?php echo number_format((float)$order['total'], 2); ?>
                <span class="mx-2">|</span>
                <strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
            </div>
            
            <hr>
            
            <div class="list-group list-group-flush">
              <?php foreach ($order['items'] as $item): ?>
                <div class="list-group-item px-0 py-3">
                  <div class="d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                         style="width: 80px; height: 80px; object-fit: cover;" class="me-3 rounded border">
                    <div class="flex-grow-1">
                      <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                      <p class="mb-1 text-muted">
                        Qty: <?php echo $item['quantity']; ?> x Php <?php echo number_format((float)$item['unit_price'], 2); ?>
                      </p>
                    </div>
                    <div>
                      <?php if ($order['status'] === 'Delivered'): ?>
                        <?php if (!empty($item['is_reviewed'])): ?>
                            <button class="btn btn-secondary btn-sm" disabled>
                                Reviewed <i class="bi bi-check"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-danger btn-sm write-review-btn"
                                    data-order-id="<?php echo $order['id']; ?>"
                                    data-product-id="<?php echo $item['product_id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($item['name']); ?>"
                                    data-product-image="<?php echo htmlspecialchars($item['image_path']); ?>">
                              Write Review
                            </button>
                        <?php endif; ?>
                      <?php elseif ($order['status'] !== 'cancelled'): ?>
                        <span class="text-muted small">Review available when order is delivered</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>