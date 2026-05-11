<?php include 'header.php'; ?>

    <main class="container">
        <section class="menu">
            <h2>Mi Carrito</h2>

            <?php
            include 'db.php';

            if (!isset($_SESSION['user_id'])) {
                echo '<div class="cart-empty">';
                echo '<p>Debes iniciar sesión para ver tu carrito.</p>';
                echo '<a href="/PHP/administrador/login.php" class="btn btn-primary">Iniciar sesión</a>';
                echo '</div>';
            } else {
                $user_id = $_SESSION['user_id'];

                $stmt = $conn->prepare("SELECT id FROM Carrito WHERE dueno_id = ? AND comprado = FALSE LIMIT 1");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $cart = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$cart) {
                    echo '<div class="cart-empty">';
                    echo '<p>Tu carrito está vacío.</p>';
                    echo '<a href="/PHP/index.php" class="btn btn-primary">Ver Menú</a>';
                    echo '</div>';
                } else {
                    $cart_id = $cart['id'];

                    $stmt = $conn->prepare("
                        SELECT ci.menu_id, ci.cantidad, m.nombre, m.precio
                        FROM CarritoItems ci
                        JOIN MenuItems m ON ci.menu_id = m.id
                        WHERE ci.carrito_id = ?
                        ORDER BY m.nombre
                    ");
                    $stmt->bind_param("i", $cart_id);
                    $stmt->execute();
                    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();

                    if (empty($items)) {
                        echo '<div class="cart-empty">';
                        echo '<p>Tu carrito está vacío.</p>';
                        echo '<a href="/PHP/index.php" class="btn btn-primary">Ver Menú</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="cart-table">';
                        echo '<div class="cart-header-row">';
                        echo '<span>Plato</span><span>Precio unit.</span><span>Cantidad</span><span>Subtotal</span><span></span>';
                        echo '</div>';

                        $total = 0;
                        foreach ($items as $item) {
                            $subtotal = $item['precio'] * $item['cantidad'];
                            $total += $subtotal;
                            echo '<div class="cart-row" data-menu-id="' . $item['menu_id'] . '">';
                            echo '<span class="cart-item-name">' . htmlspecialchars($item['nombre']) . '</span>';
                            echo '<span class="cart-item-price">$' . $item['precio'] . '</span>';
                            echo '<span class="cart-item-qty">';
                            echo '<button class="qty-btn" onclick="changeQty(' . $item['menu_id'] . ', -1)">−</button>';
                            echo '<span class="qty-value">' . $item['cantidad'] . '</span>';
                            echo '<button class="qty-btn" onclick="changeQty(' . $item['menu_id'] . ', 1)">+</button>';
                            echo '</span>';
                            echo '<span class="cart-item-subtotal">$<span class="subtotal-value">' . $subtotal . '</span></span>';
                            echo '<button class="cart-remove-btn" onclick="removeItem(' . $item['menu_id'] . ')" title="Eliminar">✕</button>';
                            echo '</div>';
                        }

                        echo '</div>';
                        echo '<div class="cart-footer">';
                        echo '<div class="cart-total">Total: $<span id="cart-total">' . $total . '</span></div>';
                        echo '<button class="btn btn-primary btn-checkout" onclick="checkout()">Confirmar Pedido</button>';
                        echo '</div>';
                    }
                }
            }

            $conn->close();
            ?>
        </section>
    </main>

    <div id="cart-toast" class="cart-toast"></div>

    <script>
        function showToast(msg, type = 'success') {
            const t = document.getElementById('cart-toast');
            t.textContent = msg;
            t.className = 'cart-toast cart-toast-' + type + ' show';
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        function cartAction(data, callback) {
            fetch('/PHP/cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            })
            .then(r => r.json())
            .then(callback)
            .catch(() => showToast('Error de conexión', 'error'));
        }

        function changeQty(menuId, delta) {
            const row = document.querySelector(`.cart-row[data-menu-id="${menuId}"]`);
            const qtyEl = row.querySelector('.qty-value');
            const newQty = parseInt(qtyEl.textContent) + delta;
            if (newQty < 1) { removeItem(menuId); return; }

            cartAction({ action: 'update', menu_id: menuId, quantity: newQty }, data => {
                if (data.success) {
                    qtyEl.textContent = newQty;
                    const price = parseInt(row.querySelector('.cart-item-price').textContent.replace('$', ''));
                    row.querySelector('.subtotal-value').textContent = price * newQty;
                    recalcTotal();
                    updateBadge(data.cart_count);
                }
            });
        }

        function removeItem(menuId) {
            cartAction({ action: 'remove', menu_id: menuId }, data => {
                if (data.success) {
                    const row = document.querySelector(`.cart-row[data-menu-id="${menuId}"]`);
                    row.style.animation = 'slideOut 0.3s ease forwards';
                    setTimeout(() => {
                        row.remove();
                        recalcTotal();
                        updateBadge(data.cart_count);
                        if (document.querySelectorAll('.cart-row').length === 0) location.reload();
                    }, 300);
                }
            });
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal-value').forEach(el => total += parseInt(el.textContent));
            document.getElementById('cart-total').textContent = total;
        }

        function checkout() {
            cartAction({ action: 'checkout' }, data => {
                if (data.success) {
                    updateBadge(0);
                    document.querySelector('.cart-table')?.remove();
                    document.querySelector('.cart-footer')?.remove();
                    document.querySelector('section.menu').insertAdjacentHTML('beforeend', `
                        <div class="cart-empty">
                            <div class="checkout-success">
                                <svg width="72" height="72" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="var(--accent)" stroke-width="2"/>
                                    <path d="M7 12l4 4 6-6" stroke="var(--accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h3>¡Pedido confirmado!</h3>
                                <p>Tu pedido ha sido registrado exitosamente.</p>
                                <a href="/PHP/index.php" class="btn btn-primary" style="margin-top:18px">Volver al Menú</a>
                            </div>
                        </div>
                    `);
                } else {
                    showToast(data.error || 'Error al confirmar', 'error');
                }
            });
        }

        function updateBadge(count) {
            const badge = document.querySelector('.cart-badge');
            if (!badge) return;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    </script>

<?php include 'footer.php'; ?>
