$(document).on('click', '#logoutBtn', function(e) {
    e.preventDefault();
    
    if (confirm('Are you sure you want to logout?')) {
        $.ajax({
            type: 'POST',
            url: '/website-popmart/db/logout_process.php',
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Error logging out. Please try again.');
            }
        });
    }
});

$(document).on('click', 'a[href$="/cart.php"], a[href$="cart.php"]', function(e) {
    if (window.IS_LOGGED_IN) return; 
    e.preventDefault();
    if ($('#loginModal').length) {
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
        const $box = $('#loginFeedback');
        if ($box.length) {
            $box.html('<div class="alert alert-warning py-2 px-3 mb-0" role="alert">Please log in to add items to your cart.</div>');
        }
    } else {
        window.location.href = '/website-popmart/index.php#login';
    }
});

$(function(){
    function peso(n){ return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(n); }
    function clampQty(q, max){ q = parseInt(q||'1',10); if(isNaN(q)||q<1) q=1; if(max && q>max) q=max; return q; }
    function updateCartBadge(){
        $.get('/website-popmart/db/cart_count.php', function(r){
            if (typeof r === 'string') { try { r = JSON.parse(r); } catch(_e) { r = { count: 0 }; } }
            var count = (r && typeof r.count !== 'undefined') ? r.count : 0;
            var badge = document.getElementById('cartCount');
            if (!badge) return;
            if (count > 0) { badge.style.display = 'inline-block'; badge.textContent = count; }
            else { badge.style.display = 'none'; badge.textContent = '0'; }
        });
    }
    if (window.IS_LOGGED_IN) updateCartBadge();

    function generateStars(rating, maxStars) {
        maxStars = maxStars || 5;
        var html = '';
        for (var i = 1; i <= maxStars; i++) {
            if (i <= rating) {
                html += '<i class="bi bi-star-fill text-warning"></i>';
            } else if (i - 0.5 <= rating) {
                html += '<i class="bi bi-star-half text-warning"></i>';
            } else {
                html += '<i class="bi bi-star text-warning"></i>';
            }
        }
        return html;
    }

    $(document).on('click', '.add-to-cart', function(e){
        if (!window.IS_LOGGED_IN) {
            e.preventDefault();
            try { new bootstrap.Modal(document.getElementById('loginModal')).show(); } catch(_e) {}
            return;
        }
        e.preventDefault();
        const $card = $(this).closest('.card');
        const productId = $(this).data('product-id');
        const title = $card.find('h5').text().trim();
        const img = $card.find('img').attr('src');
        const unitPrice = parseFloat($(this).data('price')) || parseFloat(($card.find('.card-text').text().replace(/[^0-9.]/g,'')));
        const stock = parseInt($(this).data('stock')) || 0;

        if (stock <= 0) {
            try {
                var toastErrEl = document.getElementById('globalToastError');
                var msg = document.getElementById('globalToastErrorMessage');
                if (msg) msg.textContent = 'Sorry, this product is out of stock';
                var toastErr = new bootstrap.Toast(toastErrEl, { delay: 2000 });
                toastErr.show();
            } catch(_e) { alert('Sorry, this product is out of stock'); }
            return;
        }

        $('#cartModalProductId').val(productId);
        $('#cartModalUnitPrice').val(unitPrice);
        $('#cartModalTitle').text(title);
        $('#cartModalImage').attr('src', img);
        $('#cartModalPrice').text(peso(unitPrice));
        $('#cartModalStock').text('Stock Remaining: ' + stock);
        $('#qtyInput').val(1).attr('max', stock);
        $('#cartModalTotal').text(peso(unitPrice));
        var atcModal = new bootstrap.Modal(document.getElementById('addToCartModal'));
        atcModal.show();
    });

    function recalc(){
        var max = parseInt($('#qtyInput').attr('max')) || 999;
        const qty = clampQty($('#qtyInput').val(), max);
        const unit = parseFloat($('#cartModalUnitPrice').val());
        $('#qtyInput').val(qty);
        $('#cartModalTotal').text(peso(qty * unit));
    }
    $(document).on('click', '#qtyMinus', function(){ 
        var max = parseInt($('#qtyInput').attr('max')) || 999;
        $('#qtyInput').val(clampQty($('#qtyInput').val(), max)-1); 
        recalc(); 
    });
    $(document).on('click', '#qtyPlus', function(){ 
        var max = parseInt($('#qtyInput').attr('max')) || 999;
        var current = clampQty($('#qtyInput').val(), max);
        if (current < max) {
            $('#qtyInput').val(current + 1); 
        }
        recalc(); 
    });
    $(document).on('input', '#qtyInput', recalc);

    $(document).on('click', '#confirmAddToCartBtn', function(){
        const productId = $('#cartModalProductId').val();
        var max = parseInt($('#qtyInput').attr('max')) || 999;
        const quantity = clampQty($('#qtyInput').val(), max);
        $.ajax({
            type: 'POST',
            url: '/website-popmart/db/cart_add.php',
            data: { product_id: productId, quantity: quantity },
            dataType: 'json',
            success: function(r){
                if(r.success){
                    try { bootstrap.Modal.getInstance(document.getElementById('addToCartModal')).hide(); } catch(_e) {}
                    try {
                        var toastEl = document.getElementById('globalToast');
                        var body = document.getElementById('globalToastMessage');
                        if (body) body.textContent = r.message || 'Added to your cart!';
                        var toast = new bootstrap.Toast(toastEl, { delay: 1500 });
                        toast.show();
                    } catch(_err) { /* no-op */ }
                    updateCartBadge();
                } else {
                    try {
                        var toastErrEl = document.getElementById('globalToastError');
                        var msg = document.getElementById('globalToastErrorMessage');
                        if (msg) msg.textContent = r.message || 'Failed to add to cart';
                        var toastErr = new bootstrap.Toast(toastErrEl, { delay: 2000 });
                        toastErr.show();
                    } catch(_e) { alert(r.message || 'Failed to add to cart'); }
                }
            },
            error: function(){ alert('Network error. Please try again.'); }
        });
    });

    $(document).on('click', '.view-product', function(e){
        e.preventDefault();
        
        try {
            const $card = $(this).closest('.card');
            const productId = $(this).data('product-id');
            const title = $card.find('h5').text().trim();
            const img = $card.find('img').attr('src');
            const price = $card.find('.card-text').text().trim();
            const stock = parseInt($(this).data('stock')) || 0;
            const description = $(this).data('description') || 'No description available.';

            console.log('View Product clicked:', { productId, title, stock });

            $('#detailModalProductId').val(productId);
            $('#detailModalProductStock').val(stock);
            $('#detailModalTitle').text(title);
            $('#detailModalImage').attr('src', img);
            $('#detailModalPrice').text(price);
            $('#detailModalDescription').text(description);
            
            if (stock <= 0) {
                $('#detailModalStock').html('<span class="badge bg-danger">Out of Stock</span>');
                $('#detailAddToCartBtn').prop('disabled', true).text('Out of Stock');
            } else {
                $('#detailModalStock').html('<span class="text-success">In Stock: ' + stock + ' remaining</span>');
                $('#detailAddToCartBtn').prop('disabled', false).text('Add to Cart');
            }
            
            $('#selectedRating').val(0);
            $('#reviewText').val('');
            $('.rating-star').removeClass('bi-star-fill').addClass('bi-star');
            
            loadProductReviews(productId);
            
            var modalEl = document.getElementById('productDetailModal');
            if (modalEl) {
                if (typeof bootstrap === 'undefined') {
                    console.error('Bootstrap is not defined');
                    alert('System error: Bootstrap JS not loaded. Please refresh the page.');
                    return;
                }
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (!modal) {
                    modal = new bootstrap.Modal(modalEl);
                }
                modal.show();
            } else {
                console.error('Product Detail Modal element not found!');
                alert('Error: Product detail modal not found.');
            }
        } catch (err) {
            console.error('Error opening product modal:', err);
            alert('An error occurred while opening product details. Check console for details.');
        }
    });

    function loadProductReviews(productId) {
        $('#reviewsList').html('<p class="text-muted">Loading reviews...</p>');
        
        $.get('/website-popmart/db/reviews_get.php', { product_id: productId }, function(r) {
            if (typeof r === 'string') { try { r = JSON.parse(r); } catch(_e) { r = { success: false }; } }
            
            if (r.success) {
                $('#detailStars').html(generateStars(r.avg_rating));
                $('#detailAvgRating').text(r.avg_rating > 0 ? r.avg_rating + '/5' : 'No ratings yet');
                $('#detailTotalReviews').text(r.total_reviews > 0 ? '(' + r.total_reviews + ' reviews)' : '');
                
                if (r.reviews && r.reviews.length > 0) {
                    var html = '';
                    r.reviews.forEach(function(review) {
                        var date = new Date(review.created_at).toLocaleDateString('en-US', { 
                            year: 'numeric', month: 'short', day: 'numeric' 
                        });
                        html += '<div class="border-bottom pb-3 mb-3">';
                        html += '<div class="d-flex justify-content-between align-items-start">';
                        html += '<div>';
                        html += '<strong>' + review.first_name + ' ' + review.last_name.charAt(0) + '.</strong>';
                        html += '<div>' + generateStars(review.rating) + '</div>';
                        html += '</div>';
                        html += '<small class="text-muted">' + date + '</small>';
                        html += '</div>';
                        if (review.review_text) {
                            html += '<p class="mt-2 mb-0">' + review.review_text + '</p>';
                        }
                        html += '</div>';
                    });
                    $('#reviewsList').html(html);
                } else {
                    $('#reviewsList').html('<p class="text-muted">No reviews yet. Be the first to review this product!</p>');
                }
            } else {
                $('#reviewsList').html('<p class="text-muted">Unable to load reviews.</p>');
            }
        }).fail(function() {
            $('#reviewsList').html('<p class="text-danger">Error loading reviews. Please check your connection.</p>');
        });
    }

    $(document).on('click', '.rating-star', function() {
        var rating = $(this).data('rating');
        $('#selectedRating').val(rating);
        
        $('.rating-star').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill text-warning');
            } else {
                $(this).removeClass('bi-star-fill text-warning').addClass('bi-star');
            }
        });
    });

    $(document).on('mouseenter', '.rating-star', function() {
        var rating = $(this).data('rating');
        $('.rating-star').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).addClass('text-warning');
            }
        });
    });

    $(document).on('mouseleave', '#ratingStars', function() {
        var selected = parseInt($('#selectedRating').val()) || 0;
        $('.rating-star').each(function() {
            if ($(this).data('rating') <= selected) {
                $(this).removeClass('bi-star').addClass('bi-star-fill text-warning');
            } else {
                $(this).removeClass('bi-star-fill text-warning').addClass('bi-star');
            }
        });
    });

    $(document).on('click', '.write-review-btn', function() {
        var productId = $(this).data('product-id');
        var orderId = $(this).data('order-id');
        var name = $(this).data('product-name');
        var image = $(this).data('product-image');
        
        $('#reviewModalProductId').val(productId);
        $('#reviewModalOrderId').val(orderId);
        $('#reviewModalProductName').text(name);
        $('#reviewModalImage').attr('src', image);
        
        $('#selectedRating').val(0);
        $('#reviewText').val('');
        $('.rating-star').removeClass('bi-star-fill text-warning').addClass('bi-star');
        $('#submitReviewBtn').text('Submit Review');

        var modal = new bootstrap.Modal(document.getElementById('writeReviewModal'));
        modal.show();
    });

    $(document).on('click', '#submitReviewBtn', function() {
        if (!window.IS_LOGGED_IN) {
            alert('Please log in to submit a review');
            return;
        }
        
        var productId = $('#reviewModalProductId').val();
        var orderId = $('#reviewModalOrderId').val();
        var rating = parseInt($('#selectedRating').val()) || 0;
        var reviewText = $('#reviewText').val().trim();
        
        if (rating < 1 || rating > 5) {
            alert('Please select a rating (1-5 stars)');
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Submitting...');
        
        $.post('/website-popmart/db/reviews_submit.php', {
            product_id: productId,
            order_id: orderId,
            rating: rating,
            review_text: reviewText
        }, function(r) {
            if (typeof r === 'string') { try { r = JSON.parse(r); } catch(_e) { r = { success: false, message: 'Invalid server response' }; } }
            
            if (r.success) {
                alert(r.message || 'Review submitted successfully!');
                try { bootstrap.Modal.getInstance(document.getElementById('writeReviewModal')).hide(); } catch(_e) {}
                location.reload(); 
            } else {
                alert(r.message || 'Failed to submit review');
            }
            $btn.prop('disabled', false).text('Submit Review');
        }).fail(function(xhr) {
             var msg = 'Network error. Please try again.';
             try { if(xhr.responseText) console.error(xhr.responseText); } catch(e){}
             alert(msg);
             $btn.prop('disabled', false).text('Submit Review');
        });
    });

    $(document).on('click', '#detailAddToCartBtn', function() {
        if (!window.IS_LOGGED_IN) {
            try { new bootstrap.Modal(document.getElementById('loginModal')).show(); } catch(_e) {}
            return;
        }
        
        var productId = $('#detailModalProductId').val();
        var stock = parseInt($('#detailModalProductStock').val()) || 0;
        
        if (stock <= 0) {
            alert('Sorry, this product is out of stock');
            return;
        }
        
        try { bootstrap.Modal.getInstance(document.getElementById('productDetailModal')).hide(); } catch(_e) {}
        
        var $btn = $('.add-to-cart[data-product-id="' + productId + '"]');
        if ($btn.length) {
            $btn.first().click();
        }
    });
});
