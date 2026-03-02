{{-- =====================================================
     ALPINE.JS — GLOBAL CART STORE & APP DATA + WISHLIST
     ===================================================== --}}
@php
    $frontendFlashToasts = [];
    $initialWishlistCount = 0;
    $initialCartCount = 0;

    if (session('success')) {
        $frontendFlashToasts[] = ['type' => 'success', 'message' => session('success')];
    }
    if (session('error')) {
        $frontendFlashToasts[] = ['type' => 'error', 'message' => session('error')];
    }
    if (session('warning')) {
        $frontendFlashToasts[] = ['type' => 'warning', 'message' => session('warning')];
    }
    if (session('message')) {
        $frontendFlashToasts[] = ['type' => 'success', 'message' => session('message')];
    }

    if ($errors->any()) {
        foreach ($errors->all() as $errorMessage) {
            $frontendFlashToasts[] = ['type' => 'error', 'message' => $errorMessage];
        }
    }

    if (auth()->check()) {
        $initialWishlistCount = (int) \App\Models\Wishlist::query()
            ->where('user_id', auth()->id())
            ->count();

        $initialCartCount = (int) \App\Models\Cart::query()
            ->where('user_id', auth()->id())
            ->sum('quantity');
    }
@endphp
<script>
    // User role (PHP-rendered, used for role-based pricing in cart)
    window.APP_USER_ROLE = '{{ auth()->user() ? (auth()->user()->hasRole('Outlet User') ? 'Outlet User' : (auth()->user()->hasRole('User') ? 'User' : 'Other')) : 'Guest' }}';
    window.APP_AUTHENTICATED = {{ auth()->check() ? 'true' : 'false' }};
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.APP_INITIAL_WISHLIST_COUNT = {{ $initialWishlistCount }};
    window.APP_INITIAL_CART_COUNT = {{ $initialCartCount }};
    window.FRONTEND_FLASH_TOASTS = @json($frontendFlashToasts);

    document.addEventListener('alpine:init', () => {

        // ─── WISHLIST STORE ────────────────────────────────────
        Alpine.store('wishlist', {
            ids:   [],  // array of product_id's in wishlist
            count: parseInt(window.APP_INITIAL_WISHLIST_COUNT || 0, 10) || 0,
            hydrated: false,
            hydrating: false,

            isWishlisted(productId) {
                return this.ids.includes(parseInt(productId));
            },

            async ensureHydrated(force = false) {
                if (!window.APP_AUTHENTICATED) {
                    this.hydrated = true;
                    this.ids = [];
                    this.count = 0;
                    return;
                }

                if (!force && (this.hydrated || this.hydrating)) {
                    return;
                }

                this.hydrating = true;
                try {
                    const res  = await fetch('/wishlist/ids', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.ids   = data.ids || [];
                    this.count = data.count || 0;
                } catch (e) { /* silent */ }
                finally {
                    this.hydrated = true;
                    this.hydrating = false;
                }
            },

            async toggle(productId) {
                if (!window.APP_AUTHENTICATED) {
                    window.location.href = '{{ route("login") }}';
                    return null;
                }
                try {
                    const res  = await fetch('/wishlist/toggle', {
                        method:  'POST',
                        headers: {
                            'Content-Type':  'application/json',
                            'Accept':        'application/json',
                            'X-CSRF-TOKEN':  window.CSRF_TOKEN,
                        },
                        body: JSON.stringify({ product_id: productId }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (data.wishlisted) {
                            if (!this.ids.includes(parseInt(productId))) {
                                this.ids = [...this.ids, parseInt(productId)];
                            }
                        } else {
                            this.ids = this.ids.filter(id => id !== parseInt(productId));
                        }
                        this.count = data.count;
                    }
                    return data;
                } catch (e) { return null; }
            },

            async clearAll() {
                if (!window.APP_AUTHENTICATED) {
                    window.location.href = '{{ route("login") }}';
                    return null;
                }
                try {
                    const res  = await fetch('/wishlist/clear', {
                        method:  'POST',
                        headers: {
                            'Content-Type':  'application/json',
                            'Accept':        'application/json',
                            'X-CSRF-TOKEN':  window.CSRF_TOKEN,
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.ids = [];
                        this.count = 0;
                    }
                    return data;
                } catch (e) { return null; }
            },
        });

        // ─── CART STORE ───────────────────────────────────────
        Alpine.store('cart', {
            items: window.APP_AUTHENTICATED
                ? []
                : [...JSON.parse(localStorage.getItem('cart_items') || '[]')],
            serverCount: parseInt(window.APP_INITIAL_CART_COUNT || 0, 10) || 0,
            hydrated: !window.APP_AUTHENTICATED,
            hydrating: false,

            get count() {
                if (window.APP_AUTHENTICATED && !this.hydrated && this.items.length === 0) {
                    return this.serverCount;
                }

                return this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
            },

            get total() {
                return this.items.reduce((total, item) => total + (parseFloat(item.price) * (parseInt(item.quantity) || 0)), 0);
            },

            async ensureHydrated(force = false) {
                if (!window.APP_AUTHENTICATED) {
                    this.hydrated = true;
                    return;
                }

                if (!force && (this.hydrated || this.hydrating)) {
                    return;
                }

                this.hydrating = true;
                try {
                    await this.loadFromDB();
                } finally {
                    this.hydrated = true;
                    this.hydrating = false;
                }
            },

            async loadFromDB() {
                try {
                    const res  = await fetch('/frontend/cart/items', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    // Force reactivity by reassigning the array
                    this.items = [...(data.items || [])];
                    this.serverCount = this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
                } catch (e) {
                    console.error('Cart load error:', e);
                    // Fallback to localStorage if request fails
                    this.items = JSON.parse(localStorage.getItem('cart_items') || '[]');
                    this.serverCount = this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
                }
            },

            async addItem(product, variant = null, quantity = 1) {
                const q    = parseInt(quantity) || 1;
                const role = window.APP_USER_ROLE || 'Guest';
                let price  = 0;

                if (role === 'Outlet User' || role === 'User') {
                    price = variant
                        ? (variant.outlet_price || variant.price)
                        : (product.outlet_price || product.price);
                } else {
                    price = variant ? variant.price : product.price;
                }

                if (window.APP_AUTHENTICATED) {
                    // Persist to DB
                    try {
                        const res = await fetch('/frontend/cart/add', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            },
                            body: JSON.stringify({
                                product_id: product.id,
                                variant_id: variant ? variant.id : null,
                                quantity: q
                            }),
                        });

                        let data = {};
                        try {
                            data = await res.json();
                        } catch (_) {}

                        if (!res.ok || data.success === false) {
                            throw new Error(data.message || 'Add to cart failed.');
                        }

                        await this.loadFromDB();
                        if (data.removed_from_wishlist) {
                            const wishlist = Alpine.store('wishlist');
                            wishlist.ids = wishlist.ids.filter(id => id !== parseInt(product.id));
                            wishlist.count = data.wishlist_count ?? wishlist.ids.length;
                        }
                    } catch (e) { 
                        console.error('Add item error:', e);
                        throw e;
                    }
                } else {
                    await this.ensureHydrated();
                    // Guest: use localStorage only
                    const existingItem = this.items.find(item =>
                        item.product_id === product.id &&
                        (!variant || item.variant_id === variant.id)
                    );

                    if (existingItem) {
                        existingItem.quantity += q;
                        existingItem.price = price;
                    } else {
                        this.items.push({
                            id:            Date.now() + Math.random(),
                            product_id:    product.id,
                            variant_id:    variant ? variant.id : null,
                            name:          product.name,
                            price:         price,
                            image:         product.thumb_image,
                            category:      product.category?.name || product.category || 'General',
                            variant_label: variant
                                ? (variant.name || `${variant.color || ''} ${variant.size || ''}`.trim())
                                : null,
                            quantity: q,
                        });
                    }
                    this.save();
                }
                this.items = [...this.items]; // force reactivity
            },

            async removeItem(cartId) {
                if (window.APP_AUTHENTICATED) {
                    try {
                        const res = await fetch('/frontend/cart/remove', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            },
                            body: JSON.stringify({ cart_id: cartId }),
                        });
                        if (res.ok) {
                            await this.loadFromDB();
                        } else {
                            console.error('Remove failed:', res.status);
                        }
                    } catch (e) { 
                        console.error('Remove item error:', e);
                    }
                } else {
                    await this.ensureHydrated();
                    this.items = this.items.filter(item => item.id !== cartId);
                    this.save();
                }
                this.items = [...this.items];
            },

            async updateQuantity(cartId, qty) {
                const q = Math.max(1, parseInt(qty) || 1);
                if (window.APP_AUTHENTICATED) {
                    try {
                        const res = await fetch('/frontend/cart/update-qty', {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            },
                            body: JSON.stringify({ cart_id: cartId, quantity: q }),
                        });

                        let data = {};
                        try {
                            data = await res.json();
                        } catch (_) {}

                        if (!res.ok || data.success === false) {
                            throw new Error(data.message || 'Failed to update quantity.');
                        }

                        await this.loadFromDB();
                    } catch (e) { 
                        console.error('Update quantity error:', e);
                        throw e;
                    }
                } else {
                    await this.ensureHydrated();
                    const item = this.items.find(i => i.id === cartId);
                    if (item) {
                        item.quantity = q;
                        this.save();
                        this.items = [...this.items];
                    }
                }
            },

            // Clear cart (called on logout)
            async clearLocal() {
                this.items = [];
                this.serverCount = 0;
                this.hydrated = true;
                localStorage.removeItem('cart_items');
            },

            save() {
                localStorage.setItem('cart_items', JSON.stringify(this.items));
            },
        });

        // ─── GLOBAL APP DATA ──────────────────────────────────
        Alpine.data('globalApp', () => ({
            isCartOpen:     false,
            isWishlistOpen: false,
            notifications:  [],
            userRole:       window.APP_USER_ROLE,
            searchQuery:    '',

            init() {
                this.$watch('isCartOpen', (isOpen) => {
                    if (isOpen) {
                        Alpine.store('cart').ensureHydrated();
                    }
                });

                if (Array.isArray(window.FRONTEND_FLASH_TOASTS) && window.FRONTEND_FLASH_TOASTS.length > 0) {
                    window.FRONTEND_FLASH_TOASTS.forEach((toast, idx) => {
                        setTimeout(() => {
                            this.notify(toast.message, toast.type || 'success');
                        }, 120 * (idx + 1));
                    });
                    window.FRONTEND_FLASH_TOASTS = [];
                }
            },

            // Cart getters
            get cartCount() { return Alpine.store('cart').count; },
            get cartItems() { return Alpine.store('cart').items; },
            get cartTotal() { return Alpine.store('cart').total; },
            get cartDisplayTotal() {
                return this.cartItems.reduce((total, item) => {
                    const lineDiscounted = parseFloat(item.line_total_after_discount);
                    if (!Number.isNaN(lineDiscounted)) {
                        return total + lineDiscounted;
                    }

                    const unit = parseFloat(item.display_price ?? item.price) || 0;
                    const qty = parseInt(item.quantity) || 0;
                    return total + (unit * qty);
                }, 0);
            },
            get cartOriginalTotal() {
                return this.cartItems.reduce((total, item) => {
                    const lineOriginal = parseFloat(item.line_total);
                    if (!Number.isNaN(lineOriginal)) {
                        return total + lineOriginal;
                    }

                    const unit = parseFloat(item.original_price ?? item.price) || 0;
                    const qty = parseInt(item.quantity) || 0;
                    return total + (unit * qty);
                }, 0);
            },

            // Wishlist getters
            get wishlistCount() { return Alpine.store('wishlist').count; },
            isWishlisted(id)   { return Alpine.store('wishlist').isWishlisted(id); },

            // Notification helpers
            notify(message, type = 'success') {
                const id = Date.now();
                this.notifications.push({ id, message, type, show: true });
                setTimeout(() => this.hideNotification(id), 4000);
            },

            hideNotification(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].show = false;
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 300);
                }
            },

            // Cart actions
            async addToCart(product, variant = null, quantity = 1) {
                try {
                    await Alpine.store('cart').addItem(product, variant, quantity);
                    this.notify('Added to cart ✓');
                    this.isCartOpen = true;
                } catch (e) {
                    this.notify(e?.message || 'Add to cart failed.', 'error');
                }
            },

            async removeFromCart(cartId) {
                await Alpine.store('cart').removeItem(cartId);
                this.notify('Removed from cart', 'warning');
            },

            async updateCartQty(cartId, qty) {
                try {
                    let val = parseInt(qty);

                    if (Number.isNaN(val)) {
                        this.notify('Please enter a valid quantity.', 'error');
                        await Alpine.store('cart').loadFromDB();
                        return;
                    }

                    const item = this.cartItems.find(i => parseInt(i.id) === parseInt(cartId));
                    const stock = item && item.available_stock !== undefined && item.available_stock !== null
                        ? Math.max(0, parseInt(item.available_stock) || 0)
                        : null;

                    if (stock !== null && val > stock) {
                        this.notify(`Available stock: ${stock}`, 'error');
                        val = stock;
                    }

                    if (val < 1) {
                        await this.removeFromCart(cartId);
                    } else {
                        await Alpine.store('cart').updateQuantity(cartId, val);
                    }
                } catch (e) {
                    this.notify(e?.message || 'Failed to update quantity.', 'error');
                }
            },

            // Wishlist actions
            async toggleWishlist(productId) {
                const result = await Alpine.store('wishlist').toggle(productId);
                if (result) {
                    this.notify(result.message, result.wishlisted ? 'success' : 'warning');
                }
            },

            // Logout: clear local stores before submitting
            handleLogout(formEl) {
                Alpine.store('cart').clearLocal();
                Alpine.store('wishlist').ids   = [];
                Alpine.store('wishlist').count = 0;
                formEl.submit();
            },
        }));

    });
</script>
