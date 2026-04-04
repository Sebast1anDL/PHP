<?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="container">
        <section class="menu">
            <h2>Nuestro Menú</h2>
            <div class="menu-controls">
                <div class="sort-container">
                    <label for="sortSelect">Ordenar por:</label>
                    <select id="sortSelect" class="sort-select">
                        <option value="default">Predeterminado</option>
                        <option value="name_asc">Nombre A-Z</option>
                        <option value="name_desc">Nombre Z-A</option>
                        <option value="price_asc">Precio más bajo</option>
                        <option value="price_desc">Precio más alto</option>
                    </select>
                </div>
            </div>
            
            <?php
            include 'db.php';
            
            $sql_cat = "SELECT * FROM Categoria ORDER BY id";
            $result_cat = $conn->query($sql_cat);
            
            if ($result_cat->num_rows > 0) {
                while($cat = $result_cat->fetch_assoc()) {
                    echo '<div class="menu-category">';
                    echo '<h3>' . htmlspecialchars($cat['nombre']) . '</h3>';
                    echo '<div class="menu-items">';
                    
                    $sql_items = "SELECT * FROM MenuItems WHERE categoria_id = " . $cat['id'];
                    $result_items = $conn->query($sql_items);
                    
                    if ($result_items->num_rows > 0) {
                        while($item = $result_items->fetch_assoc()) {
                            $is_fav = false;
                            if (isset($_SESSION['user_id'])) {
                                $sql_check = "SELECT 1 FROM Favoritos WHERE usuario_id = ? AND menu_id = ?";
                                $stmt_check = $conn->prepare($sql_check);
                                $stmt_check->bind_param("ii", $_SESSION['user_id'], $item['id']);
                                $stmt_check->execute();
                                $result_check = $stmt_check->get_result();
                                if ($result_check->num_rows > 0) {
                                    $is_fav = true;
                                }
                                $stmt_check->close();
                            }
                            echo '<div class="menu-item">';
                            echo '<div class="item-header">';
                            echo '<div class="item-title">';
                            echo '<h4>' . htmlspecialchars($item['nombre']) . '</h4>';
                            if (isset($_SESSION['user_id'])) {
                                echo '<span onclick="toggleFav(this, ' . $item['id'] . ')" class="favorite-toggle" title="Agregar/Quitar de favoritos">';
                                echo '<svg width="24" height="24" viewBox="0 0 24 24" class="heart-icon-small">';
                                if ($is_fav) {
                                    echo '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="var(--danger)"/>';
                                } else {
                                    echo '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="none" stroke="var(--danger)" stroke-width="2"/>';
                                }
                                echo '</svg>';
                                echo '</span>';
                            }
                            echo '</div>';
                            echo '<span class="price">$' . $item['precio'] . '</span>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay items en esta categoría.</p>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No hay categorías disponibles.</p>';
            }
            
            $conn->close();
            ?>
        </section>
    </main>

    <script>
        function parsePrice(priceText) {
            if (!priceText) return 0;
            return parseFloat(priceText.replace(/[^0-9.,]/g, '').replace(',', '.')) || 0;
        }

        function sortMenuItems(mode) {
            const categories = document.querySelectorAll('.menu-category');
            categories.forEach(category => {
                const containers = category.querySelector('.menu-items');
                const items = Array.from(containers.querySelectorAll('.menu-item'));

                if (mode === 'default') {
                    // Mantener orden original
                    return;
                } else if (mode.startsWith('name')) {
                    items.sort((a, b) => {
                        const nameA = a.querySelector('h4').innerText.toLowerCase();
                        const nameB = b.querySelector('h4').innerText.toLowerCase();
                        if (nameA < nameB) return mode === 'name_asc' ? -1 : 1;
                        if (nameA > nameB) return mode === 'name_asc' ? 1 : -1;
                        return 0;
                    });
                } else if (mode.startsWith('price')) {
                    items.sort((a, b) => {
                        const priceA = parsePrice(a.querySelector('.price').innerText);
                        const priceB = parsePrice(b.querySelector('.price').innerText);
                        return mode === 'price_asc' ? priceA - priceB : priceB - priceA;
                    });
                }

                items.forEach(item => containers.appendChild(item));
            });
        }

        document.getElementById('sortSelect').addEventListener('change', (e) => {
            sortMenuItems(e.target.value);
        });

        function toggleFav(el, menu_id) {
            fetch('toggle_favorite.php?menu_id=' + menu_id, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const path = el.querySelector('path');
                if (data.is_fav) {
                    path.setAttribute('fill', 'var(--danger)');
                    path.setAttribute('stroke', 'none');
                } else {
                    path.setAttribute('fill', 'none');
                    path.setAttribute('stroke', 'var(--danger)');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

<?php include 'footer.php'; ?>
