{{-- =====================================================
     ALPINE.JS — GLOBAL CART STORE & APP DATA
     ===================================================== --}}
<script>
    // User role (PHP-rendered, used for role-based pricing in cart)
    window.APP_USER_ROLE = '{{ auth()->user() ? (auth()->user()->hasRole('Outlet User') ? 'Outlet User' : (auth()->user()->hasRole('User') ? 'User' : 'Other')) : 'Guest' }}';

    document.addEventListener('alpine:init', () => {

        // ─── CART STORE ───────────────────────────────────────
        Alpine.store('cart', {
            items: JSON.parse(localStorage.getItem('cart_items') || '[]'),

            get count() {
                return this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
            },

            get total() {
                return this.items.reduce((total, item) => total + (parseFloat(item.price) * (parseInt(item.quantity) || 0)), 0);
            },

            addItem(product, variant = null, quantity = 1) {
                const q = parseInt(quantity) || 1;
                const existingItem = this.items.find(item =>
                    item.product_id === product.id &&
                    (!variant || item.variant_id === variant.id)
                );

                // Determine price based on user role
                const role = window.APP_USER_ROLE || 'Guest';
                let price = 0;

                if (role === 'Outlet User' || role === 'User') {
                    price = variant
                        ? (variant.outlet_price || variant.price)
                        : (product.outlet_price || product.price);
                } else {
                    price = variant ? variant.price : product.price;
                }

                if (existingItem) {
                    existingItem.quantity += q;
                    existingItem.price = price;
                } else {
                    this.items.push({
                        id: Date.now() + Math.random(),
                        product_id: product.id,
                        variant_id: variant ? variant.id : null,
                        name: product.name,
                        price: price,
                        image: product.thumb_image,
                        category: product.category?.name || product.category || 'General',
                        variant_label: variant
                            ? (variant.name || `${variant.color || ''} ${variant.size || ''}`.trim())
                            : null,
                        quantity: q,
                    });
                }
                this.save();
                this.items = [...this.items]; // force reactivity
            },

            removeItem(id) {
                this.items = this.items.filter(item => item.id !== id);
                this.save();
                this.items = [...this.items]; // force reactivity
            },

            updateQuantity(id, qty) {
                const item = this.items.find(i => i.id === id);
                if (item) {
                    item.quantity = Math.max(1, parseInt(qty) || 1);
                    this.save();
                    this.items = [...this.items]; // force reactivity
                }
            },

            save() {
                localStorage.setItem('cart_items', JSON.stringify(this.items));
            },
        });

        // ─── GLOBAL APP DATA ──────────────────────────────────
        Alpine.data('globalApp', () => ({
            isCartOpen: false,
            notifications: [],
            userRole: window.APP_USER_ROLE,

            init() {
                // console.log('Global App Initialized. Role:', this.userRole);
            },

            // Cart getters
            get cartCount() { return Alpine.store('cart').count; },
            get cartItems() { return Alpine.store('cart').items; },
            get cartTotal() { return Alpine.store('cart').total; },

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
            addToCart(product, variant = null, quantity = 1) {
                Alpine.store('cart').addItem(product, variant, quantity);
                this.notify('Added product to cart');
            },

            removeFromCart(id) {
                Alpine.store('cart').removeItem(id);
                this.notify('Removed product from cart', 'warning');
            },

            updateCartQty(id, qty) {
                const val = parseInt(qty);
                if (val < 1) {
                    this.removeFromCart(id);
                } else {
                    Alpine.store('cart').updateQuantity(id, qty);
                }
            },
        }));
    });
</script>
