<?php include 'header.php'; ?>

    <main class="container">
        <section class="menu">
            <h2>Mis Pedidos</h2>

            <?php
            include 'db.php';

            if (!isset($_SESSION['user_id'])) {
                echo '<div class="cart-empty"><p>Debes iniciar sesión para ver tu historial.</p>';
                echo '<a href="/PHP/administrador/login.php" class="btn btn-primary">Iniciar sesión</a></div>';
            } else {
                $user_id = $_SESSION['user_id'];

                $stmt = $conn->prepare("
                    SELECT id, fecha_pedido, total
                    FROM HistorialPedidos
                    WHERE usuario_id = ?
                    ORDER BY fecha_pedido DESC, id DESC
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                if (empty($pedidos)) {
                    echo '<div class="cart-empty">';
                    echo '<p>Todavía no realizaste ningún pedido.</p>';
                    echo '<a href="/PHP/index.php" class="btn btn-primary">Ver Menú</a>';
                    echo '</div>';
                } else {
                    foreach ($pedidos as $pedido) {
                        $stmt2 = $conn->prepare("
                            SELECT nombre_item, precio_unitario, cantidad
                            FROM HistorialItems
                            WHERE pedido_id = ?
                            ORDER BY nombre_item
                        ");
                        $stmt2->bind_param("i", $pedido['id']);
                        $stmt2->execute();
                        $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                        $stmt2->close();

                        $fecha  = date('d/m/Y', strtotime($pedido['fecha_pedido']));
                        $total  = number_format($pedido['total'], 0, ',', '.');
                        $pid    = $pedido['id'];

                        echo "<div class=\"historial-order\" id=\"order-{$pid}\">";
                        echo "  <div class=\"historial-order-header\" onclick=\"toggleOrder({$pid})\">";
                        echo "    <div class=\"historial-order-meta\">";
                        echo "      <span class=\"historial-order-id\"># {$pid}</span>";
                        echo "      <span class=\"historial-order-date\">{$fecha}</span>";
                        echo "    </div>";
                        echo "    <div class=\"historial-order-right\">";
                        echo "      <span class=\"historial-order-total\">\${$total}</span>";
                        echo "      <span class=\"historial-chevron\" id=\"chevron-{$pid}\">";
                        echo "        <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\" stroke-linecap=\"round\"><polyline points=\"6 9 12 15 18 9\"/></svg>";
                        echo "      </span>";
                        echo "    </div>";
                        echo "  </div>";
                        echo "  <div class=\"historial-order-body\" id=\"body-{$pid}\">";

                        if (empty($items)) {
                            echo "    <p class=\"historial-empty-items\">Sin detalle disponible.</p>";
                        } else {
                            echo "    <div class=\"historial-items-header\">";
                            echo "      <span>Plato</span><span>Precio unit.</span><span>Cant.</span><span>Subtotal</span>";
                            echo "    </div>";
                            foreach ($items as $item) {
                                $sub    = number_format($item['precio_unitario'] * $item['cantidad'], 0, ',', '.');
                                $precio = number_format($item['precio_unitario'], 0, ',', '.');
                                $nombre = htmlspecialchars($item['nombre_item']);
                                echo "    <div class=\"historial-item-row\">";
                                echo "      <span class=\"historial-item-name\">{$nombre}</span>";
                                echo "      <span class=\"historial-item-price\">\${$precio}</span>";
                                echo "      <span class=\"historial-item-qty\">{$item['cantidad']}</span>";
                                echo "      <span class=\"historial-item-sub\">\${$sub}</span>";
                                echo "    </div>";
                            }
                        }

                        echo "  </div>";
                        echo "</div>";
                    }
                }
            }

            $conn->close();
            ?>
        </section>
    </main>

    <script>
        function toggleOrder(pid) {
            const body    = document.getElementById('body-' + pid);
            const chevron = document.getElementById('chevron-' + pid);
            const isOpen  = body.classList.toggle('open');
            chevron.classList.toggle('rotated', isOpen);
        }
    </script>

<?php include 'footer.php'; ?>
