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
            include '../db.php';
            
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
                            echo '<div class="menu-item">';
                            echo '<div class="item-header">';
                            echo '<h4>' . htmlspecialchars($item['nombre']) . '</h4>';
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
    </script>

<?php include 'footer.php'; ?>
